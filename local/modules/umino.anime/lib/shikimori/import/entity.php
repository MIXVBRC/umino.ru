<?php


namespace Umino\Anime\Shikimori\Import;


use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use CDatabase;
use CFile;
use CIBlockElement;
use CUtil;
use Umino\Anime\Core;
use Umino\Anime\Logger;
use Umino\Anime\Shikimori\Manager;
use Umino\Anime\Shikimori\API;

class Entity
{
    protected string $id;
    protected string $parent_id;
    protected API\Entity $api;
    protected static CIBlockElement $element;
    protected static array $properties = [];

    public function __construct(string $id, string $parent_id = '')
    {
        $this->id = trim($id);
        $this->parent_id = trim($parent_id);
        $this->api = $this->getAPI($this->parent_id);
        if (empty(self::$element)) {
            self::$element = new CIBlockElement();
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getParentId(): string
    {
        return $this->parent_id;
    }

    public function rebaseFields(array $fields): array
    {
        $fields['IBLOCK_ID'] = Manager::getIBID(static::getName());
        $fields['XML_ID'] = self::getXmlId($this->getId(), Manager::getIBCode(static::getName()));

        return $fields;
    }

    public function load(): bool
    {
        $fields = $this->api->get();

        if (empty($fields)) return false;

        Core::keysToUpperCase($fields);

        $fields = $this->rebaseFields($fields);

        return self::add($fields);
    }

    public function add(array $fields): bool
    {
        $result = true;

        if ($element = self::getIBElement($fields['XML_ID'])) {

            $fields = self::spliceFields($fields, $element);

            if ($fields['PROPERTY_VALUES']) {
                self::$element::SetPropertyValuesEx($element['ID'], $element['IBLOCK_ID'], $fields['PROPERTY_VALUES']);
            }

            if ($fields) {
                unset($fields['PROPERTY_VALUES']);
                $result = self::$element->Update($element['ID'], $fields);
            }

        } else {

            self::prepareFields($fields);
            
            $result = self::$element->Add($fields);

        }

        if (empty($result)) {

            Logger::log([
                'error' => self::$element->LAST_ERROR,
                'fields' => $fields,
            ]);

        }

        return $result;
    }

    protected static function spliceFields(array $fields_new, array $fields_old): array
    {
        $fields_result = [];
        foreach ($fields_old as $code => $field_old_value) {
            $field_new_value = $fields_new[$code];

            if ($code == 'PROPERTY_VALUES') {
                $properties_result = self::spliceProperties(
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

    protected static function spliceProperties(array $properties_new, array $properties_old): array
    {
        $properties_result = [];

        foreach ($properties_old as $code => $property_old) {
            $property_new_value = $properties_new[$code];

            if (empty($property_new_value)) {

                continue;

            } else if (empty($property_old['VALUE'])) {

                if (in_array($property_old['USER_TYPE'], ['Date','DateTime'])) {

                    $properties_result[$code] = self::getDate($property_new_value, $property_old['USER_TYPE']);

                } else if (in_array($property_old['TYPE'],['F'])) {

                    if ($property_old['MULTIPLE'] == 'Y') {

                        $files = [];

                        foreach ($property_new_value as $link) {
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

                } else if (in_array($property_old['USER_TYPE'], ['Date','DateTime'])) {

                    $property_new_value = self::getDate($property_new_value, $property_old['USER_TYPE']);
                    $property_old_value = self::getDate($property_old['VALUE'], $property_old['USER_TYPE']);

                    if (self::checkDate($property_new_value->toString(), $property_old_value->toString()) > 0) {
                        $properties_result[$code] = $property_new_value;
                    }

                } else if ($property_old['VALUE'] != $property_new_value) {
                    $properties_result[$code] = $property_new_value;
                }
            }
        }

        return $properties_result;
    }

    protected static function getFileArray($data): array
    {
        $result = [];

        if (empty($data)) return $result;

        if (is_array($data)) {
            foreach ($data as $link) {
                $result[] = self::getFileArray($link);
            }
        } else {
            $result = self::makeFileArray($data);
        }

        return $result;
    }

    public static function getIBElement(string $xmlId): array
    {
        $items = self::getIBElements([$xmlId]);
        return end($items) ?: [];
    }

    public static function getIBElements(array $xmlId): array
    {
        $entity = ElementTable::getEntity();
        $query = new Query($entity);
        $query
            ->setFilter([
                'IBLOCK_ID' => Manager::getIBID(static::getName()),
                'XML_ID' => $xmlId,
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
                'PROPERTY_DESCRIPTION' => 'ELEMENT_PROPERTY.DESCRIPTION',
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
                'DATE_UPDATE' => $item['DATE_UPDATE']->format('d.m.Y s:i:H'),
            ];
            if (empty($item['PROPERTY_CODE'])) continue;

            $value = $properties[$item['XML_ID']][$item['PROPERTY_CODE']]['VALUE']?:[];

            if ($item['PROPERTY_DESCRIPTION']) {
                $item['PROPERTY_VALUE'] = [
                    'VALUE' => $item['PROPERTY_VALUE'],
                    'DESCRIPTION' => $item['PROPERTY_DESCRIPTION'],
                ];
            }

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

        return $items;
    }

    public static function makeFileArray(string $filepath): array
    {
        if (empty($filepath)) return [];

        $info = pathinfo($filepath);

        $fileArray = CFile::MakeFileArray($filepath);

        $extension = explode('/', $fileArray['type'])[1];

        $fileArray['name'] = implode('.', [
            $info['filename'],
            $extension,
        ]);

        if ($info['filename'] == 'missing_original') return [];

        return $fileArray;
    }

    protected function prepareFields(&$fields)
    {
        if ($fields['DETAIL_PICTURE']) {
            $fields['DETAIL_PICTURE'] = self::getFileArray($fields['DETAIL_PICTURE']);
        }
        if ($fields['PREVIEW_PICTURE']) {
            $fields['PREVIEW_PICTURE'] = self::getFileArray($fields['PREVIEW_PICTURE']);
        }

        if (empty($fields['PROPERTY_VALUES'])) return;

        $properties = Manager::getIBProperties(static::getName());

        if (empty($properties)) return;

        foreach ($fields['PROPERTY_VALUES'] as $code => &$value) {

            if (empty($value)) continue;

            $property = $properties[$code];

            if (in_array($property['USER_TYPE'], ['Date','DateTime'])) {

                $value = self::getDate($value, $property['USER_TYPE']);

            } else if ($property['PROPERTY_TYPE'] === 'F') {

                if ($property['MULTIPLE'] === 'Y') {
                    $files = [];
                    foreach ($value as $link) {
                        if (!$fileArray = self::getFileArray($link)) continue;
                        $files[] = [
                            'VALUE' => $fileArray,
                            'DESCRIPTION' => '',
                        ];
                    }
                    $value = $files;
                } else {
                    $value = self::getFileArray($value);
                }
            } else if (is_array($value)) {
                $values = [];
                foreach ($value as $item) {
                    if (in_array($item, $values)) continue;
                    $values[] = $item;
                }
                $value = $values;
            }
        }
    }

    protected static function getProperties(): array
    {
        if ($properties = static::$properties[static::getName()]) return $properties;

        $entity = PropertyTable::getEntity();
        $query = new Query($entity);
        $query
            ->setFilter([
                'IBLOCK_ID' => Manager::getIBID(static::getName()),
            ])
            ->setSelect(['*'])
        ;

        foreach ($query->fetchAll() as $property) {
            $properties[$property['CODE']] = $property;
        }

        return static::$properties[static::getName()] = $properties;
    }

    protected function getAPI(string $parent_id = ''): API\Entity
    {
        $class = Manager::getAPIClass(static::getName());
        if ($parent_id) {
            return new $class($this->getId(), $parent_id);
        } else {
            return new $class($this->getId());
        }
    }

    protected static function getClass(): string
    {
        return get_called_class();
    }

    public static function getName(): string
    {
        $explode = explode('\\', static::getClass());
        return end($explode);
    }

    protected static function getXmlId(...$params): string
    {
        return md5(serialize(static::rearrange($params)));
    }

    protected static function getCode(...$params): string
    {
        $result = static::rearrange($params);

        $result = implode('-', $result);

        return Cutil::translit(
            $result,
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

    protected static function rearrange(array $array): array
    {
        $result = [];

        foreach ($array as $item) {
            if (is_array($item)) {
                $result[] += static::rearrange($item);
            } else {
                $result[] = trim($item);
            }
        }

        return $result;
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
    protected static function checkDate(string $data_A, string $data_B): int
    {
        return (new CDatabase)->CompareDates(
            $data_A,
            $data_B
        );
    }

    protected static function getDate(string $date, string $type = 'DateTime')
    {
        $result = '';

        if (empty($date)) return $result;

        if ($type == 'DateTime') {
            $result = DateTime::createFromTimestamp(strtotime($date));
        } else {
            $result = Date::createFromTimestamp(strtotime($date));
        }

        return $result;
    }
}