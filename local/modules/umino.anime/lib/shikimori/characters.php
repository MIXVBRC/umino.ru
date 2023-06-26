<?php


namespace Umino\Anime\Shikimori;


class Characters extends Entity
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
                'NAME_ALT' => $fields['ALTNAME'],
                'NAME_JAPANESE' => $fields['JAPANESE'],
                'SEYU' => People::getByIds(array_column($fields['SEYU'], 'ID')),
                'MANGAS' => Mangas::getByIds(array_column($fields['MANGAS'], 'ID')),
            ],
        ];
    }
}