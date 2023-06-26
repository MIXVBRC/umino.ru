<?php


namespace Umino\Anime\Shikimori;


class Publishers extends Genres
{
    protected function rebase(array $fields): array
    {
        return [
            'XML_ID' => $this->getXmlId(),
            'CODE' => static::buildCode($this->getId(), $fields['NAME']),
            'NAME' => $fields['NAME'],
        ];
    }
}