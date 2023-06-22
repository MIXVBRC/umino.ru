<?php


namespace Umino\Anime;

use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use CDatabase;
use CIBlockElement;
use CModule;
use CUtil;
use Umino\Anime\Tables\EpisodesTable;

class Import
{
    protected CIBlockElement $element;
    protected CDatabase $cDatabase;

    protected int $userId;

    protected array $iblocks = [];
    protected array $collection = [];
    protected array $properties;

    protected array $elements = [];

    protected int $memory = 0;
    protected array $memories = [];

    protected static array $imageTypes = ['png', 'jpeg', 'jpg'];

    protected static array $lang = [
        'STUDIOS' => 'Студии',
        'PEOPLE' => 'Люди',
        'GENRES' => 'Жанры',
        'TRANSLATIONS' => 'Студии переводов',
        'ANIME' => 'Сериалы',
        'EPISODES' => 'Эпизоды',

        'ACTORS' => 'Актер',
        'DIRECTORS' => 'Режиссер',
        'PRODUCERS' => 'Продюсер',
        'WRITERS' => 'Писатель',
        'COMPOSERS' => 'Композитор',
        'EDITORS' => 'Редактор',
        'DESIGNERS' => 'Дизайнер',
        'OPERATORS' => 'Оператор',

        'Рекап' => 'Рекапы',
        'Рекапы' => 'Рекапы',

        'Спешл' => 'Спецвыпуски',
        'Спэшл' => 'Спецвыпуски',
        'Спешлы' => 'Спецвыпуски',
        'Спецвыпуск' => 'Спецвыпуски',
        'Спецвыпуски' => 'Спецвыпуски',
        'Спешлы без перевода' => 'Спецвыпуски без перевода',
        'SP - Gomer' => 'Спецвыпуски',

        '0 серия' => '0 серия',
        'Эпизод 0' => '0 серия',

        'OVA' => 'OVA',
        'ONA' => 'ONA',

        'Коллаж' => 'Коллаж',

        'Фильм' => 'Фильм',
        'Мини-эпизоды' => 'Мини-эпизоды',
        'Манга-эпизоды' => 'Манга-эпизоды',
        'Сезон 2' => '',

        'эп-коллаж' => 'Эп-коллаж',
        'Эп-коллаж' => 'Эп-коллаж',

        '1 сезон, Режиссёрская версия' => 'Режиссёрская версия',
        'Дополнительный материал' => 'Дополнительные материалы',
    ];

    public function __construct()
    {
        self::showMemoryUsage($this);

        $errors = [];
        if (!CModule::IncludeModule('iblock')) {
            $errors[] = 'Модуль инфоблоков не подключен';
        } else if (!$this->iblocks['STUDIOS']  = Core::getStudiosIBlockID()) {
            $errors[] = 'Не выбран инфоблок для студий';
        } else if (!$this->iblocks['PEOPLE']  = Core::getPeopleIBlockID()) {
            $errors[] = 'Не выбран инфоблок для персон';
        } else if (!$this->iblocks['GENRES']  = Core::getGenresIBlockID()) {
            $errors[] = 'Не выбран инфоблок для жанров';
        } else if (!$this->iblocks['TRANSLATIONS']  = Core::getTranslationsIBlockID()) {
            $errors[] = 'Не выбран инфоблок для студий переводов';
        } else if (!$this->iblocks['ANIME'] = Core::getAnimeIBlockID()) {
            $errors[] = 'Не выбран инфоблок для сериалов';
        } else if (!$this->iblocks['EPISODES'] = EpisodesTable::class) {
            $errors[] = 'Не найдена таблица для сериалов';
        }

        if ($errors) {
            throw new \Exception(implode('; ', $errors));
        }

        foreach ($this->iblocks as $type => $id) {
            if (in_array($type, ['EPISODES'])) continue;
            $properties = PropertyTable::getList([
                'filter' => [
                    'IBLOCK_ID' => $id
                ]
            ])->fetchAll();
            foreach ($properties as $property) {
                $this->properties[$id][$property['CODE']] = $property;
            }
        }

        $this->element = new CIBlockElement;
        $this->cDatabase = new CDatabase;

        $this->userId = 2;
    }

