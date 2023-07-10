<?php


namespace Umino\Anime\Shikimori;


use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ORM\Query\Query;
use CIBlockElement;
use CUtil;
use Umino\Anime\Core;
use Umino\Anime\Shikimori\API\Anime;
use Umino\Anime\Shikimori\API\Genre;
use Umino\Anime\Shikimori\API\People;

class Import
{
    protected static CIBlockElement $element;

    public static function start()
    {
        $anime = new Anime(1);

        $fields = $anime->get();
        $fields['roles'] = static::roles($anime->roles());
//        $fields['screenshots'] = static::screenshots($anime->screenshots(1));
        $fields['genres'] = static::genres(array_column($fields['genres'], 'id'));

        $fields['studios'] = array_column($fields['studios'], 'id');
        $fields['videos'] = array_column($fields['videos'], 'id');

        Core::keysToUpperCase($fields);

        $fields = [
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'DETAIL_PICTURE' => $fields['IMAGE'],
            'DETAIL_TEXT' => $fields['DESCRIPTION'],
            'PROPERTY_VALUES' => [
                'NAME_ORIGIN' => $fields['NAME'],
                'NAME_OTHER' => array_merge(
                    [$fields['LICENSE_NAME_RU']],
                    $fields['ENGLISH'],
                    $fields['SYNONYMS'],
                    $fields['JAPANESE'],
                ),
                'SCREENSHOTS' => $fields['SCREENSHOTS'],
                'FRANCHISE' => $fields['FRANCHISE'],
                'TYPE' => $fields['KIND'],
                'SCORE' => $fields['SCORE'],
                'STATUS' => $fields['STATUS'],
                'EPISODES' => $fields['EPISODES'],
                'EPISODES_AIRED' => $fields['EPISODES_AIRED'],
                'AIRED_ON' => $fields['AIRED_ON'],
                'RELEASED_ON' => $fields['RELEASED_ON'],
                'RATING' => $fields['RATING'],
                'DURATION' => $fields['DURATION'],
                'LICENSORS' => $fields['LICENSORS'],
                'GENRES' => $fields['GENRES'],
                'STUDIOS' => $fields['STUDIOS'],
                'VIDEOS' => $fields['VIDEOS'],
                'CHARACTERS' => $fields['ROLES']['CHARACTERS'],
                'PEOPLE' => $fields['ROLES']['PERSONS'],
            ]
        ];

        pre($fields);
    }

    protected static function people(array $people)
    {
        pre($people);
        die;
    }

    protected static function roles(array $roles): array
    {
        $ids = [];

        foreach ($roles as $key => &$items) {
            $max = 5;
            foreach ($items as &$item) {
                if ($max <= 0) break;
                $ids[$key][] = new $key($item['person']);
                $max--;
            }
        }

        static::people(People::getAsync());

        die;

        return $roles;
    }

    protected static function screenshots(array $images): array
    {
        foreach ($images as &$image) {
            $image = \CFile::MakeFileArray($image);
        }

        return $images;
    }

    protected static function genres(array $ids): array
    {
        $items = array_diff($ids, static::get(13, $ids));

        if (empty($items)) return $ids;

        foreach ($items as &$id) {
            $id = new Genre($id);
        } unset($id);

        $items = Genre::getAsync();

        foreach ($items as $item) {
            static::add([
                'XML_ID' => $item['id'],
                'IBLOCK_ID' => 13,
                'CODE' => Cutil::translit(
                    implode('-', [$item['id'], $item['russian'] ?: $item['name']]),
                    'ru',
                    [
                        'max_len' => 255,
                        'change_case' => 'L',
                        'replace_space' => '-',
                        'replace_other' => '-',
                        'delete_repeat_replace ' => true,
                        'safe_chars' => '',
                    ]
                ),
                'NAME' => $item['russian'] ?: $item['name'],
                'PROPERTY_VALUES' => [
                    'NAME_ORIGIN' => $item['name'],
                ]
            ]);
        }

        return $ids;
    }

    protected static function get(int $iblock, array $xmlIds): array
    {
        $entity = ElementTable::getEntity();
        $query = new Query($entity);
        $query
            ->setFilter([
                'IBLOCK_ID' => $iblock,
                'XML_ID' => $xmlIds
            ])
            ->setSelect(['XML_ID'])
        ;

        return array_column($query->fetchAll(), 'XML_ID');
    }

    protected static function add(array $fields)
    {
        if (empty(static::$element)) {
            static::$element = new CIBlockElement();
        }
        static::$element->Add($fields);
    }
}