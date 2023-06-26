<?php


namespace Umino\Anime\Shikimori;


class Videos extends Entity
{
    protected function rebase(array $fields): array
    {
        $result = [];

        foreach ($fields as $item) {
            $item = Video::create($item['ID'], $item);
            $result[] = [
                'ID' => $item->getId(),
                'VIDEO' => $item,
            ];
        }

        return $result;
    }

    protected static function getUrl(array $additional = []): string
    {
        return Request::buildApiURL(array_merge([Animes::getName()], $additional, [static::getName()]));
    }
}