    public function __destruct()
    {
        Core::addEvent('OnImportFinish', [
            'memory_usage' => $this->memories,
        ]);
    }

    public function start(array $data): bool
    {
        self::showMemoryUsage($this);

        Core::addEvent('OnImportStart');

        if (empty($data)) return false;

        foreach ($data as $item) {
            if (empty($item) || empty($item['SHIKIMORI_ID']) || empty($item['MATERIAL_DATA']['TITLE_EN']) || empty($item['MATERIAL_DATA'])) continue;
            $serialXmlId = $this->collectAnime($item);
            $translationXmlId = $this->collectTranslations($item['TRANSLATION']);
            if (!$serialXmlId || !$translationXmlId) continue;
            $this->collectEpisodes($item, $serialXmlId, $translationXmlId);
        }


        self::showMemoryUsage($this);

        $this->elements = $this->getItems();

        self::showMemoryUsage($this);

        foreach ($this->collection as $type => $fields) {
            if (in_array($type, ['EPISODES'])) {

                foreach ($fields as $key => $item) {

                    if (empty($element = $this->elements[$type][$item['XML_ID']])) continue;

                    if ($item['EPISODES'] && count($element['EPISODES']) < count($item['EPISODES'])) {

                        Core::addEvent('OnBeforeImportUpdate', [
                            'type' => $type,
                            'fields' => &$item,
                        ]);

                        $result = EpisodesTable::update($element['ID'], [
                            'EPISODES' => $item['EPISODES'],
                        ]);

                        if (!$result->isSuccess()) {
                            Logger::log([
                                'message' => $result->getErrorMessages(),
                                'fields' => $item,
                            ]);
                        } else {

                            Core::addEvent('OnAfterImportUpdate', [
                                'type' => $type,
                                'fields' => $item,
                            ]);

                            Core::addEvent('OnImportEpisodesAdd', [
                                'episodes' => array_diff($element['EPISODES'],$item['EPISODES']),
                            ]);
                        }
                    }

                    unset($fields[$key]);
                }

                if (empty($fields)) continue;

                $this->addEpisodes($fields);

            } else {

                $this->addElements($type, $fields);

            }
        }

        self::showMemoryUsage($this);

        return true;
    }

    protected function processFields(&$fields)
    {
        if (!empty($fields['DETAIL_PICTURE']) && self::checkImage($fields['DETAIL_PICTURE'])) {
            $fields['DETAIL_PICTURE'] = self::getFileArray($fields['DETAIL_PICTURE']);
        }
        if (!empty($fields['PREVIEW_PICTURE']) && self::checkImage($fields['PREVIEW_PICTURE'])) {
            $fields['PREVIEW_PICTURE'] = self::getFileArray($fields['PREVIEW_PICTURE']);
        }

        foreach ($fields['PROPERTY_VALUES'] as $code => &$value) {

            if (empty($value)) continue;

            $property = $this->properties[$fields['IBLOCK_ID']][$code];

            if (in_array($property['USER_TYPE'], ['Date','DateTime'])) {

                $value = self::getDate($value);

            } else if ($property['PROPERTY_TYPE'] === 'F') {

                if ($property['MULTIPLE'] === 'Y') {
                    $files = [];
                    foreach ($value as $key => $link) {
                        if (!self::checkImage($link)) continue;
                        $files[] = [
                            'VALUE' => self::getFileArray($link),
                            'DESCRIPTION' => '',
                        ];
                    }
                    $value = $files;
                } else {
                    $value = self::checkImage($value) ? self::getFileArray($value) : '';
                }
            } else if (is_array($value)) {
                $value = array_values(array_unique($value));
            }
        }
    }

