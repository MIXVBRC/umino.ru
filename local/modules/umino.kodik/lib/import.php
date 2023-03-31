<?php


namespace Umino\Kodik;


use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Type\DateTime;
use CIBlockElement;
use CModule;
use CUtil;
use Umino\Kodik\Parser\ParserShikimori;
use Umino\Kodik\Parser\ParserWorldArt;
use Umino\Kodik\Tables\DataTable;
use Umino\Kodik\Tables\ImportResultsTable;
use Umino\Kodik\Tables\InfoTable;
use Umino\Kodik\Tables\TranslationTable;

class Import
{
    public static function start(int $count = 0)
    {
        CModule::IncludeModule('iblock');

        $element = new CIBlockElement;

        if ($count <= 0) {
            $count = ImportResultsTable::getCount();
        }

        $limit = Core::getAPILimit();

        $pageCount = ceil($count / $limit);

        for ($page = 0; $page < $pageCount; $page++) {

            $itemList = ImportResultsTable::getList([
                'limit' => $limit,
                'offset' => $page * $limit,
            ])->fetchAll();

            foreach ($itemList as $item) {

                $elementId = self::addElement($item['RESULTS'], $element);

                if (!$elementId) continue;

                $videoId = self::addVideo($item['RESULTS'], $elementId);

                $translationId = self::addTranslation($item['RESULTS']);

                self::addData($item['RESULTS'], $videoId, $translationId);
            }
        }

        self::loadImages();
    }

    protected static function addElement(array $item, CIBlockElement $element): int
    {
        $xml_id = md5($item['TITLE']);

        $id = ElementTable::getList([
            'filter' => [
                'IBLOCK_ID' => Core::getIBlock(),
                'XML_ID' => $xml_id,
            ],
            'select' => ['ID'],
            'limit' => 1,
        ])->fetch()['ID'];

        if ($id) return $id;

        $fields = [
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => Core::getIBlock(),
            'NAME' => $item['TITLE'],
            'CODE' => Cutil::translit($item['TITLE'], 'ru', ['replace_space' => '-','replace_other' => '-']),
            'XML_ID' => $xml_id,
            'ACTIVE' => 'N',
            'DETAIL_TEXT_TYPE' => 'html',
        ];

        return $element->Add($fields);
    }

    protected static function addVideo(array $item, int $elementId): int
    {
        $xml_id = md5($item['TITLE']);

        $id = InfoTable::getList([
            'filter' => ['XML_ID'=>$xml_id],
            'select' => ['ID'],
            'limit' => 1,
        ])->fetch()['ID'];

        if ($id)  {
            InfoTable::update($id, [
                'IBLOCK_ELEMENT_ID' => $elementId,
            ]);
            return $id;
        }

        $fields = [
            'XML_ID' => $xml_id,
            'TYPE' => $item['TYPE'],
            'TITLE' => $item['TYPE'],
            'TITLE_ORIGINAL' => $item['TITLE_ORIG'],
            'TITLE_OTHER' => explode('/', str_replace(' / ', '/', $item['OTHER_TITLE'])),
            'YEAR' => $item['YEAR'],
            'KODIK_ID' => $item['ID'],
            'SHIKIMORI_ID' => $item['SHIKIMORI_ID']?:'',
            'WORLDART_LINK' => $item['WORLDART_LINK']?:'',
            'KINOPOISK_ID' => $item['KINOPOISK_ID']?:'',
            'IMDB_ID' => $item['IMDB_ID']?:'',
            'IBLOCK_ELEMENT_ID' => $elementId,
        ];

        $id = InfoTable::add($fields);

        return $id->getId();
    }

    protected static function addTranslation(array $item)
    {
        $xml_id = md5($item['TRANSLATION']['TITLE'].$item['TRANSLATION']['TYPE']);

        $id = TranslationTable::getList([
            'filter' => ['XML_ID'=>$xml_id],
            'select' => ['ID'],
            'limit' => 1,
        ])->fetch()['ID'];

        if ($id) return $id;

        $fields = [
            'XML_ID' => $xml_id,
            'TITLE' => $item['TRANSLATION']['title'],
            'KODIK_ID' => $item['TRANSLATION']['id'],
            'TYPE' => $item['TRANSLATION']['type'],
        ];

        $id = TranslationTable::add($fields);

        return $id->getId();
    }

