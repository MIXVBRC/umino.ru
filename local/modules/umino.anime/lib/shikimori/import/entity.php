<?php


namespace Umino\Anime\Shikimori\Import;


use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\Type\DateTime;
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
    protected API\Entity $api;
    protected static CIBlockElement $element;
    protected static array $properties = [];

    public function __construct(string $id, string $parent_sid = null)
    {
        $this->id = $id;
        $this->api = $this->getAPI($parent_sid);
        if (empty(self::$element)) {
            self::$element = new CIBlockElement();
        }
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function rebaseFields(array $fields): array
    {
        $fields['IBLOCK_ID'] = Manager::getIBID(static::getName());
        $fields['XML_ID'] = self::getXmlId($this->getId());

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
        if (self::checkIB($fields['XML_ID'])) return true;

        self::prepareFields($fields);
        $result = self::$element->Add($fields);
        if (empty($result)) {
            Logger::log([
                'error' => self::$element->LAST_ERROR,
                'fields' => $fields,
            ]);
            return false;
        }

        return true;
    }

    public function checkIB(string $xmlId)
    {
        $entity = ElementTable::getEntity();
        $query = new Query($entity);
        $query
            ->setLimit(1)
            ->setFilter([
                'IBLOCK_ID' => Manager::getIBID(static::getName()),
                'XML_ID' => $xmlId,
            ])
            ->setSelect(['ID'])
        ;

        return (bool) $query->fetch()['ID'];
    }

    protected static function prepareFields(array &$fields): bool
    {
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

    protected function getAPI(string $parent_sid = null): API\Entity
    {
        $class = Manager::getAPIClass(static::getName());
        if ($parent_sid) {
            return new $class($this->getId(), $parent_sid);
        } else {
            return new $class($this->getId());
        }
    }

    protected function loadIB()
    {

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
        $result = static::rearrange($params);
        $result[] = Manager::getIBCode(static::getName());
        $result = serialize($result);
        return md5($result);
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
                $result[] = $item;
            }
        }

        return $result;
    }
}