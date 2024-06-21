<?php


namespace Umino\Anime\Shikimori\Import;


use Umino\Anime\Shikimori\Manager;

class Episode extends Entity
{
    public function rebaseFields(array $fields): array
    {
        $fields = parent::rebaseFields($fields);

        Manager::addLoad(Translation::getName(), $fields['TRANSLATION']['ID']);

        $fields['NAME'] = [
            $fields['TITLE'],
            $fields['TRANSLATION']['TITLE'],
            $fields['TRANSLATION']['TYPE'],
            $fields['TYPE'],
        ];
        if ($fields['SEASON']) {
            $fields['NAME'][] =$fields['SEASON'] . ($fields['SEASON_TYPE'] ? ' | '. $fields['SEASON_TYPE'] : '');
        }
        $fields['NAME'] = implode(' | ', $fields['NAME']);

        $fields['ANIME'] = self::getXmlId($fields['SHIKIMORI_ID'], Manager::getIBCode(Anime::getName()));
        $fields['TRANSLATION'] = self::getXmlId($fields['TRANSLATION']['ID'], Manager::getIBCode(Translation::getName()));

        foreach ($fields['EPISODES'] as $num => &$episode) {
            $episode = [
                'VALUE' => $episode,
                'DESCRIPTION' => $num,
            ];
        }

        return [
            'NAME' => $fields['NAME'],
            'XML_ID' => $fields['XML_ID'],
            'IBLOCK_ID' => $fields['IBLOCK_ID'],
            'PROPERTY_VALUES' => [
                'KODIK_TITLE' => $fields['TITLE'],
                'KODIK_TITLE_ORIG' => $fields['TITLE_ORIG'],
                'TYPE' => $fields['TYPE'],
                'QUALITY' => $fields['QUALITY'],
                'LINK' => $fields['LINK'],
                'SEASON' => $fields['SEASON'],
                'SEASON_TYPE' => $fields['SEASON_TYPE'],
                'EPISODES' => $fields['EPISODES'],
                'EPISODES_COUNT' => $fields['EPISODES_COUNT'],
                'TRANSLATION' => $fields['TRANSLATION'],
                'ANIME' => $fields['ANIME'],
            ]
        ];
    }
}