    protected static function addData(array $item, int $videoId, int $translationId)
    {
        $xml_id = md5($item['TITLE'] . $item['TRANSLATION']['TITLE']);

        $fields = [
            'XML_ID' => $xml_id,
            'TITLE' => $item['TITLE'] . ' (' . $item['TRANSLATION']['TITLE'] . ')',
            'INFO_ID' => $videoId,
            'TRANSLATION_ID' => $translationId,
            'SEASON' => $item['LAST_SEASON'],
            'EPISODES' => $item['LAST_EPISODE'],
            'EPISODES_ALL' => $item['EPISODES_COUNT'],
            'QUALITY' => $item['QUALITY'],
            'KODIK_DATE_CREATE' => DateTime::createFromTimestamp(strtotime($item['CREATED_AT'])),
            'KODIK_DATE_UPDATE' => DateTime::createFromTimestamp(strtotime($item['UPDATED_AT'])),
            'LINK' => $item['LINK'],
            'SCREENSHOTS' => $item['SCREENSHOTS'],
        ];

        $id = DataTable::getList([
            'filter' => ['XML_ID'=>$xml_id],
            'select' => ['ID'],
            'limit' => 1,
        ])->fetch()['ID'];

        if ($id) {
            DataTable::update($id, $fields);
        } else {
            DataTable::add($fields);
        }
    }

    public static function getParseStages(): array
    {
        return [
            ParserShikimori::class => 'SHIKIMORI_ID',
//            ParserWorldArt::class => 'WORLDART_LINK',
        ];
    }

    protected static function parseData(array $stageIds): array
    {
        $fields = [
            'PREVIEW_PICTURE' => 'getImage',
            'DETAIL_TEXT' => 'getDescription',
        ];

        $params = [];

        foreach ($stageIds as $stage => $id) {

            if (empty($id) || empty($fields)) continue;

            switch ($stage) {
                case ParserShikimori::class:
                    $parser = new ParserShikimori(ParserShikimori::$url.$id);
                    break;
                case ParserWorldArt::class:
                    $parser = new ParserWorldArt($id);
                    break;
                default:
                    $parser = null;
            }

            if (is_null($parser)) {
                continue;
            }

            foreach ($fields as $fieldName => $method) {
                if (!empty($params[$fieldName] = $parser->{$method}())) {
                    unset($fields[$fieldName]);
                }
            }
        }

        return $params;
    }

    public static function loadImages(array $ids = [])
    {
        $element = new CIBlockElement();

        if ($ids) {
            $filter = ['IBLOCK_ELEMENT_ID' => $ids];
        } else {
            $filter = [
                [
                    'LOGIC' => 'OR',
                    ['IBLOCK_ELEMENT_PREVIEW_PICTURE' => false],
                    ['IBLOCK_ELEMENT_DETAIL_TEXT' => false],
                ]
            ];
        }

        $itemList = InfoTable::getList([
            'filter' => $filter,
            'select' => [
                'SHIKIMORI_ID',
                'WORLDART_LINK',
                'IBLOCK_ELEMENT_ID',
                'IBLOCK_ELEMENT_PREVIEW_PICTURE' => 'IBLOCK_ELEMENT.PREVIEW_PICTURE',
                'IBLOCK_ELEMENT_DETAIL_TEXT' => 'IBLOCK_ELEMENT.DETAIL_TEXT',
            ]
        ])->fetchAll();

        foreach ($itemList as $item) {

            $stageIds = [];
            foreach (self::getParseStages() as $stage => $fieldName) {
                $stageIds[$stage] = $item[$fieldName];
            }

            if ($stageIds && $params = self::parseData($stageIds)) {
                $element->Update($item['IBLOCK_ELEMENT_ID'], $params);
            }
        }

        $itemList = ElementTable::getList([
            'filter' => [
                'ACTIVE' => 'N',
                '!PREVIEW_PICTURE' => false,
            ],
            'select' => ['ID'],
        ])->fetchAll();

        foreach ($itemList as $item) {
            $element->Update($item['ID'], ['ACTIVE' => 'Y']);
        }
    }
}