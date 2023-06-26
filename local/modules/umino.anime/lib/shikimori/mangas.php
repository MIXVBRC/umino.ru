<?php


namespace Umino\Anime\Shikimori;


class Mangas extends Entity
{
    protected function rebase(array $fields): array
    {
        return [
            'XML_ID' => $this->getXmlId(),
            'CODE' => static::buildCode($this->getId(), $fields['RUSSIAN'] ?: $fields['NAME']),
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'DETAIL_PICTURE' => Request::buildFileURL([$fields['IMAGE']['ORIGINAL']]),
            'DETAIL_TEXT' => strip_tags($fields['DESCRIPTION_HTML']),
            'PROPERTY_VALUES' => [
                'NAME_ORIGIN' => $fields['NAME'],
                'NAME_OTHER' => array_merge(
                    $fields['LICENSE_NAME_RU'],
                    $fields['ENGLISH'],
                    $fields['SYNONYMS'],
                    $fields['JAPANESE'],
                ),
                'TYPE' => $fields['KIND'],
                'SCORE' => $fields['SCORE'],
                'STATUS' => $fields['STATUS'],
                'VOLUMES' => $fields['VOLUMES'],
                'CHAPTERS' => $fields['CHAPTERS'],
                'AIRED_ON' => $fields['AIRED_ON'],
                'RELEASED_ON' => $fields['RELEASED_ON'],
                'LICENSORS' => $fields['LICENSORS'],
                'GENRES' => Genres::getByIds(array_column($fields['GENRES'], 'ID')),
                'PUBLISHERS' => Publishers::getByIds(array_column($fields['PUBLISHERS'], 'ID')),
            ],
        ];
    }
}