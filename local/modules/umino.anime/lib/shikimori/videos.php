<?php


namespace Umino\Anime\Shikimori;


use CFile;


class Videos extends Entity
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

    protected static function getUrl(array $additional = []): string
    {
        return Request::buildApiURL(array_merge([Animes::getName()], $additional, [static::getName()]));
    }
}