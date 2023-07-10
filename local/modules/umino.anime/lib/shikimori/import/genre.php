<?php


namespace Umino\Anime\Shikimori\Import;


class Genre extends Entity
{
    public function rebaseFields(array $fields): array
    {
        $fields = parent::rebaseFields($fields);

        $fields['NAME_ORIGIN'] = $fields['NAME'];
        $fields['NAME'] = $fields['RUSSIAN'] ?: $fields['NAME'];
        $fields['CODE'] = static::getCode($this->getId(),$fields['NAME']);

        return [
            'NAME' => $fields['NAME'],
            'XML_ID' => $fields['XML_ID'],
            'IBLOCK_ID' => $fields['IBLOCK_ID'],
            'CODE' => $fields['CODE'],
            'PROPERTY_VALUES' => [
                'NAME_EN' => $fields['NAME_ORIGIN'],
                'TYPE' => $fields['ENTRY_TYPE'],
            ]
        ];
    }
}