    protected function spliceProperties(array $properties_new, array $properties_old): array
    {
        $properties_result = [];

        foreach ($properties_old as $code => $property_old) {
            $property_new_value = $properties_new[$code];

            if (empty($property_new_value)) {

                continue;

            } else if (empty($property_old['VALUE'])) {

                if (in_array($property_old['USER_TYPE'],['DateTime','Time'])) {

                    $properties_result[$code] = self::getDate($property_new_value);

                } else if (in_array($property_old['TYPE'],['F'])) {

                    if ($property_old['MULTIPLE'] == 'Y') {

                        $files = [];

                        foreach ($property_new_value as $link) {
                            if (!self::checkImage($link)) continue;
                            $files[] = [
                                'VALUE' => self::getFileArray($link),
                                'DESCRIPTION' => '',
                            ];
                        }

                        $properties_result[$code] = $files;

                    } else {
                        $properties_result[$code] = self::getFileArray($property_new_value);
                    }

                } else {
                    $properties_result[$code] = $property_new_value;
                }

            } else if (!in_array($property_old['TYPE'],['F'])) {

                if ($property_old['MULTIPLE'] == 'Y') {

                    if ($diff = array_diff($property_new_value, $property_old['VALUE'])) {
                        $properties_result[$code] = array_merge($property_old['VALUE'], $diff);
                    }

                } else if (in_array($property_old['USER_TYPE'],['DateTime','Time'])) {

                    $property_new_value = self::getDate($property_new_value);
                    $property_old_value = self::getDate($property_old['VALUE']);

                    if ($this->checkDate($property_new_value->toString(), $property_old_value->toString()) > 0) {
                        $properties_result[$code] = $property_new_value;
                    }

                } else if ($property_old['VALUE'] != $property_new_value) {
                    $properties_result[$code] = $property_new_value;
                }
            }
        }

        return $properties_result;
    }

    protected function spliceFields(array $fields_new, array $fields_old): array
    {
        $fields_result = [];
        foreach ($fields_old as $code => $field_old_value) {
            $field_new_value = $fields_new[$code];

            if ($code == 'PROPERTY_VALUES') {
                $properties_result = $this->spliceProperties(
                    $field_new_value,
                    $field_old_value
                );
                if (!empty($properties_result)) {
                    $fields_result[$code] = $properties_result;
                }
                continue;
            }

            if (empty($field_new_value)) {
                continue;
            } else if (empty($field_old_value)) {
                if (in_array($code, ['PREVIEW_PICTURE' ,'DETAIL_PICTURE'])) {
                    $fields_result[$code] = self::getFileArray($field_new_value);
                } else {
                    $fields_result[$code] = $field_new_value;
                }
            } else if ($field_new_value != $field_old_value && !in_array($code, ['PREVIEW_PICTURE' ,'DETAIL_PICTURE'])) {
                $fields_result[$code] = $field_new_value;
            }
        }

        return $fields_result;
    }

