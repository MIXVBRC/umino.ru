<?php


namespace Umino\Anime\Shikimori;


class Studios extends Genres
{
    protected function rebase(array $fields): array
    {
        return [
            'XML_ID' => $this->getXmlId(),
            'CODE' => static::buildCode($this->getId(), $fields['FILTERED_NAME'] ?: $fields['NAME']),
            'NAME' => $fields['FILTERED_NAME'] ?: $fields['NAME'],
            'DETAIL_PICTURE' => $fields['IMAGE'],
            'PROPERTY_VALUES' => [
                'NAME_ORIGIN' => $fields['NAME'],
            ],
        ];
    }
}