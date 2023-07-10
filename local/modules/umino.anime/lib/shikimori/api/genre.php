<?php


namespace Umino\Anime\Shikimori\API;


class Genre extends Entity
{
    protected static string $type = 'genres';

    public function get(): array
    {
        foreach (static::response() as $item) {
            if ($this->id != $item['id']) continue;
            return $item;
        }

        return [];
    }

    public static function getAsync(): array
    {
        $results = [];

        $ids = [];
        foreach (static::getIds() as $object) {
            $ids[] = $object->getId();
        }

        foreach (static::response() as $item) {
            if (!in_array($item['id'], $ids)) continue;
            $results[$item['id']] = $item;
        }

        return $results;
    }
}