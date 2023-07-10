<?php


namespace Umino\Anime\Shikimori\Import;


use Umino\Anime\Shikimori\Manager;

class Character extends Entity
{
    public function rebaseFields(array $fields): array
    {
        $fields = parent::rebaseFields($fields);

        $fields['SEYU'] = array_column($fields['SEYU'], 'ID');
        foreach ($fields['SEYU'] as &$sid) {
            Manager::addLoad(People::getName(), $sid);
            $sid = self::getXmlId($sid);
        } unset($sid);

        $fields['MANGAS'] = array_column($fields['MANGAS'], 'ID');
        foreach ($fields['MANGAS'] as &$sid) {
            Manager::addLoad(Manga::getName(), $sid);
            $sid = self::getXmlId($sid);
        } unset($sid);

        $fields['NAME_EN'] = $fields['NAME'];
        $fields['NAME'] = $fields['RUSSIAN'] ?: $fields['NAME'];
        $fields['CODE'] = static::getCode($this->getId(),$fields['NAME']);

        return [
            'NAME' => $fields['NAME'],
            'XML_ID' => $fields['XML_ID'],
            'IBLOCK_ID' => $fields['IBLOCK_ID'],
            'CODE' => $fields['CODE'],
            'DETAIL_PICTURE' => $fields['IMAGE'],
            'DETAIL_TEXT' => $fields['DESCRIPTION'],
            'PROPERTY_VALUES' => [
                'NAME_EN' => $fields['NAME_EN'],
                'NAME_JP' => $fields['JAPANESE'],
                'NAME_ALT' => $fields['ALTNAME'],
                'SEYU' => $fields['SEYU'],
                'MANGAS' => $fields['MANGAS'],
                'SPOILER' => $fields['SPOILER'],
            ]
        ];
    }
}