<?php


namespace Umino\Anime\Shikimori\Import;


class Translation extends Entity
{
    public function rebaseFields(array $fields): array
    {
        $fields = parent::rebaseFields($fields);

        $fields['NAME'] = $fields['TITLE'];
        $fields['CODE'] = static::getCode($this->getId(),$fields['NAME']);

        return [
            'NAME' => $fields['TITLE'],
            'XML_ID' => $fields['XML_ID'],
            'IBLOCK_ID' => $fields['IBLOCK_ID'],
            'CODE' => $fields['CODE'],
            'PROPERTY_VALUES' => [
                'TYPE' => $fields['TYPE'],
            ]
        ];
    }
}