    protected function addElements(string $type, array $items)
    {
        foreach ($items as $xmlId => $item) {
            if ($element = $this->elements[$type][$xmlId]) {

                if (!in_array($type, ['ANIME', 'PEOPLE'])) continue;

                if (in_array($type, ['ANIME']) && $element['MODIFIED_BY'] == $this->userId) {
                    $date_update_new = self::getDate($item['DATE_UPDATE'])->toString();
                    $date_update_old = self::getDate($element['DATE_UPDATE'])->toString();

                    if ($this->checkDate($date_update_new, $date_update_old) < 1) {
                        continue;
                    }
                }

                unset($item['DATE_UPDATE']);

                $item_result = $this->spliceFields($item, $element);

                if (empty($item_result)) continue;

                Core::addEvent('OnBeforeImportUpdate', [
                    'type' => $type,
                    'fields' => &$item_result,
                ]);

                $properties = $item_result['PROPERTY_VALUES'];
                unset($item_result['PROPERTY_VALUES']);

                if (!empty($properties)) {
                    $this->element::SetPropertyValuesEx($element['ID'], $element['IBLOCK_ID'], $properties);
                }

                if (!$this->element->Update($element['ID'], $item_result)) {
                    Logger::log([
                        'message' => $this->element->LAST_ERROR,
                        'fields' => $item_result,
                    ]);
                } else {
                    if (!empty($properties)) {
                        $item_result['PROPERTY_VALUES'] = $properties;
                    }
                    Core::addEvent('OnAfterImportUpdate', [
                        'type' => $type,
                        'fields' => $item_result
                    ]);
                }

            } else {

                $item['IBLOCK_ID'] = $this->iblocks[$type];

                $this->processFields($item);

                Core::addEvent('OnBeforeImportAdd', [
                    'type' => $type,
                    'fields' => &$item
                ]);

                if (empty($item['ID'] = $this->element->Add($item))) {
                    Logger::log([
                        'message' => $this->element->LAST_ERROR,
                        'fields' => $item
                    ]);
                } else {
                    Core::addEvent('OnAfterImportAdd', [
                        'type' => $type,
                        'fields' => $item
                    ]);
                }
            }
        }
    }

    protected function addEpisodes(array $items)
    {
        foreach ($items as $item) {

            Core::addEvent('OnBeforeImportAdd', [
                'type' => 'EPISODES',
                'items' => &$item,
            ]);

            $result = EpisodesTable::add($item);

            if (!$result->isSuccess()) {
                Logger::log([
                    'message' => $result->getErrorMessages(),
                    'fields' => $item
                ]);
            } else {
                Core::addEvent('OnAfterImportAdd', [
                    'type' => 'EPISODES',
                    'fields' => $item
                ]);
            }
        }
    }

    protected function collectStudios(array $studios): array
    {
        $result = [];

        foreach ($studios as $name) {

            $fields = [
                'XML_ID' => self::getXmlId($name),
                'CODE' => self::getCode($name),
                'NAME' => $name,
                'MODIFIED_BY' => $this->userId,
            ];

            $this->addToCollection(__FUNCTION__, $fields);

            $result[] = $fields['XML_ID'];
        }

        return $result;
    }

    protected function collectPeople(array $materialData, string $key): array
    {
        $result = [];

        foreach ($materialData[$key] as $person) {

            $fields = [
                'XML_ID' => self::getXmlId($person),
                'CODE' => self::getCode($person),
                'NAME' => $person,
                'MODIFIED_BY' => $this->userId,
            ];

            $properties = [
                'ROLES' => [self::$lang[$key]],
                'NAME_RU' => '',
                'NAME_EN' => '',
            ];

            if (preg_match('/[a-zA-Z]/', $person)) {
                $properties['NAME_RU'] = '';
                $properties['NAME_EN'] = $person;
            } else {
                $properties['NAME_RU'] = $person;
                $properties['NAME_EN'] = ucwords(Cutil::translit($person, 'ru', ['replace_space' => ' ','replace_other' => ' ']));
            }

            $fields['PROPERTY_VALUES'] = array_diff($properties, ['']);

            $this->addToCollection(__FUNCTION__, $fields);

            $result[] = $fields['XML_ID'];
        }

        return $result;

    }

    protected function collectGenres(array $materialData, string $key): array
    {
        $result = [];

        foreach ($materialData[$key] as $genre) {

            $genre = mb_strtolower($genre);

            $fields = [
                'XML_ID' => md5($genre),
                'CODE' => self::getCode($genre),
                'NAME' => $genre,
                'MODIFIED_BY' => $this->userId,
            ];

            $this->addToCollection(__FUNCTION__, $fields);

            $result[] = $fields['XML_ID'];
        }

        return $result;
    }

