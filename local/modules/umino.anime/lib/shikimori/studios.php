<?php


namespace Umino\Anime\Shikimori;


class Studios extends Genres
{
    protected static function rebase(array $fields): array
    {
        return [
            'NAME' => $fields['FILTERED_NAME'] ?: $fields['NAME'],
            'DETAIL_PICTURE' => Request::buildFileURL([$fields['IMAGE']]),
            'PROPERTY_VALUES' => [
                'NAME_ORIGIN' => $fields['NAME'],
            ],
        ];
    }
}