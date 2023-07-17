<?php


namespace Umino\Anime\Shikimori\Import;


use Bitrix\Main\Type\Date;
use Umino\Anime\Shikimori\API\AnimeRole as APIRole;
use Umino\Anime\Shikimori\API\Episode as APIEpisode;
use Umino\Anime\Shikimori\Manager;

class Anime extends Entity
{
    public function rebaseFields(array $fields): array
    {
        $fields = parent::rebaseFields($fields);

        /** Episode */

        $episodes = APIEpisode::rebase($this->api->episodes());
        foreach ($episodes as $episode) {
            Manager::addLoad(Episode::getName(), $episode['id'], $this->getId());
        }

        /** Video */

        $fields['VIDEOS'] = array_column($fields['VIDEOS'], 'ID');
        foreach ($fields['VIDEOS'] as &$sid) {
            Manager::addLoad(Video::getName(), $sid, $this->getId());
            $sid = static::getXmlId($sid, Manager::getIBCode(Video::getName()));
        } unset($sid);

        /** Genre */

        $fields['GENRES'] = array_column($fields['GENRES'], 'ID');
        foreach ($fields['GENRES'] as &$sid) {
            Manager::addLoad(Genre::getName(), $sid);
            $sid = static::getXmlId($sid, Manager::getIBCode(Genre::getName()));
        } unset($sid);

        /** Studio */

        $fields['STUDIOS'] = array_column($fields['STUDIOS'], 'ID');
        foreach ($fields['STUDIOS'] as &$sid) {
            Manager::addLoad(Studio::getName(), $sid);
            $sid = static::getXmlId($sid, Manager::getIBCode(Studio::getName()));
        } unset($sid);

        /** Franchise */

        if ($fields['FRANCHISE']) {
            Manager::addLoad(Franchise::getName(), $fields['FRANCHISE'], $this->getId());
            $fields['FRANCHISE'] = static::getXmlId($fields['FRANCHISE'], Manager::getIBCode(Franchise::getName()));
        }

        /** Role */

        $fields['ROLES'] = APIRole::rebase($this->api->roles());
        foreach ($fields['ROLES'] as &$role) {
            Manager::addLoad(AnimeRole::getName(), $role['id'], $this->getId());
            $role['id'] = static::getXmlId($role['id'], Manager::getIBCode(AnimeRole::getName()));

            if ($role['type'] == Character::getName()) {
                $fields['CHARACTERS'][] = $role['id'];
            } else {
                $fields['PEOPLE'][] = $role['id'];
            }
        } unset($role);

        $fields['AIRED_ON'] = Date::createFromTimestamp(strtotime($fields['AIRED_ON']))->toString();
        $fields['RELEASED_ON'] = Date::createFromTimestamp(strtotime($fields['RELEASED_ON']))->toString();

        $fields['SCREENSHOTS'] = $this->api->screenshots(Manager::$maxScreenshots);

        $fields['NAME_ALL'] = array_unique(array_merge(
            [$fields['LICENSE_NAME_RU'], $fields['RUSSIAN'], $fields['NAME']],
            $fields['ENGLISH'],
            $fields['SYNONYMS'],
            $fields['JAPANESE'],
        ));
        $fields['NAME_ORIGIN'] = $fields['NAME'];
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
                'NAME_EN' => $fields['NAME_ORIGIN'],
                'NAME_ALL' => $fields['NAME_ALL'],
                'SCREENSHOTS' => $fields['SCREENSHOTS'],
                'TYPE' => $fields['KIND'],
                'SCORE' => $fields['SCORE'],
                'STATUS' => $fields['STATUS'],
                'EPISODES' => $fields['EPISODES'],
                'EPISODES_AIRED' => $fields['EPISODES_AIRED'],
                'AIRED_ON' => $fields['AIRED_ON'],
                'RELEASED_ON' => $fields['RELEASED_ON'],
                'RATING' => $fields['RATING'],
                'DURATION' => $fields['DURATION'],
                'FRANCHISE' => $fields['FRANCHISE'],
                'LICENSORS' => $fields['LICENSORS'],
                'GENRES' => $fields['GENRES'],
                'STUDIOS' => $fields['STUDIOS'],
                'VIDEOS' => $fields['VIDEOS'],
                'PEOPLE' => $fields['PEOPLE'],
                'CHARACTERS' => $fields['CHARACTERS'],
            ]
        ];
    }
}