    protected function collectTranslations(array $translation): string
    {
        $fields = [
            'XML_ID' => self::getXmlId($translation['TITLE'] . $translation['TYPE']),
            'CODE' => self::getCode($translation['TITLE']),
            'NAME' => $translation['TITLE'],
            'MODIFIED_BY' => $this->userId,
        ];

        $properties = [
            'KODIK_ID' => $translation['ID'],
            'TYPE' => $translation['TYPE'],
        ];

        $fields['PROPERTY_VALUES'] = array_diff($properties, ['']);

        $this->addToCollection(__FUNCTION__, $fields);

        return $fields['XML_ID'];
    }

    protected function collectAnime(array $item): string
    {
        $materialData = $item['MATERIAL_DATA'];

        $uniqueness = implode('-', [
            $item['SHIKIMORI_ID'],
            $materialData['TITLE_EN'],
            $item['TYPE'],
        ]);

        $fields = [
            'XML_ID' => self::getXmlId($materialData['TITLE_EN']),
            'CODE' => self::getCode($materialData['TITLE_EN']),
            'NAME' => $item['TITLE'],
            'PREVIEW_TEXT' => $materialData['DESCRIPTION'] ?: $materialData['ANIME_DESCRIPTION'],
            'DETAIL_TEXT' => $materialData['ANIME_DESCRIPTION'] ?: $materialData['DESCRIPTION'],
            'DETAIL_PICTURE' => $materialData['POSTER_URL'],
            'DATE_UPDATE' => $item['UPDATED_AT'],
            'MODIFIED_BY' => $this->userId,
        ];

        $properties = [
            'TITLE' => $materialData['TITLE'],
            'TITLE_ORIG' => $item['TITLE_ORIG'],
            'TITLE_EN' => $materialData['TITLE_EN'],
            'OTHER_TITLES' => $materialData['OTHER_TITLES'],
            'OTHER_TITLES_EN' => $materialData['OTHER_TITLES_EN'],
            'OTHER_TITLES_JP' => $materialData['OTHER_TITLES_JP'],
            'ANIME_KIND' => $materialData['ANIME_KIND'],
            'ALL_STATUS' => $materialData['ALL_STATUS'],
            'ANIME_STATUS' => $materialData['ANIME_STATUS'],
            'YEAR' => $materialData['YEAR'] ?: $item['YEAR'],
            'SCREENSHOTS' => $materialData['SCREENSHOTS'],
            'DURATION' => $materialData['DURATION'],
            'COUNTRIES' => $materialData['COUNTRIES'],
            'ALL_GENRES' => $this->collectGenres($materialData, 'ALL_GENRES'),
            'GENRES' => $this->collectGenres($materialData, 'GENRES'),
            'ANIME_GENRES' => $this->collectGenres($materialData, 'ANIME_GENRES'),
            'ANIME_STUDIOS' => $this->collectStudios($materialData['ANIME_STUDIOS']?:[]),
            'BLOCKED_COUNTRIES' => $item['BLOCKED_COUNTRIES'],
            'KINOPOISK_RATING' => $materialData['KINOPOISK_RATING'],
            'KINOPOISK_VOTES' => $materialData['KINOPOISK_VOTES'],
            'IMDB_RATING' => $materialData['IMDB_RATING'],
            'IMDB_VOTES' => $materialData['IMDB_VOTES'],
            'SHIKIMORI_RATING' => $materialData['SHIKIMORI_RATING'],
            'SHIKIMORI_VOTES' => $materialData['SHIKIMORI_VOTES'],
            'PREMIERE_WORLD' => $materialData['PREMIERE_WORLD'] ?: $materialData['AIRED_AT'],
            'AIRED_AT' => $materialData['AIRED_AT'] ?: $materialData['PREMIERE_WORLD'],
            'NEXT_EPISODE_AT' => $materialData['NEXT_EPISODE_AT'],
            'MINIMAL_AGE' => $materialData['MINIMAL_AGE'],
            'EPISODES_TOTAL' => $materialData['EPISODES_TOTAL'],
            'EPISODES_AIRED' => $materialData['EPISODES_AIRED'],
            'ACTORS' => $this->collectPeople($materialData, 'ACTORS'),
            'DIRECTORS' => $this->collectPeople($materialData, 'DIRECTORS'),
            'WRITERS' => $this->collectPeople($materialData, 'WRITERS'),
            'COMPOSERS' => $this->collectPeople($materialData, 'COMPOSERS'),
            'OPERATORS' => $this->collectPeople($materialData, 'OPERATORS'),
            'KINOPOISK_ID' => $item['KINOPOISK_ID'],
            'IMDB_ID' => $item['IMDB_ID'],
            'SHIKIMORI_ID' => $item['SHIKIMORI_ID'],
            'MDL_ID' => $item['MDL_ID'],
            'TYPE' => $item['TYPE'],
        ];

        $fields['PROPERTY_VALUES'] = array_diff($properties, ['']);

        $this->addToCollection(__FUNCTION__, $fields);

        return $fields['XML_ID'];
    }

