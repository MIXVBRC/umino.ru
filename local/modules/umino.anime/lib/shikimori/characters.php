<?php


namespace Umino\Anime\Shikimori;


class Characters extends Entity
{
    protected static function rebase(array $fields): array
    {
        return [
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'DETAIL_PICTURE' => Request::buildFileURL([$fields['IMAGE']['ORIGINAL']]),
            'DETAIL_TEXT' => strip_tags($fields['DESCRIPTION_HTML']),
            'PROPERTY_VALUES' => [
                'NAME_ORIGIN' => $fields['NAME'],
                'NAME_ALT' => $fields['ALTNAME'],
                'NAME_JAPANESE' => $fields['JAPANESE'],
//                'SEYU' => People::creates(array_column($fields['SEYU'], 'ID')),
//                'MANGAS' => Mangas::creates(array_column($fields['MANGAS'], 'ID')),
            ],
        ];
    }
}