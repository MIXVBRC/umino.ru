<?php


namespace Umino\Anime\Shikimori\Import;


class Publisher extends Entity
{
    public function rebaseFields(array $fields): array
    {
        $fields['XML_ID'] = $this->getId();
        $fields['CODE'] = static::getCode($this->getId(),$fields['NAME']);

        return [
            'NAME' => $fields['NAME'],
            'XML_ID' => $fields['XML_ID'],
            'CODE' => $fields['CODE'],
        ];
    }
}