    protected function collectEpisodes(array $item, string $serialXmlId, string $translationXmlId): array
    {
        $result = [];

        if (empty($item['SEASONS'])) {

            $titleArray = [
                $item['TRANSLATION']['TITLE'],
                $item['TRANSLATION']['TYPE'],
            ];

            $title = implode(' | ', $titleArray);

            $fields = [
                'XML_ID' => md5($serialXmlId.$title.$translationXmlId),
                'NAME' => implode(' | ', [$item['TITLE'],$title]),
                'SERIAL_XML_ID' => $serialXmlId,
                'TRANSLATION_XML_ID' => $translationXmlId,
                'SEASON' => 0,
                'TYPE' => '',
                'QUALITY' => $item['QUALITY'],
                'ANIME_LINK' => $item['LINK'],
                'EPISODES' => [],
                'EPISODES_COUNT' => 0,
                'KODIK_ID' => $item['ID'],
                'KODIK_TYPE' => $item['TYPE'],
            ];

            $this->addToCollection('EPISODES', $fields);

            $result[] = $fields['XML_ID'];

        } else {
            foreach ($item['SEASONS'] as $seasonNum => $season) {

                $titleArray = [
                    $item['TRANSLATION']['TITLE'],
                    $item['TRANSLATION']['TYPE'],
                ];

                $titleArray[] = 'сезон ' . $seasonNum;

                if ($season['TITLE']) $titleArray[] = $season['TITLE'];

                $title = implode(' | ', $titleArray);

                $fields = [
                    'XML_ID' => md5($serialXmlId.$title.$translationXmlId),
                    'NAME' => implode(' | ', [$item['TITLE'],$title]),
                    'SERIAL_XML_ID' => $serialXmlId,
                    'TRANSLATION_XML_ID' => $translationXmlId,
                    'SEASON' => $seasonNum < 0 ? 0 : $seasonNum,
                    'TYPE' => $season['TITLE'],
                    'QUALITY' => $item['QUALITY'],
                    'ANIME_LINK' => $item['LINK'],
                    'SEASON_LINK' => $season['LINK'],
                    'EPISODES' => $season['EPISODES'],
                    'EPISODES_COUNT' => count($season['EPISODES']),
                    'KODIK_ID' => $item['ID'],
                    'KODIK_TYPE' => $item['TYPE'],
                ];

                $this->addToCollection('EPISODES', $fields);

                $result[] = $fields['XML_ID'];
            }
        }

        return $result;
    }

