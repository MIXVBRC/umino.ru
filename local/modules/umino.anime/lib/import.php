<?php


namespace Umino\Anime;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;
use CIBlockElement;
use CModule;
use CUtil;
use Umino\Anime\Parsers\ParserShikimori;
use Umino\Anime\Parsers\ParserWorldArt;
use Umino\Anime\Tables\DataTable;
use Umino\Anime\Tables\EpisodesTable;
use Umino\Anime\Tables\Info;
use Umino\Anime\Tables\InfoTable;
use Umino\Anime\Tables\KodikResultTable;
use Umino\Anime\Tables\TranslationTable;

class Import
{
    protected static $iblockId;

    protected static $infoFields = [];
    protected static $translationFields = [];
    protected static $dataFields = [];

    public static function start(array $results): bool
    {
        if (!self::$iblockId = Core::getIBlock()) {
            Logger::log('В настройках модуля не выбран инфоблок для заполнения.');
            return false;
        } else if (empty($results)) {
            Logger::log('Пустой массив.');
            return false;
        }



        foreach ($results as $result) {
            $xml_id = md5($result['TITLE_ORIG'] . $result['YEAR']);
        }



        CModule::IncludeModule('iblock');

        $kodikResults = KodikResultTable::getList([
            'filter' => [
                '>DATE_UPDATE' => 'KODIK_RESULT.DATE_IMPORT'
            ],
            'runtime' => [
                'KODIK_RESULT' => [
                    'data_type' => KodikResultTable::class,
                    'reference' => [
                        '=this.ID' => 'ref.ID',
                    ],
                    'join_type' => 'inner',
                ]
            ],
            'order' => ['DATE_IMPORT' => 'ASC'],
            'limit' => $limit > 0 ? $limit : Core::getFillElementCount(),
        ])->fetchAll();

        if (empty($kodikResults)) return;

        $infoIds = [];

        foreach ($kodikResults as $kodikResult) {

            $infoXmlId = self::addInfoFields($kodikResult);
            $translationXmlId = self::addTranslationFields($kodikResult);
            $dataXmlId = self::addDataFields($kodikResult);

            $infoIds[] = $infoId = self::addInfo($kodikResult);

            $translationId = self::addTranslation($kodikResult);

            self::addData($kodikResult, $infoId, $translationId);

            $fields = [
                'DATE_IMPORT' => new DateTime()
            ];

            $result = KodikResultTable::update($kodikResult['ID'], $fields);

            if (!$result->isSuccess()) {
                Logger::log([
                    'message' => $result->getErrorMessages(),
                    'fields' => array_merge(['ID' => $kodikResult['ID']], $fields)
                ]);
            }
        }

        self::addElements($infoIds);

        return true;
    }

    public static function start2(bool $loadImages = false, int $limit = 0)
    {
        if (!self::$iblockId = Core::getIBlock()) {
            Logger::log('В настройках модуля не выбран инфоблок для заполнения.');
            return;
        }

        CModule::IncludeModule('iblock');

        $kodikResults = KodikResultTable::getList([
            'filter' => [
                '>DATE_UPDATE' => 'KODIK_RESULT.DATE_IMPORT'
            ],
            'runtime' => [
                'KODIK_RESULT' => [
                    'data_type' => KodikResultTable::class,
                    'reference' => [
                        '=this.ID' => 'ref.ID',
                    ],
                    'join_type' => 'inner',
                ]
            ],
            'order' => ['DATE_IMPORT' => 'ASC'],
            'limit' => $limit > 0 ? $limit : Core::getFillElementCount(),
        ])->fetchAll();

        if (empty($kodikResults)) return;

        $infoIds = [];

        foreach ($kodikResults as $kodikResult) {

            $infoXmlId = self::addInfoFields($kodikResult);
            $translationXmlId = self::addTranslationFields($kodikResult);
            $dataXmlId = self::addDataFields($kodikResult);

            $infoIds[] = $infoId = self::addInfo($kodikResult);

            $translationId = self::addTranslation($kodikResult);

            self::addData($kodikResult, $infoId, $translationId);

            $fields = [
                'DATE_IMPORT' => new DateTime()
            ];

            $result = KodikResultTable::update($kodikResult['ID'], $fields);

            if (!$result->isSuccess()) {
                Logger::log([
                    'message' => $result->getErrorMessages(),
                    'fields' => array_merge(['ID' => $kodikResult['ID']], $fields)
                ]);
            }
        }

        $elementIds = self::addElements($infoIds);

        if ($elementIds && $loadImages) {
            self::loadImages(array_unique($elementIds));
        }
    }

