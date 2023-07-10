<?php


namespace Umino\Anime\Shikimori;


class Screenshots extends Entity
{
    protected static function rebase(array $fields): array
    {
        $result = [];

        foreach ($fields as $item) {
            $result[] = Image::create($item['ORIGINAL'], [
                'URL' => Request::buildFileURL([$item['ORIGINAL']]),
            ]);
        }

        return $result;
    }

    public static function create(string $id, array $fields = []): ?object
    {
        $id = static::buildId($id);
        $xmlId = static::buildXmlId($id, static::getClass());

        if ($fields) {

            return new static($id, $xmlId, $fields);

        } else if ($item = static::getById($id)) {

            return $item;

        } else {

            static::addLoad([$id]);
            $fields = static::load()[$id];
            
            if (empty($fields)) return null;

            $fields = static::rebase($fields);

            return new static($id, $xmlId, $fields);
        }
    }

    protected static function getUrl(array $additional = []): string
    {
        return Request::buildApiURL(array_merge([Animes::getName()], $additional, [static::getName()]));
    }
}