    protected function getItems(): array
    {
        $result = [];

        foreach ($this->iblocks as $type => $id) {

            // Убираем все что не ANIME, EPISODES и PEOPLE
            if (!in_array($type, ['ANIME', 'EPISODES', 'PEOPLE'])) {
                $entity = ElementTable::getEntity();
                $query = new Query($entity);
                $query
                    ->setFilter([
                        'XML_ID' => array_keys($this->collection[$type]),
                        'IBLOCK_ID' => $id,
                    ])
                    ->setSelect(['XML_ID']);

                foreach ($query->fetchAll() as $item) {
                    unset($this->collection[$type][$item['XML_ID']]);
                }
                if (empty($this->collection[$type])) unset($this->collection[$type]);
                continue;
            }

            if (in_array($type, ['EPISODES'])) {

                $entity = $id::getEntity();
                $query = new Query($entity);
                $query
                    ->setFilter([
                        'XML_ID' => array_keys($this->collection[$type]),
                    ])
                    ->setSelect([
                        'ID',
                        'XML_ID',
                        'EPISODES',
                    ]);

                foreach ($query->fetchAll() as $element) {
                    $result[$type][$element['XML_ID']] = $element;
                }

            } else {

                $entity = ElementTable::getEntity();
                $query = new Query($entity);
                $query
                    ->setFilter([
                        'XML_ID' => array_keys($this->collection[$type]),
                        'IBLOCK_ID' => $id,
                    ])
                    ->setSelect([
                        'ID',
                        'XML_ID',
                        'IBLOCK_ID',
                        'NAME',
                        'PREVIEW_PICTURE',
                        'DETAIL_PICTURE',
                        'PREVIEW_TEXT',
                        'DETAIL_TEXT',
                        'DATE_UPDATE' => 'TIMESTAMP_X',
                        'MODIFIED_BY',

                        'PROPERTY_CODE' => 'PROPERTY.CODE',
                        'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE',
                        'PROPERTY_USER_TYPE' => 'PROPERTY.USER_TYPE',
                        'PROPERTY_MULTIPLE' => 'PROPERTY.MULTIPLE',
                        'PROPERTY_VALUE' => 'ELEMENT_PROPERTY.VALUE',
                    ])
                    ->registerRuntimeField('PROPERTY', [
                        'data_type' => PropertyTable::class,
                        'reference' => Join::on('ref.IBLOCK_ID', 'this.IBLOCK_ID'),
                        'join_type' => 'left',
                    ])
                    ->registerRuntimeField('ELEMENT_PROPERTY', [
                        'data_type' => ElementPropertyTable::class,
                        'reference' => Join::on('ref.IBLOCK_PROPERTY_ID', 'this.PROPERTY.ID')
                            ->whereColumn('ref.IBLOCK_ELEMENT_ID', 'this.ID'),
                        'join_type' => 'left',
                    ]);

                $items = [];
                $properties = [];
                foreach ($query->fetchAll() as $item) {
                    $items[$item['XML_ID']] = [
                        'ID' => $item['ID'],
                        'XML_ID' => $item['XML_ID'],
                        'IBLOCK_ID' => $item['IBLOCK_ID'],
                        'MODIFIED_BY' => $item['MODIFIED_BY'],
                        'NAME' => $item['NAME'],
                        'PREVIEW_PICTURE' => $item['PREVIEW_PICTURE'],
                        'DETAIL_PICTURE' => $item['DETAIL_PICTURE'],
                        'PREVIEW_TEXT' => $item['PREVIEW_TEXT'],
                        'DETAIL_TEXT' => $item['DETAIL_TEXT'],
                        'DATE_UPDATE' => $item['DATE_UPDATE']->toString(),
                    ];
                    if (empty($item['PROPERTY_CODE'])) continue;

                    $value = $properties[$item['XML_ID']][$item['PROPERTY_CODE']]['VALUE']?:[];

                    if ($item['PROPERTY_MULTIPLE'] == 'Y') {
                        $value = array_merge($value, [$item['PROPERTY_VALUE']]);
                    } else {
                        $value = $item['PROPERTY_VALUE'];
                    }

                    $properties[$item['XML_ID']][$item['PROPERTY_CODE']] = [
                        'TYPE' => $item['PROPERTY_TYPE'],
                        'USER_TYPE' => $item['PROPERTY_USER_TYPE'],
                        'MULTIPLE' => $item['PROPERTY_MULTIPLE'],
                        'VALUE' => $value,
                    ];
                }

                if ($properties) {
                    foreach ($items as &$item) {
                        $item['PROPERTY_VALUES'] = $properties[$item['XML_ID']];
                    } unset($item);
                }

                $result[$type] = $items;
            }
        }

        return $result;
    }

