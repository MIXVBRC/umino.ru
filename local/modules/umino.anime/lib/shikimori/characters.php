<?php


namespace Umino\Anime\Shikimori;


class Characters extends Entity
{
    protected function rebase(array $fields): array
    {
        return [
            'ID' => $fields['ID'],
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'NAME_ORIGIN' => $fields['NAME'],
            'NAME_ALT' => $fields['ALTNAME'],
            'NAME_JAPANESE' => $fields['JAPANESE'],
            'IMAGE' => Request::buildURL([$fields['IMAGE']['ORIGINAL']]),
            'DESCRIPTION' => strip_tags($fields['DESCRIPTION_HTML']),
            'SEYU' => People::getCollection(array_column($fields['SEYU'], 'ID')),
            'MANGAS' => Mangas::getCollection(array_column($fields['MANGAS'], 'ID')),
        ];
    }
}