<?php


namespace Umino\Anime\Shikimori\Import;


class Role extends Entity
{
    public function rebaseFields(array $fields): array
    {
        $fields['XML_ID'] = $this->getId();
        $fields['CODE'] = static::getCode($fields['PERSON'],$fields['NAME']);

        return [
            'NAME' => $fields['NAME'],
            'XML_ID' => $fields['XML_ID'],
            'CODE' => $fields['CODE'],
            'PROPERTY_VALUES' => [
                'NAME_EN' => $fields['NAME_ORIGIN'],
                'PERSON' => $fields['PERSON'],
                'CLASS' => $fields['CLASS'],
            ]
        ];
    }
}