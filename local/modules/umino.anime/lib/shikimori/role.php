<?php


namespace Umino\Anime\Shikimori;


class Role extends Entity
{
    protected static function rebase(array $fields): array
    {
        return [
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