<?php


namespace Umino\Anime\Shikimori;


use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Query\Query;
use CModule;
use Umino\Anime\Cache;
use Umino\Anime\Core;
use Umino\Anime\Shikimori\Tables\ShikimoriLoadTable;

class Manager
{
    protected static array $types = [
        'Anime' => [
            'Import' => Import\Anime::class,
            'API' => API\Anime::class,
            'IB' => [
                'CODE' => 'animes',
            ],
        ],
        'Manga' => [
            'Import' => Import\Manga::class,
            'API' => API\Manga::class,
            'IB' => [
                'CODE' => 'mangas',
            ],
        ],
        'AnimeRole' => [
            'Import' => Import\AnimeRole::class,
            'API' => API\AnimeRole::class,
            'IB' => [
                'CODE' => 'roles',
            ],
        ],
        'MangaRole' => [
            'Import' => Import\MangaRole::class,
            'API' => API\MangaRole::class,
            'IB' => [
                'CODE' => 'roles',
            ],
        ],
        'People' => [
            'Import' => Import\People::class,
            'API' => API\People::class,
            'IB' => [
                'CODE' => 'people',
            ],
        ],
        'Character' => [
            'Import' => Import\Character::class,
            'API' => API\Character::class,
            'IB' => [
                'CODE' => 'characters',
            ],
        ],
        'Genre' => [
            'Import' => Import\Genre::class,
            'API' => API\Genre::class,
            'IB' => [
                'CODE' => 'genres',
            ],
        ],
        'Publisher' => [
            'Import' => Import\Publisher::class,
            'API' => API\Publisher::class,
            'IB' => [
                'CODE' => 'publishers',
            ],
        ],
        'Studio' => [
            'Import' => Import\Studio::class,
            'API' => API\Studio::class,
            'IB' => [
                'CODE' => 'studios',
            ],
        ],
        'Video' => [
            'Import' => Import\Video::class,
            'API' => API\Video::class,
            'IB' => [
                'CODE' => 'videos',
            ],
        ],
        'Translation' => [
            'Import' => Import\Translation::class,
            'API' => API\Translation::class,
            'IB' => [
                'CODE' => 'translations',
            ],
        ],
        'Episode' => [
            'Import' => Import\Episode::class,
            'API' => API\Episode::class,
            'IB' => [
                'CODE' => 'episodes',
            ],
        ],
        'Franchise' => [
            'Import' => Import\Franchise::class,
            'API' => API\Franchise::class,
            'IB' => [
                'CODE' => 'franchises',
            ],
        ],
    ];

    public static int $maxScreenshots = 5;
    public static int $maxLimit = 20;

    public static function getTypes(): array
    {
        return self::$types;
    }

    public static function load(int $limit = 10, bool $isLoad = false, array $types = [])
    {
        CModule::IncludeModule('iblock');

        while ($limit > 0) {
            $limit -= self::$maxLimit;

            $entity = ShikimoriLoadTable::getEntity();
            $query = new Query($entity);
            $query
                ->setLimit(self::$maxLimit)
                ->setFilter([
                    'TYPE' => $types ?: array_keys(static::$types),
                    '=IS_LOAD' => $isLoad,
                ])
                ->setOrder([
                    'PRIORITY' => 'ASC',
                    'DATE_CREATE' => 'ASC',
                ])
                ->setSelect(['*'])
            ;

            if ($query->queryCountTotal() <= 0) break;

            $items = [];

            foreach ($query->fetchAll() as $item) {
                $class = static::getImportClass($item['TYPE']);
                if ($item['PARENT_SID']) {
                    $object = new $class($item['SID'], $item['PARENT_SID']);
                } else {
                    $object = new $class($item['SID']);
                }

                $items[$item['TYPE']][$item['ID']] = $object;
            }

            foreach (array_keys($items) as $type) {
                /** @var API\Entity $apiClass */
                $apiClass = static::getAPIClass($type);
                $apiClass::getAsync();
            }

            foreach ($items as $item) {
                /** @var Import\Entity $import */
                foreach ($item as $id => $import) {

                    if (!$import->load()) continue;

                    ShikimoriLoadTable::update($id, [
                        'IS_LOAD' => true,
                    ]);
                }
            }
        }
    }

    public static function getAPIClass(string $type): string
    {
        return static::$types[$type]['API'] ?: '';
    }

    public static function getImportClass(string $type): string
    {
        return static::$types[$type]['Import'] ?: '';
    }

    public static function getIBCode(string $type): string
    {
        return static::$types[$type]['IB']['CODE'] ?: '';
    }

    public static function getPriority(string $type): string
    {
        $priority = Cache::get();
        if (empty($priority)) $priority = Cache::set(Core::getImportTypesPriority());
        return isset($priority[$type]) ? $priority[$type] : 100;
    }

    public static function getIBID(string $type): int
    {
        $result = static::$types[$type]['IB']['ID'];

        if ($result) return $result;

        $ibCode = static::getIBCode($type);

        if (!$ibCode) return 0;

        $entity = IblockTable::getEntity();
        $query = new Query($entity);
        $query
            ->setLimit(1)
            ->setFilter([
                '=CODE' => $ibCode,
            ])
            ->setSelect(['ID'])
        ;

        $result = $query->fetch()['ID'];

        if (empty($result)) return false;

        return static::$types[$type]['IB']['ID'] = $result;
    }

    public static function getIBProperties(string $type): array
    {
        $result = static::$types[$type]['IB']['PROPERTIES'];

        if ($result) return $result;

        $ibid = static::getIBID($type);

        $entity = PropertyTable::getEntity();
        $query = new Query($entity);
        $query
            ->setFilter([
                '=IBLOCK_ID' => $ibid,
            ])
            ->setSelect(['*'])
        ;

        foreach ($query->fetchAll() as $property) {
            $result[$property['CODE']] = $property;
        }

        return static::$types[$type]['IB']['PROPERTIES'] = $result;
    }

    public static function addLoad(string $type, string $sid, string $parent_sid = null): bool
    {
        if (static::getLoad($type, $sid, $parent_sid)) return false;

        ShikimoriLoadTable::add([
            'SID' => $sid,
            'PARENT_SID' => $parent_sid,
            'TYPE' => $type,
            'PRIORITY' => static::getPriority($type),
        ]);

        return true;
    }

    public static function getLoad(string $type, string $sid, string $parent_sid = null)
    {
        $entity = ShikimoriLoadTable::getEntity();
        $query = new Query($entity);
        $query
            ->setLimit(1)
            ->setFilter([
                '=SID' => $sid,
                '=PARENT_SID' => $parent_sid,
                '=TYPE' => $type,
            ]);

        return $query->fetch();
    }
}