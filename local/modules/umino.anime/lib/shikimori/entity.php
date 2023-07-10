<?php


namespace Umino\Anime\Shikimori;


use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
use CFile;
use CIBlockElement;
use CUtil;

class Entity
{
    public static array $data = [];
    protected static array $loads = [];
    protected static array $iblockIds = [];
    protected static array $properties = [];

    protected static CIBlockElement $element;

    protected static bool $md5Id = false;

    protected string $id;
    protected string $xmlId;
    protected string $sId;
    protected array $fields = [];

    protected static array $cache = [];

    public function __construct(string $id, string $xmlId, array $fields)
    {
        $this->id = $id;
        $this->xmlId = $xmlId;
        $this->setFields($fields);
        $this->addData();
    }

    protected static function rebase(array $fields): array
    {
        return $fields;
    }

    public static function create(string $id): ?object
    {
        $id = static::buildId($id);
        $xmlId = static::buildXmlId($id, static::getClass());

        if ($item = static::getById($xmlId)) {

            return $item;

        } else if ($items = static::loadFromDataBase([$xmlId])) {

            return end($items);

        } else {

            static::addLoad([$id]);
            $results = static::load();

            if (empty($fields = $results[$id])) return null;

            $fields = static::rebase($fields);

            $fields['XML_ID'] = $xmlId;
            $fields['CODE'] = static::buildCode($id, $fields['NAME']);

            static::saveToDataBase($fields);

            $items = static::loadFromDataBase([$xmlId]);

            return $items[$xmlId];
        }
    }

    protected static function saveToDataBase(array $fields): bool
    {
        static::prepareFields($fields);

        if (static::add($fields)) {
            return true;
        } else {
            var_dump(static::$element->LAST_ERROR);
            return false;
        }
    }

    protected function getId(): string
    {
        return $this->id;
    }

    protected function getSId(): string
    {
        return $this->sId;
    }

    protected function getXmlId(): string
    {
        return $this->xmlId;
    }

    protected static function addLoad(array $ids)
    {
        foreach ($ids as $id) {
            static::$loads[$id] = static::getUrl([$id]);
        }
    }

    protected static function load(): array
    {
        $result = static::loadFromRequest();
        static::$loads = [];
        return $result;
    }

    protected static function getIblockId(): int
    {
        if ($iblockId = static::$iblockIds[static::getName()]) return $iblockId;

        $entity = IblockTable::getEntity();
        $query = new Query($entity);
        $query
            ->setFilter([
                'CODE' => static::getName()
            ])
            ->setSelect([
                'ID',
            ])
        ;

        $iblockId = $query->fetch()['ID'];

        return static::$iblockIds[static::getName()] = $iblockId;
    }

    protected static function loadFromDataBase(array $xmlIds): array
    {
        $entity = ElementTable::getEntity();
        $query = new Query($entity);
        $query
            ->setFilter([
                'IBLOCK_ID' => static::getIblockId(),
                'XML_ID' => $xmlIds
            ])
            ->setSelect([
                'ID',
                'XML_ID',
                'CODE',
                'IBLOCK_ID',
                'NAME',
                'DETAIL_PICTURE',
                'DETAIL_TEXT',
                'PROPERTY_CODE' => 'PROPERTY.CODE',
                'PROPERTY_MULTIPLE' => 'PROPERTY.MULTIPLE',
                'PROPERTY_TYPE' => 'PROPERTY.PROPERTY_TYPE',
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
            ])
        ;

        $items = [];
        $properties = [];
        foreach ($query->fetchAll() as $item) {
            if ($item['PROPERTY_CODE']) {
                if (in_array($item['PROPERTY_TYPE'], ['F'])) {
                    $item['PROPERTY_VALUE'] = CFile::GetPath($item['PROPERTY_VALUE']);
                }
                if ($item['PROPERTY_MULTIPLE'] === 'Y') {
                    $properties[$item['XML_ID']][$item['PROPERTY_CODE']][] = $item['PROPERTY_VALUE'];
                } else {
                    $properties[$item['XML_ID']][$item['PROPERTY_CODE']] = $item['PROPERTY_VALUE'];
                }
            }

            unset($item['PROPERTY_CODE'], $item['PROPERTY_VALUE']);

            if ($items[$item['XML_ID']]) continue;

            if ($item['DETAIL_PICTURE']) {
                $item['DETAIL_PICTURE'] = CFile::GetPath($item['DETAIL_PICTURE']);
            }
            $items[$item['XML_ID']] = $item;
        }

        if (empty($items)) return $items;

        foreach ($items as &$item) {
            $item['PROPERTY_VALUES'] = $properties[$item['XML_ID']];

            $item = new static($item['ID'], $item['XML_ID'], $item);
        } unset($item);

        return $items;
    }