    protected function addToCollection(string $type, array $fields)
    {
        $type = strtoupper(str_replace('collect','',$type));
        $xml_id = $fields['XML_ID'];

        if ($collect = $this->collection[$type][$xml_id]) {

            switch ($type) {
                case 'PEOPLE':
                    self::merge(
                        ['ROLES'],
                        $collect['PROPERTY_VALUES'],
                        $fields['PROPERTY_VALUES']
                    );
                    break;
            }

        } else {
            $this->collection[$type][$xml_id] = $fields;
        }
    }

    /**
     * @param array|string $data
     * @return array
     */
    protected static function getFileArray($data): array
    {
        $result = [];

        if (empty($data)) return $result;

        if (is_array($data)) {
            foreach ($data as $link) {
                $result[] = self::getFileArray($link);
            }
        } else {
            $fileArray = \CFile::MakeFileArray($data);
            if (self::checkFileType($fileArray['type'])) {
                $result = $fileArray;
            }
        }

        return $result;
    }

    protected static function checkFileType(string $type = ''): bool
    {
        if (empty($type)) return false;

        $type = explode('/', $type);
        $type = end($type);

        if (in_array($type, self::$imageTypes)) return true;

        return false;
    }

    protected static function getDate($date)
    {
        if (!empty($date)) {
            $date = DateTime::createFromTimestamp(strtotime(str_replace(['Z','T'], ['',' '], $date)));
        }
        return $date ?: '';
    }

    /**
     * Сверяет даты
     *
     * <ul>
     * <li>1 - A новее B старее
     * <li>0 - A и B одинаковые
     * <li>-1 - A старее B новее
     * </ul>
     *
     * @param string $data_A - сверяемая дата
     * @param string $data_B - с чем сверяем
     * @return int
     */
    protected function checkDate(string $data_A, string $data_B): int
    {
        return $this->cDatabase->CompareDates(
            $data_A,
            $data_B
        );
    }

    protected static function merge(array $keys, array &$data, array $array)
    {
        foreach ($keys as $key) {
            $data[$key] = array_unique(array_merge($data[$key], $array[$key]));
        }
    }

    protected static function checkImage(string $url = ''): bool
    {
        if (empty($url)) return false;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if (in_array($code, [404])) return false;

        return true;
    }

    protected static function getXmlId(string $string): string
    {
        return md5(self::getCode($string));
    }

    public static function getCode(string $string): string
    {
        return Cutil::translit(
            $string,
            'ru',
            [
                'max_len' => 255,
                'change_case' => 'L',
                'replace_space' => '-',
                'replace_other' => '-',
                'delete_repeat_replace ' => true,
                'safe_chars' => '',
            ]
        );
    }

    private static function showMemoryUsage(Import $import)
    {
        $trace = debug_backtrace();
        $memory = memory_get_usage();
        if ($import->memory) {
            $import->memories[] = [
                'MEASURING_POINT' => $trace[0]['file'].':'.$trace[0]['line'],
                'METHOD' => $trace[1]['class'].$trace[1]['type'].$trace[1]['function'].'()',
                'MEMORY' => $memory - $import->memory,
            ];
        }
        $import->memory = $memory;
    }
}