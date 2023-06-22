<?php


namespace Umino\Anime\Shikimori;


class People extends Entity
{
    protected function rebase(array $fields): array
    {
        return [
            'ID' => $fields['ID'],
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'NAME_ORIGIN' => $fields['NAME'],
            'NAME_JAPANESE' => $fields['JAPANESE'],
            'IMAGE' => Request::buildURL([$fields['IMAGE']['ORIGINAL']]),
            'JOB' => $fields['JOB_TITLE'],
        ];
    }
}