    protected static function getProperties(): array
    {
        if ($properties = static::$properties[static::getName()]) return $properties;

        $entity = PropertyTable::getEntity();
        $query = new Query($entity);
        $query
            ->setFilter([
                'IBLOCK_ID' => static::getIblockId(),
            ])
            ->setSelect(['*'])
        ;

        foreach ($query->fetchAll() as $property) {
            $properties[$property['CODE']] = $property;
        }

        return static::$properties[static::getName()] = $properties;
    }

    protected static function prepareFields(array &$fields): bool
    {
        $fields['IBLOCK_ID'] = static::getIblockId();

        if ($fields['DETAIL_PICTURE']) {
            $fields['DETAIL_PICTURE'] = CFile::MakeFileArray($fields['DETAIL_PICTURE']);
        }

        if (empty($fields['PROPERTY_VALUES'])) return true;

        $properties = static::getProperties();

        foreach ($fields['PROPERTY_VALUES'] as $code => &$value) {
            $property = $properties[$code];

            if ($property['IS_REQUIRED'] === 'Y' && is_null($value)) return false;

            if ($property['MULTIPLE'] === 'Y' && !is_array($value)) {
                $value = [$value];
            }

            if (in_array($property['USER_TYPE'], ['Date', 'DateTime'])) {
                if (is_array($value)) {
                    foreach ($value as &$item) {
                        $item = DateTime::createFromTimestamp(strtotime($item));
                    }
                } else {
                    $value = DateTime::createFromTimestamp(strtotime($value));
                }
            }

            if (in_array($property['PROPERTY_TYPE'], ['F'])) {
                if (is_array($value)) {
                    foreach ($value as &$item) {
                        $item = CFile::MakeFileArray($item);
                    }
                } else {
                    $value = CFile::MakeFileArray($value);
                }
            }
        }

        return true;
    }

    protected static function add(array $fields)
    {
        if (empty(static::$element)) {
            static::$element = new CIBlockElement();
        }
        return static::$element->Add($fields);
    }

    protected static function loadFromRequest(): array
    {
        $result = [];

        foreach (array_keys(static::$loads) as $key) {
            if (empty($cache = static::getCache($key))) continue;
            $result[$key] = $cache;
            unset(static::$loads[$key]);
        }

        $request = new Request();
        $request->addToAsyncQueue(self::$loads);
        $request->initAsyncRequest();

        foreach ($request->getResult() as $key => $resultRequest) {
            $result[$key] = $resultRequest;
        }

        static::$loads = [];
        return $result;
    }

    protected static function addCache(string $key, string $value)
    {
        static::$cache[static::getClass()][$key] = $value;
    }

    protected static function getCache(string $key)
    {
        return static::$cache[static::getClass()][$key];
    }

    protected static function getUrl(array $additional = []): string
    {
        return Request::buildApiURL(array_merge([static::getName()], $additional));
    }

    protected static function getClass(): string
    {
        return get_called_class();
    }

    protected static function getName(): string
    {
        $explode = explode('\\', static::getClass());
        return strtolower(end($explode));
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    protected function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    protected function addData()
    {
        static::$data[static::getClass()][$this->xmlId] = $this;
    }

    protected static function rearrange(array $array): array
    {
        $result = [];

        foreach ($array as $item) {
            if (is_array($item)) {
                $result[] += static::rearrange($item);
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    protected static function buildId(...$params): string
    {
        $result = static::rearrange($params);

        $result = implode('', $result);

        if (static::$md5Id) return md5($result);

        return $result;
    }

    protected static function buildXmlId(...$params): string
    {
        $result = static::rearrange($params);

        $result = implode('', $result);

        return md5($result);
    }

    protected static function buildCode(...$params): string
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

    public static function getById(string $xmlId): ?object
    {
        return static::$data[static::getClass()][$xmlId];
    }

    public static function creates(array $ids): array
    {
        $result = [];

        $items = [];

        foreach ($ids as $key => $id) {
            $id = static::buildId($id);
            $xmlId = static::buildXmlId($id, static::getClass());

            if ($item = static::getById($xmlId)) {
                $result[$xmlId] = $item;
                unset($ids[$key]);
            } else {
                $items[$id] = $xmlId;
            }
        }

        if ($loads = static::loadFromDataBase($items)) {
            $result = array_merge($result, $loads);
            foreach (array_keys($loads) as $xmlId) {
                unset($items[array_search($xmlId, $items)]);
            }
        }
        
        static::addLoad($items);
        $results = static::load();

        foreach ($results as $item) {
            $fields = static::rebase($item);

            $fields['XML_ID'] = static::buildXmlId($item['ID'], static::getClass());
            $fields['CODE'] = static::buildCode($item['ID'], $fields['NAME']);

            static::saveToDataBase($fields);
        }

        if ($loads = static::loadFromDataBase($items)) {
            $result = array_merge($result, $loads);
            foreach (array_keys($loads) as $xmlId) {
                unset($items[array_search($xmlId, $items)]);
            }
        }

        return $result;
    }

    public static function getData():array
    {
        return self::$data;
    }
}