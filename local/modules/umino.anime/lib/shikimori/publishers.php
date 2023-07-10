<?php


namespace Umino\Anime\Shikimori;


class Publishers extends Genres
{
    protected static function rebase(array $fields): array
    {
        return [
            'NAME' => $fields['NAME'],
        ];
    }
}