    protected static function getDiscrepancy(array $newArray, array $oldArray): array
    {
        $result = [];

        foreach ($newArray as $key => $value) {

            if ($oldArray[$key] == $value) continue;

            $result[$key] = $value;
        }

        return $result;
    }

    protected static function addInfoFields(array $item): string
    {
        $xml_id = md5($item['TITLE_ORIG'] . $item['YEAR']);

        self::$infoFields[$xml_id] = [
            'XML_ID' => $xml_id,
            'TYPE' => $item['TYPE'],
            'TITLE' => $item['TITLE'],
            'TITLE_ORIGINAL' => $item['TITLE_ORIG'],
            'TITLE_OTHER' => $item['OTHER_TITLE'],
            'YEAR' => $item['YEAR'],
            'SEASON' => $item['LAST_SEASON'],
            'KODIK_ID' => $item['KODIK_ID'],
            'SHIKIMORI_ID' => $item['SHIKIMORI_ID']?:'',
            'WORLDART_LINK' => $item['WORLDART_LINK']?:'',
            'KINOPOISK_ID' => $item['KINOPOISK_ID']?:'',
            'IMDB_ID' => $item['IMDB_ID']?:'',
        ];

        return $xml_id;
    }

    protected static function addTranslationFields(array $item): string
    {
        $xml_id = md5($item['TRANSLATION']['TITLE'] . $item['TRANSLATION']['TYPE']);

        self::$translationFields[$xml_id] = [
            'XML_ID' => $xml_id,
            'TITLE' => $item['TRANSLATION']['TITLE'],
            'KODIK_ID' => $item['TRANSLATION']['ID'],
            'TYPE' => $item['TRANSLATION']['TYPE'],
        ];

        return $xml_id;
    }

    protected static function addDataFields(array $item): string
    {
        $xml_id = md5($item['TITLE_ORIG'] . $item['TRANSLATION']['ID']);

        self::$dataFields[$xml_id] = [
            'XML_ID' => $xml_id,
            'TITLE' => $item['TITLE'] . ' (' . $item['TRANSLATION']['TITLE'] . ')',
            'INFO_ID' => '',
            'TRANSLATION_ID' => '',
            'EPISODES' => $item['LAST_EPISODE'],
            'EPISODES_ALL' => $item['EPISODES_COUNT'],
            'QUALITY' => $item['QUALITY'],
            'LINK' => $item['LINK'],
            'SCREENSHOTS' => $item['SCREENSHOTS'],
        ];

        return $xml_id;
    }

    protected static function addOrUpdate($class, array $fields)
    {
        if (!is_object($class)) return false;

        if ($oldFields && $infoId = $oldFields['ID'])  {
            $discrepancy = self::getDiscrepancy($fields, $oldFields);
            if ($discrepancy) {
                $result = $class::update($infoId, $discrepancy);
            }
        } else {
            $result = $class::add($fields);
            $infoId = $result->getId();
        }

        return $infoId;
    }

