<?php


namespace Umino\Anime\Shikimori;


class Mangas extends Entity
{
    protected function rebase(array $fields): array
    {
        return [
            'ID' => $fields['ID'],
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'NAME_ORIGIN' => $fields['NAME'],
            'NAME_OTHER' => array_merge(
                $fields['LICENSE_NAME_RU'],
                $fields['ENGLISH'],
                $fields['SYNONYMS'],
                $fields['JAPANESE'],
            ),
            'IMAGE' => Request::buildURL([$fields['IMAGE']['ORIGINAL']]),
            'TYPE' => $fields['KIND'],
            'SCORE' => $fields['SCORE'],
            'STATUS' => $fields['STATUS'],
            'VOLUMES' => $fields['VOLUMES'],
            'CHAPTERS' => $fields['CHAPTERS'],
            'AIRED_ON' => $fields['AIRED_ON'],
            'RELEASED_ON' => $fields['RELEASED_ON'],
            'DESCRIPTION' => strip_tags($fields['DESCRIPTION_HTML']),
            'LICENSORS' => $fields['LICENSORS'],
            'GENRES' => Genres::getCollection(array_column($fields['GENRES'], 'ID')),
            'PUBLISHERS' => Publishers::getCollection(array_column($fields['PUBLISHERS'], 'ID')),
        ];
    }
}