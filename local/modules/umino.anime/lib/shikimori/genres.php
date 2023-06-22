<?php


namespace Umino\Anime\Shikimori;


class Genres extends Entity
{
    protected static array $collection = [];

    protected function rebase(array $fields): array
    {
        return [
            'ID' => $fields['ID'],
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'NAME_ORIGIN' => $fields['NAME'],
        ];
    }

    protected static function addToLoad(int $id)
    {
        static::$loads[$id] = static::getUrl();
    }

    protected static function load(): array
    {
        $result = [];

        $loadKeys = array_keys(static::$loads);
        $collectionKeys = array_keys(static::$collection);

        if (array_diff($loadKeys, $collectionKeys)) {
            $request = new Request();
            $request->addToAsyncQueue([static::getUrl()]);
            $request->initAsyncRequest();
            $response = $request->getResult();

            foreach (end($response) as $item) {
                static::$collection[$item['ID']] = $item;
            }
        }

        foreach ($loadKeys as $id) {
            $result[$id] = static::$collection[$id];
        }

        static::$loads = [];
        return $result;
    }

    protected function setFields(array $fields)
    {
        if (empty($fields)) {
            static::addToLoad($this->getId());
            $fields = static::load()[$this->getId()];
        }
        $this->fields = $this->rebase($fields);
    }

//    public static function getCollection(array $ids): array
//    {
//        $result = [];
//        $collection = [];
//
//        foreach ($ids as $id) {
//            if ($object = self::getById($id)) {
//                $result[$id] = $object;
//            } else {
//                $collection[$id] = self::load($id);
//            }
//        }
//
//        $class = self::getClass();
//        foreach ($collection as $id => $fields) {
//            $result[$id] = new $class((int) $id, $fields);
//        }
//
//        return $result;
//    }
}