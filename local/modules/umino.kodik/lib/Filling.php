<?php


namespace Umino\Kodik;


use CIBlockElement;
use COption;

Class Filling
{
    protected $videoList = [];
    protected $translationList = [];
    protected $dataList = [];

    protected $element;

    public function __construct()
    {
        $this->videoList = $this->getXMLIDList($this->getIBlockVideo());
        $this->translationList = $this->getXMLIDList($this->getIBlockTranslation());
        $this->dataList = $this->getXMLIDList($this->getIBlockData());

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

    protected function getXMLIDList(int $iBlockId): array
    {
        $result = [];
        $itemList = \Bitrix\Iblock\ElementTable::getList([
            'filter' => [
                'IBLOCK_ID' => $iBlockId,
            ],
            'select' => ['ID', 'XML_ID'],
        ])->fetchAll();

        foreach ($itemList as $item) {
            $result[$item['XML_ID']] = $item['ID'];
        }

        return $result;
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
        $xml_id = md5($item['title']);

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
                'SCREENSHOTS' => $item['screenshots']
            ],
            'NAME' => $item['title'],
            'ACTIVE' => 'Y',
            'XML_ID' => $xml_id,
        ];

        if ($id = $this->videoList[$xml_id]) {
            $this->element->Update($id, $params);
        } else {
            $id = $this->element->Add($params);
            $this->videoList[$xml_id] = $id;
        }

        return $id;
    }

    protected function addTranslation(array $item)
    {
        $xml_id = md5($item['translation']['title'].$item['translation']['type']);

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

        if ($id = $this->translationList[$xml_id]) {
            $this->element->Update($id, $params);
        } else {
            $id = $this->element->Add($params);
            $this->translationList[$xml_id] = $id;
        }

        return $id;
    }

    protected function addData(array $item, int $videoId, int $translationId)
    {

        $xml_id = md5($videoId . $translationId . $item['last_season']);

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
            ],
            'NAME' => $item['title'] . ' (' . $item['translation']['title'] . ')',
            'ACTIVE' => 'Y',
            'XML_ID' => $xml_id,
        ];

        if ($id = $this->dataList[$xml_id]) {
            $this->element->Update($id, $params);
        } else {
            $id = $this->element->Add($params);
            $this->dataList[$xml_id] = $id;
        }
    }
}