<?php


namespace Umino\Anime\Shikimori\API;


use Umino\Anime\Request;

class Studio extends Genre
{
    protected static string $type = 'studios';

    public function get(): array
    {
        foreach (static::response() as $item) {
            if ($this->id != $item['id']) continue;

            $item['image'] = static::getBaseUrl($item['image']);

            return $item;
        }

        return [];
    }

    public static function getAsync(): array
    {
        $results = [];

        foreach (static::response() as $item) {
            if (!in_array($item['id'], static::getIds())) continue;
            $item['image'] = static::getBaseUrl($item['image']);
            $results[$item['id']] = $item;
        }

        return $results;
    }
}