<?php


namespace Umino\Anime\Shikimori;


class People extends Entity
{
    protected static function rebase(array $fields): array
    {
        return [
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'DETAIL_PICTURE' => Request::buildFileURL([$fields['IMAGE']['ORIGINAL']]),
            'PROPERTY_VALUES' => [
                'NAME_ORIGIN' => $fields['NAME'],
                'NAME_JAPANESE' => $fields['JAPANESE'],
                'JOB' => $fields['JOB_TITLE'],
            ],
        ];
    }
}