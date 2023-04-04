<?php


namespace Umino\Anime;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ORM\Fields\ArrayField;
use Bitrix\Main\Type\DateTime;
use CIBlockElement;
use CModule;
use CUtil;
use Umino\Anime\Parsers\ParserShikimori;
use Umino\Anime\Parsers\ParserWorldArt;
use Umino\Anime\Tables\DataTable;
use Umino\Anime\Tables\Info;
use Umino\Anime\Tables\InfoTable;
use Umino\Anime\Tables\KodikResultTable;
use Umino\Anime\Tables\TranslationTable;

class Import
{
    protected static $iblockId;

    public static function start(bool $loadImages = false, int $limit = 0)
    {
        if (!self::$iblockId = Core::getIBlock()) {
            Logger::log('В настройках модуля не выбран инфоблок для заполнения.');
            return;
        }

        CModule::IncludeModule('iblock');

        $kodikResults = KodikResultTable::getList([
            'limit' => $limit > 0 ? $limit : Core::getFillElementCount(),
            'order' => ['DATE_IMPORT' => 'ASC'],
        ])->fetchAll();

        if (empty($kodikResults)) return;

        $videoIds = [];

        foreach ($kodikResults as $kodikResult) {

            $videoIds[] = $videoId = self::addInfo($kodikResult);

            $translationId = self::addTranslation($kodikResult);

            self::addData($kodikResult, $videoId, $translationId);

            $fields = [
                'DATE_IMPORT' => new DateTime()
            ];

            $result = KodikResultTable::update($kodikResult['ID'], $fields);

            Logger::log([
                'message' => $result->getErrorMessages(),
                'fields' => array_merge(['ID' => $kodikResult['ID']], $fields)
            ]);
        }

        $elementIds = self::addElements($videoIds);

        if ($elementIds && $loadImages) {
            self::loadImages(array_unique($elementIds));
        }
    }

    protected static function addInfo(array $item): int
    {
        $fields = [
            'XML_ID' => md5($item['TITLE'] . $item['YEAR']),
            'TYPE' => $item['TYPE'],
            'TITLE' => $item['TITLE'],
            'TITLE_ORIGINAL' => $item['TITLE_ORIG'],
            'TITLE_OTHER' => $item['OTHER_TITLE'],
            'YEAR' => $item['YEAR'],
            'SEASON' => $item['LAST_SEASON'],
            'KODIK_ID' => $item['ID'],
            'SHIKIMORI_ID' => $item['SHIKIMORI_ID']?:'',
            'WORLDART_LINK' => $item['WORLDART_LINK']?:'',
            'KINOPOISK_ID' => $item['KINOPOISK_ID']?:'',
            'IMDB_ID' => $item['IMDB_ID']?:'',
        ];

        $infoObject = InfoTable::getList(['filter'=>['XML_ID'=>$fields['XML_ID']]])->fetchObject();

        if ($infoObject && $infoId = $infoObject->getId())  {
            $result = InfoTable::update($infoId, $fields);
        } else {
            $result = InfoTable::add($fields);
            $infoId = $result->getId();
        }

        Logger::log([
            'message' => $result->getErrorMessages(),
            'fields' => array_merge(['ID' => $infoId], $fields)
        ]);

        return $infoId;
    }

    protected static function addTranslation(array $item)
    {
        $fields = [
            'XML_ID' => md5($item['TRANSLATION']['TITLE'] . $item['TRANSLATION']['TYPE']),
            'TITLE' => $item['TRANSLATION']['TITLE'],
            'KODIK_ID' => $item['TRANSLATION']['ID'],
            'TYPE' => $item['TRANSLATION']['TYPE'],
        ];

        $translationObject = TranslationTable::getList(['filter'=>['XML_ID'=>$fields['XML_ID']]])->fetchObject();

        if ($translationObject && $translationId = $translationObject->getId()) {
            $result = TranslationTable::update($translationId, $fields);
        } else {
            $result = TranslationTable::add($fields);
            $translationId = $result->getId();
        }

        Logger::log([
            'message' => $result->getErrorMessages(),
            'fields' => array_merge(['ID' => $translationId], $fields)
        ]);

        return $translationId;
    }

    protected static function addData(array $item, int $videoId, int $translationId)
    {
        $title = $item['TITLE'] . ' (' . $item['TRANSLATION']['TITLE'] . ')';

        $fields = [
            'XML_ID' => md5($title),
            'TITLE' => $title,
            'INFO_ID' => $videoId,
            'TRANSLATION_ID' => $translationId,
            'EPISODES' => $item['LAST_EPISODE'],
            'EPISODES_ALL' => $item['EPISODES_COUNT'],
            'QUALITY' => $item['QUALITY'],
            'LINK' => $item['LINK'],
            'SCREENSHOTS' => $item['SCREENSHOTS'],
        ];

        $dataObject = DataTable::getList(['filter'=>['XML_ID'=>$fields['XML_ID']]])->fetchObject();

        if ($dataObject && $dataId = $dataObject->getId()) {
            $result = DataTable::update($dataId, $fields);
        } else {
            $result = DataTable::add($fields);
            $dataId = $result->getId();
        }

        Logger::log([
            'message' => $result->getErrorMessages(),
            'fields' => array_merge(['ID' => $dataId], $fields)
        ]);

        return $dataId;
    }

