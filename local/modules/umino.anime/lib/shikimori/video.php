<?php


namespace Umino\Anime\Shikimori;


use CFile;


class Videos extends Genres
{
    protected static function rebase(array $fields): array
    {
        return [
            'NAME' => $fields['NAME'],
            'DETAIL_PICTURE' => CFile::MakeFileArray($fields['IMAGE_URL']),
            'PROPERTY_VALUES' => [
                'URL' => $fields['URL'],
                'TYPE' => $fields['KIND'],
                'HOSTING' => $fields['HOSTING'],
            ],
        ];
    }

    protected static function loadFromRequest(): array
    {
        return [];
    }
}