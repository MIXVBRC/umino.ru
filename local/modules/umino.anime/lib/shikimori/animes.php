<?php


namespace Umino\Anime\Shikimori;


class Animes extends Entity
{
    protected function rebase(array $fields): array
    {
        foreach ($fields['VIDEOS'] as &$item) {
            $item = Video::create($item['ID'], $item)->getXmlId();
        }

//        $fields['SCREENSHOTS'] = Screenshots::create($fields['ID']);

        $fields['GENRES'] = Genres::getByIds(array_column($fields['GENRES'], 'ID'));
        /** @var Genres $item */
        foreach ($fields['GENRES'] as &$item) {
            $item = $item->getXmlId();
        }

        $fields['STUDIOS'] = Studios::getByIds(array_column($fields['STUDIOS'], 'ID'));
        /** @var Studios $item */
        foreach ($fields['STUDIOS'] as &$item) {
            $item = $item->getXmlId();
        }

//        $fields['ROLES'] = Roles::create($fields['ID']);
//        /** @var Studios $item */
//        foreach ($fields['ROLES'] as &$item) {
//            $item = $item->getXmlId();
//        }

        return [
            'XML_ID' => $this->getXmlId(),
            'CODE' => static::buildCode($this->getId(), $fields['RUSSIAN'] ?: $fields['NAME']),
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'DETAIL_PICTURE' => Image::create($fields['IMAGE']['ORIGINAL']),
            'DETAIL_TEXT' => strip_tags($fields['DESCRIPTION_HTML']),
            'PROPERTY_VALUES' => [
                'NAME_ORIGIN' => $fields['NAME'],
                'NAME_OTHER' => array_merge(
                    $fields['LICENSE_NAME_RU'],
                    $fields['ENGLISH'],
                    $fields['SYNONYMS'],
                    $fields['JAPANESE'],
                ),
                'SCREENSHOTS' => $fields['SCREENSHOTS'],
                'FRANCHISE' => $fields['FRANCHISE'],
                'TYPE' => $fields['KIND'],
                'SCORE' => $fields['SCORE'],
                'STATUS' => $fields['STATUS'],
                'EPISODES' => $fields['EPISODES'],
                'EPISODES_AIRED' => $fields['EPISODES_AIRED'],
                'AIRED_ON' => $fields['AIRED_ON'],
                'RELEASED_ON' => $fields['RELEASED_ON'],
                'RATING' => $fields['RATING'],
                'DURATION' => $fields['DURATION'],
                'LICENSORS' => $fields['LICENSORS'],
                'GENRES' => $fields['GENRES'],
                'STUDIOS' => $fields['STUDIOS'],
                'VIDEOS' => $fields['VIDEOS'],
                'ROLES' => $fields['ROLES'],
            ]
        ];
    }
}