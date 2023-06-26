<?php


namespace Umino\Anime\Shikimori;


use CFile;


class Video extends Entity
{
    protected function rebase(array $fields): array
    {
        return [
            'XML_ID' => $this->getXmlId(),
            'CODE' => static::buildCode($this->getId(), $fields['NAME']),
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