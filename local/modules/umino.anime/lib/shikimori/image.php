<?php


namespace Umino\Anime\Shikimori;


class Image extends Entity
{
    protected static bool $md5Id = true;

    public static function create(string $id, array $fields = []): ?object
    {
        $id = static::buildId($id);
        $xmlId = static::buildXmlId($id, static::getClass());

        if ($fields) {
            return new static($id, $xmlId, $fields);
        } if ($item = static::getById($id)) {
            return $item;
        } else {
            return null;
        }
    }

    protected static function load(): array
    {
        return [];
    }
}