    protected static function addInfo(array $item): int
    {
        $xml_id = md5($item['TITLE_ORIG'] . $item['YEAR']);

        $fields = [
            'XML_ID' => $xml_id,
            'TYPE' => $item['TYPE'],
            'TITLE' => $item['TITLE'],
            'TITLE_ORIGINAL' => $item['TITLE_ORIG'],
            'TITLE_OTHER' => $item['OTHER_TITLE'],
            'YEAR' => $item['YEAR'],
            'SEASON' => $item['LAST_SEASON'],
            'KODIK_ID' => $item['KODIK_ID'],
            'SHIKIMORI_ID' => $item['SHIKIMORI_ID']?:'',
            'WORLDART_LINK' => $item['WORLDART_LINK']?:'',
            'KINOPOISK_ID' => $item['KINOPOISK_ID']?:'',
            'IMDB_ID' => $item['IMDB_ID']?:'',
        ];

        $result = new Result();

        $info = InfoTable::getList(['filter'=>['XML_ID'=>$xml_id]])->fetch();

        if ($info && $infoId = $info['ID'])  {
            $discrepancy = self::getDiscrepancy($fields, $info);
            if ($discrepancy) {
                $result = InfoTable::update($infoId, $discrepancy);
            }
        } else {
            $result = InfoTable::add($fields);
            $infoId = $result->getId();
        }

        if (!$result->isSuccess()) {
            Logger::log([
                'message' => $result->getErrorMessages(),
                'fields' => array_merge(['ID' => $infoId], $fields)
            ]);
        }

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

        $result = new Result();

        $translation = TranslationTable::getList(['filter'=>['XML_ID'=>$fields['XML_ID']]])->fetch();

        if ($translation && $translationId = $translation['ID']) {
            $discrepancy = self::getDiscrepancy($fields, $translation);
            if ($discrepancy) {
                $result = TranslationTable::update($translationId, $discrepancy);
            }
        } else {
            $result = TranslationTable::add($fields);
            $translationId = $result->getId();
        }

        if (!$result->isSuccess()) {
            Logger::log([
                'message' => $result->getErrorMessages(),
                'fields' => array_merge(['ID' => $translationId], $fields)
            ]);
        }

        return $translationId;
    }

    protected static function addData(array $item, int $infoId, int $translationId)
    {
        $xml_id = md5($item['TITLE_ORIG'] . $item['TRANSLATION']['ID']);

        $fields = [
            'XML_ID' => $xml_id,
            'TITLE' => $item['TITLE'] . ' (' . $item['TRANSLATION']['TITLE'] . ')',
            'INFO_ID' => $infoId,
            'TRANSLATION_ID' => $translationId,
            'EPISODES' => $item['LAST_EPISODE'],
            'EPISODES_ALL' => $item['EPISODES_COUNT'],
            'QUALITY' => $item['QUALITY'],
            'LINK' => $item['LINK'],
            'SCREENSHOTS' => $item['SCREENSHOTS'],
        ];

        $result = new Result();

        $data = DataTable::getList(['filter'=>['XML_ID'=>$fields['XML_ID']]])->fetch();

        if ($data && $dataId = $data['ID']) {
            $discrepancy = self::getDiscrepancy($fields, $data);
            if ($discrepancy) {
                $result = DataTable::update($dataId, $discrepancy);
            }
        } else {
            $result = DataTable::add($fields);
            $dataId = $result->getId();
        }

        if (!$result->isSuccess()) {
            Logger::log([
                'message' => $result->getErrorMessages(),
                'fields' => array_merge(['ID' => $dataId], $fields)
            ]);
        } else {

            $episodes = EpisodesTable::getList([
                'filter' => ['RESULT_ID' => $item['ID']],
                'select' => ['ID', 'DATA_ID'],
            ])->fetchAll();

            foreach ($episodes as $episode) {

                if ($episode['DATA_ID'] == $dataId) continue;

                $fields = ['DATA_ID' => $dataId];
                $update = EpisodesTable::update($episode['ID'], $fields);
                if (!$update->isSuccess()) {
                    Logger::log([
                        'message' => $update->getErrorMessages(),
                        'fields' => array_merge(['ID' => $episode['ID']], $fields)
                    ]);
                }
                usleep(5000);
            }
        }

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
            if (!$result->isSuccess()) {
                Logger::log([
                    'message' => $result->getErrorMessages(),
                    'fields' => array_merge(['ID' => $info['ID']], $fields)
                ]);
            }

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