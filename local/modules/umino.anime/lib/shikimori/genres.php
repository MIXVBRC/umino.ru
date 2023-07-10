<?php


namespace Umino\Anime\Shikimori;


class Genres extends Entity
{
    protected static function rebase(array $fields): array
    {
        return [
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'PROPERTY_VALUES' => [
                'NAME_ORIGIN' => $fields['NAME'],
            ],
        ];
    }

    protected static function getUrl(array $additional = []): string
    {
        return Request::buildApiURL([static::getName()]);
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

        if (!empty($items)) {

            static::addLoad([1]);
            $results = static::load()[1];

            foreach ($results as $item) {

                if (!in_array($item['ID'], array_keys($items))) continue;

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
        }

        return $result;
    }
}