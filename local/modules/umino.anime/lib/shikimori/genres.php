<?php


namespace Umino\Anime\Shikimori;


class Genres extends Entity
{
    protected static array $collection = [];

    protected function rebase(array $fields): array
    {
        return [
            'XML_ID' => $this->getXmlId(),
            'CODE' => static::buildCode($this->getId(), $fields['RUSSIAN'] ?: $fields['NAME']),
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'PROPERTY_VALUES' => [
                'NAME_ORIGIN' => $fields['NAME'],
            ],
        ];
    }

    protected static function addLoad(string $id)
    {
        static::$loads[$id] = static::getUrl();
    }

    protected static function load(): array
    {
        $result = [];

        $loadKeys = array_keys(static::$loads);
        $collectionKeys = array_keys(static::getCollection());

        if (array_diff($loadKeys, $collectionKeys)) {

            $request = new Request();
            $request->addToAsyncQueue([static::getUrl()]);
            $request->initAsyncRequest();
            $response = $request->getResult();

            foreach (end($response) as $item) {
                static::addCollection($item);
            }
        }

        foreach ($loadKeys as $id) {
            $result[$id] = static::getCollection()[$id];
        }

        static::$loads = [];
        return $result;
    }

    protected static function getCollection(): array
    {
        return static::$collection;
    }

    protected static function addCollection(array $fields)
    {
        static::$collection[$fields['ID']] = $fields;
    }
}