    protected static function addElements(array $videoIds): array
    {
        $elementIds = [];

        $element = new CIBlockElement;

        $infoList = InfoTable::getList([
            'filter' => ['ID' => $videoIds],
        ]);

        /** @var Info $infoObject */
        while ($info = $infoList->fetch()) {

            $fields = [
                'IBLOCK_SECTION_ID' => false,
                'IBLOCK_ID' => Core::getIBlock(),
                'NAME' => $info['TITLE'],
                'CODE' => Cutil::translit($info['TITLE'], 'ru', ['replace_space' => '-','replace_other' => '-']),
                'XML_ID' => $info['XML_ID'],
                'DETAIL_TEXT_TYPE' => 'html',
            ];

            $properties = [
                'TITLE_ORIGINAL' => $info['TITLE_ORIGINAL'],
                'TITLE_OTHER' => $info['TITLE_OTHER'],
                'YEAR' => $info['YEAR'],
                'SEASON' => $info['SEASON'],
                'TRANSLATIONS' => [],
                'EPISODES' => 1,
                'SCREENSHOTS' => [],
            ];

            $dataList = DataTable::getList([
                'filter' => ['INFO_ID' => $info['ID']],
                'select' => [
                    'TRANSLATION_TITLE' => 'TRANSLATION.TITLE',
                    'EPISODES',
                    'SCREENSHOTS',
                ]
            ])->fetchAll();

            foreach ($dataList as $data) {
                $properties['TRANSLATIONS'][] = $data['TRANSLATION_TITLE'];
                $properties['EPISODES'] = (int) $data['EPISODES'] > $properties['EPISODES'] ? $data['EPISODES'] : $properties['EPISODES'];

                if (empty($properties['SCREENSHOTS'])) {
                    $properties['SCREENSHOTS'] = $data['SCREENSHOTS'];
                }
            }

            $fields['PROPERTY_VALUES'] = $properties;

            $elementObject = ElementTable::getList([
                'filter' => [
                    'IBLOCK_ID' => Core::getIBlock(),
                    [
                        'LOGIC' => 'OR',
                        ['XML_ID' => $info['XML_ID']],
                        ['CODE' => $fields['CODE']],
                    ]

                ]
            ])->fetchObject();

            if ($elementObject && $elementId = $elementObject->getId()) {
                if (!$element->Update($elementId, $fields)) {
                    Logger::log([
                        'message' => $element->LAST_ERROR,
                        'element_id' => $elementId,
                        'fields' => $fields
                    ]);
                }
            } else {
                $fields = array_merge($fields, ['ACTIVE' => 'N']);
                if (empty($elementId = $element->Add($fields))) {
                    Logger::log([
                        'message' => $element->LAST_ERROR,
                        'fields' => $fields,
                    ]);

                    continue;
                }
            }

            $fields = ['IBLOCK_ELEMENT_ID' => $elementId];

            $result = InfoTable::update($info['ID'], $fields);
            Logger::log([
                'message' => $result->getErrorMessages(),
                'fields' => array_merge(['ID' => $info['ID']], $fields)
            ]);

            $elementIds[] = $elementId;
        }

        return $elementIds;
    }

    protected static function getParseStages(): array
    {
        return [
            ParserShikimori::class => 'SHIKIMORI_ID',
            ParserWorldArt::class => 'WORLDART_LINK',
        ];
    }

    protected static function parseData(array $stageIds): array
    {
        $fields = [
            'PREVIEW_PICTURE' => 'getImage',
            'DETAIL_TEXT' => 'getDescription',
            'PROPERTY_VALUES' => [
                'GENRES' => 'getGenres',
                'TYPE' => 'getType',
                'EPISODES_ALL' => 'getEpisodes',
                'EPISODE_DURATION' => 'getEpisodeDuration',
            ],
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
                if (empty($method)) {
                    unset($fields[$fieldName]);
                } else if (is_array($method)) {
                    foreach ($method as $name => $value) {
                        if (!empty($params[$fieldName][$name] = $parser->{$value}())){
                            unset($fields[$fieldName][$name]);
                        }
                    }
                } else if (!empty($params[$fieldName] = $parser->{$method}())) {
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

            if ($stageIds && $fields = self::parseData($stageIds)) {
                
                CIBlockElement::SetPropertyValuesEx($item['IBLOCK_ELEMENT_ID'], Core::getIBlock(), $fields['PROPERTY_VALUES']);

                unset($fields['PROPERTY_VALUES']);

                if (!$element->Update($item['IBLOCK_ELEMENT_ID'], $fields)) {
                    Logger::log([
                        'message' => 'Элемент инфоблока не обновлен.',
                        'fields' => array_merge(['ID' => $item['IBLOCK_ELEMENT_ID']], $fields)
                    ]);
                }
            }

            usleep(200000);
        }

        $itemList = $element::GetList(
            [],
            [
                'ACTIVE' => 'N',
                '!PREVIEW_PICTURE' => false,
            ],
            false,
            false,
            ['ID']
        );

        while ($item = $itemList->GetNext()) {
            $fields = ['ACTIVE' => 'Y'];
            if (!$element->Update($item['ID'], $fields)) {
                Logger::log([
                    'message' => 'Элемент инфоблока не обновлен.',
                    'fields' => array_merge(['ID' => $item['ID']], $fields)
                ]);
            }
        }
    }
}