<?php


namespace Umino\Anime\Shikimori\Import;


class Video extends Entity
{
    public function rebaseFields(array $fields): array
    {
        $fields = parent::rebaseFields($fields);

        $fields['CODE'] = static::getCode($this->getId(),$fields['NAME']);

        return [
            'NAME' => $fields['NAME'],
            'XML_ID' => $fields['XML_ID'],
            'IBLOCK_ID' => $fields['IBLOCK_ID'],
            'CODE' => $fields['CODE'],
            'PROPERTY_VALUES' => [
                'URL' => $fields['URL'],
                'IMAGE_URL' => $fields['IMAGE_URL'],
                'PLAYER_URL' => $fields['PLAYER_URL'],
                'TYPE' => $fields['KIND'],
                'HOSTING' => $fields['HOSTING'],
            ]
        ];
    }
}