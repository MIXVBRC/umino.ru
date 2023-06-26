<?php


namespace Umino\Anime\Shikimori;


class Role extends Entity
{
    protected function rebase(array $fields): array
    {
        return [
            'XML_ID' => $this->getXmlId(),
            'CODE' => static::buildCode($this->getId(), $fields['NAME']),
            'NAME' => $fields['NAME'],
            'PROPERTY_VALUES' => [
                'NAME_ORIGIN' => $fields['NAME_ORIGIN'],
                'ENTITY' => $fields['ENTITY'],
            ],
        ];
    }

    protected static function loadFromRequest(): array
    {
        return [];
    }
}