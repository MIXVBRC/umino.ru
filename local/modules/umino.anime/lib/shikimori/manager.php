<?php


namespace Umino\Anime\Shikimori;


use Bitrix\Iblock\IblockTable;
use Bitrix\Main\ORM\Query\Query;
use CModule;
use Umino\Anime\Shikimori\Tables\ShikimoriLoadTable;

class Manager
{
    protected static array $types = [
        'Anime' => [
            'Import' => Import\Anime::class,
            'API' => API\Anime::class,
            'IB_CODE' => 'animes',
            'PRIORITY' => 50,
        ],
        'Manga' => [
            'Import' => Import\Manga::class,
            'API' => API\Manga::class,
            'IB_CODE' => 'mangas',
            'PRIORITY' => 100,
        ],
        'AnimeRole' => [
            'Import' => Import\AnimeRole::class,
            'API' => API\AnimeRole::class,
            'IB_CODE' => 'roles',
            'PRIORITY' => 300,
        ],
        'MangaRole' => [
            'Import' => Import\MangaRole::class,
            'API' => API\MangaRole::class,
            'IB_CODE' => 'roles',
            'PRIORITY' => 300,
        ],
        'People' => [
            'Import' => Import\People::class,
            'API' => API\People::class,
            'IB_CODE' => 'people',
            'PRIORITY' => 400,
        ],
        'Character' => [
            'Import' => Import\Character::class,
            'API' => API\Character::class,
            'IB_CODE' => 'characters',
            'PRIORITY' => 400,
        ],
        'Genre' => [
            'Import' => Import\Genre::class,
            'API' => API\Genre::class,
            'IB_CODE' => 'genres',
            'PRIORITY' => 200,
        ],
        'Publisher' => [
            'Import' => Import\Publisher::class,
            'API' => API\Publisher::class,
            'IB_CODE' => 'publishers',
            'PRIORITY' => 200,
        ],
        'Studio' => [
            'Import' => Import\Studio::class,
            'API' => API\Studio::class,
            'IB_CODE' => 'studios',
            'PRIORITY' => 200,
        ],
        'Video' => [
            'Import' => Import\Video::class,
            'API' => API\Video::class,
            'IB_CODE' => 'videos',
            'PRIORITY' => 500,
        ],
        'Translation' => [
            'Import' => Import\Translation::class,
            'API' => API\Translation::class,
            'IB_CODE' => 'translations',
            'PRIORITY' => 50,
        ],
        'Episode' => [
            'Import' => Import\Episode::class,
            'API' => API\Episode::class,
            'IB_CODE' => 'episodes',
            'PRIORITY' => 50,
        ],
        'Franchise' => [
            'Import' => Import\Franchise::class,
            'API' => API\Franchise::class,
            'IB_CODE' => 'franchises',
            'PRIORITY' => 100,
        ],
    ];

    public static int $maxScreenshots = 5;

    public static function load(int $limit)
    {
        CModule::IncludeModule('iblock');

        $entity = ShikimoriLoadTable::getEntity();
        $query = new Query($entity);
        $query
            ->setLimit($limit)
            ->setFilter([
                'TYPE' => array_keys(static::$types),
                '=IS_LOAD' => false,
            ])
            ->setOrder([
                'PRIORITY' => 'ASC',
            ])
            ->setSelect(['*'])
        ;

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
        return static::$types[$type]['IB_CODE'] ?: '';
    }

    public static function getPriority(string $type): string
    {
        return static::$types[$type]['PRIORITY'] ?: '';
    }

    public static function getIBID(string $type): int
    {
        $result = static::$types[$type]['IB_ID'];

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

        return static::$types[$type]['IB_ID'] = $result;
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