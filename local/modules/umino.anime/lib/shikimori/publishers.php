<?php


namespace Umino\Anime\Shikimori;


class Publishers extends Genres
{
    protected function rebase(array $fields): array
    {
        return [
            'ID' => $fields['ID'],
            'NAME' => $fields['NAME'],
        ];
    }
}