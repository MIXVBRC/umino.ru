<?php


namespace Umino\Anime\Shikimori;


class Animes extends Entity
{
    protected static int $maxScreenshots = 5;

    protected static function rebase(array $fields): array
    {
//        $fields['VIDEOS'] = Videos::creates([$fields['ID'] => array_column($fields['VIDEOS'], 'ID')]);
//        pre($fields['VIDEOS']);die;
//        /** @var Videos $item */
//        foreach ($fields['VIDEOS'] as &$item) {
//            $item = $item->getXmlId();
//        }

//        $fields['SCREENSHOTS'] = array_splice(Screenshots::create($fields['ID'])->getFields(), 0, static::$maxScreenshots);
//        foreach ($fields['SCREENSHOTS'] as &$screenshot) {
//            $screenshot = $screenshot->getFields()['URL'];
//        }

//        $fields['GENRES'] = Genres::creates(array_column($fields['GENRES'], 'ID'));
//        /** @var Genres $item */
//        foreach ($fields['GENRES'] as &$item) {
//            $item = $item->getXmlId();
//        }

        $fields['STUDIOS'] = Studios::creates(array_column($fields['STUDIOS'], 'ID'));
        /** @var Studios $item */
        foreach ($fields['STUDIOS'] as &$item) {
            $item = $item->getXmlId();
        }

//        $fields['ROLES'] = Roles::creates([$fields['ID']]);
//        /** @var Studios $item */
//        foreach ($fields['ROLES'] as &$item) {
//            $item = $item->getXmlId();
//        }

        return [
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'DETAIL_PICTURE' => Request::buildFileURL([$fields['IMAGE']['ORIGINAL']]),
            'DETAIL_TEXT' => strip_tags($fields['DESCRIPTION_HTML']),
            'PROPERTY_VALUES' => [
                'NAME_ORIGIN' => $fields['NAME'],
                'NAME_OTHER' => array_merge(
                    [$fields['LICENSE_NAME_RU']],
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

    protected static function getRoles()
    {

    }
}