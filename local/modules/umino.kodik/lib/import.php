<?php


namespace Umino\Kodik;


use Bitrix\Iblock\ElementTable;
use CIBlockElement;
use CModule;
use COption;
use CUtil;
use Umino\Kodik\Parser\ParserShikimori;
use Umino\Kodik\Parser\ParserWorldArt;

class Import
{
    protected $iblockVideo;
    protected $iblockTranslation;
    protected $iblockData;

    protected $element;

    protected static $stages = [
        ParserShikimori::class => 'SHIKIMORI_ID',
        ParserWorldArt::class => 'WORLDART_ID',
    ];

    public function __construct()
    {
        CModule::IncludeModule('iblock');

        $this->iblockVideo = $this->getIBlockVideo();
        $this->iblockTranslation = $this->getIBlockTranslation();
        $this->iblockData = $this->getIBlockData();

        $this->element = new CIBlockElement;
    }

    protected function getIBlockVideo(): int
    {
        return COption::GetOptionString("umino.kodik", "iblock_video_id");
    }

    protected function getIBlockTranslation(): int
    {
        return COption::GetOptionString("umino.kodik", "iblock_translation_id");
    }

    protected function getIBlockData(): int
    {
        return COption::GetOptionString("umino.kodik", "iblock_data_id");
    }

    protected function getByXmlId(int $iblockId, string $xmlId)
    {
        return ElementTable::getList([
            'filter' => [
                'IBLOCK_ID' => $iblockId,
                'XML_ID' => $xmlId,
            ],
            'select' => ['ID'],
            'limit' => 1,
        ])->fetch()['ID'];
    }

    public function start(array $itemList)
    {
        foreach ($itemList as $item) {

            $videoId = $this->addVideo($item);

            $translationId = $this->addTranslation($item);

            $this->addData($item, $videoId, $translationId);
        }
    }

    protected function addVideo(array $item)
    {
        $title = $item['title'];
        $xml_id = md5($title);

        if ($id = $this->getByXmlId($this->iblockVideo, $xml_id)) return $id;

        $params = [
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $this->getIBlockVideo(),
            'PROPERTY_VALUES'=> [
                'KODIK_ID' => $item['id'],
                'YEAR' => $item['year'],
                'KINOPOISK_ID' => $item['kinopoisk_id'],
                'TITLE_ORIGINAL' => $item['title_orig'],
                'TITLE_OTHER' => explode('/', str_replace(' / ', '/', $item['other_title'])),
                'TYPE' => $item['type'],
                'IMDB_ID' => $item['imdb_id'],
                'WORLDART_ID' => explode('id=',$item['worldart_link'])[1],
                'SHIKIMORI_ID' => $item['shikimori_id'],
            ],
            'NAME' => $title,
            'CODE' => Cutil::translit($title, 'ru', ['replace_space' => '-','replace_other' => '-']),
            'ACTIVE' => 'Y',
            'XML_ID' => $xml_id,
            'DETAIL_TEXT_TYPE' => 'html',
        ];

        $stageIds = [];
        foreach (self::$stages as $stage => $fieldName) {
            $stageIds[$stage] = $params['PROPERTY_VALUES'][$fieldName];
        }

        if ($stageIds && $parseData = $this->parseData($stageIds)) {
            $params = array_merge($params, $parseData);
        }

        return $this->element->Add($params);
    }

    protected function addTranslation(array $item)
    {
        $xml_id = md5($item['translation']['title'].$item['translation']['type']);

        if ($id = $this->getByXmlId($this->iblockTranslation, $xml_id)) return $id;

        $params = [
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $this->getIBlockTranslation(),
            'PROPERTY_VALUES'=> [
                'KODIK_ID' => $item['translation']['id'],
                'TYPE' => $item['translation']['type'],
            ],
            'NAME' => $item['translation']['title'],
            'ACTIVE' => 'Y',
            'XML_ID' => $xml_id,
        ];

        return $this->element->Add($params);
    }

    protected function addData(array $item, int $videoId, int $translationId)
    {
        $xml_id = md5($item['title'] . $item['translation']['title']);

        $params = [
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $this->getIBlockData(),
            'PROPERTY_VALUES'=> [
                'VIDEO_CONTENT' => $videoId,
                'TRANSLATION' => $translationId,
                'LAST_SEASON' => $item['last_season'],
                'LAST_EPISODE' => $item['last_episode'],
                'EPISODES_COUNT' => $item['episodes_count'],
                'QUALITY' => $item['quality'],
                'CREATED_AT' => $item['created_at'],
                'UPDATED_AT' => $item['updated_at'],
                'LINK' => $item['link'],
                'SCREENSHOTS' => $item['screenshots'],
            ],
            'NAME' => $item['title'] . ' (' . $item['translation']['title'] . ')',
            'ACTIVE' => 'Y',
            'XML_ID' => $xml_id,
        ];

        if ($id = $this->getByXmlId($this->iblockData, $xml_id)) {
            $this->element->Update($id, $params);
        } else {
            $this->element->Add($params);
        }
    }

    protected function parseData(array $stageIds): array
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
                    $parser = new ParserShikimori($id);
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

        if (!empty($fields)) {
            $params['ACTIVE'] = 'N';
        }

        return $params;
    }

    public function loadImages(array $ids)
    {
        $properties = [
            'filter' => [
                'IBLOCK_CODE' => 'video_content',
                'ID' => $ids,
            ],
            'fields' => ['ID'],
            'properties' => [
                'SHIKIMORI_ID',
                'WORLDART_ID',
            ],
            'select' => [],
        ];
        $properties['select'] = $properties['fields'];
        foreach ($properties['properties'] as $property) {
            $properties['select'][] = 'PROPERTY_'.$property;
        }

        $dbResult = CIBlockElement::GetList([],$properties['filter'],false,false,$properties['select']);

        $itemList = [];
        while ($item = $dbResult->GetNext()) {
            foreach ($properties['fields'] as $field) {
                $itemList[$item['ID']][$field] = $item[$field];
            }
            foreach ($properties['properties'] as $property) {
                $itemList[$item['ID']][$property] = $item['PROPERTY_'.$property.'_VALUE'];
            }
        }

        foreach ($itemList as $id => $item) {

            $stageIds = [];
            foreach (self::$stages as $stage => $fieldName) {
                $stageIds[$stage] = $item[$fieldName];
            }

            if ($stageIds && $params = $this->parseData($stageIds)) {
                $this->element->Update($id, $params);
            }
        }
    }
}