<?php


namespace Umino\Anime\Shikimori\Import;


use Bitrix\Main\Type\Date;
use Umino\Anime\Shikimori\API\MangaRole as APIRole;
use Umino\Anime\Shikimori\Manager;

class Manga extends Entity
{
    public function rebaseFields(array $fields): array
    {
        $fields = parent::rebaseFields($fields);

        $fields['GENRES'] = array_column($fields['GENRES'], 'ID');
        foreach ($fields['GENRES'] as &$sid) {
            Manager::addLoad(Genre::getName(), $sid);
            $sid = self::getXmlId($sid);
        } unset($sid);

        $fields['PUBLISHERS'] = array_column($fields['PUBLISHERS'], 'ID');
        foreach ($fields['PUBLISHERS'] as &$sid) {
            Manager::addLoad(Publisher::getName(), $sid);
            $sid = self::getXmlId($sid);
        } unset($sid);

        $fields['ROLES'] = APIRole::rebase($this->api->roles());
        foreach ($fields['ROLES'] as &$role) {
            Manager::addLoad(MangaRole::getName(), $role['id'], $this->getId());
            $role['id'] = self::getXmlId($role['id']);

            if ($role['type'] == Character::getName()) {
                $fields['CHARACTERS'][] = $role['id'];
            } else {
                $fields['PEOPLE'][] = $role['id'];
            }
        } unset($role);

        $fields['AIRED_ON'] = Date::createFromTimestamp(strtotime($fields['AIRED_ON']))->toString();
        $fields['RELEASED_ON'] = Date::createFromTimestamp(strtotime($fields['RELEASED_ON']))->toString();

        $fields['NAME_ALL'] = array_unique(array_merge(
            [$fields['RUSSIAN'], $fields['NAME']],
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
                'FRANCHISE' => $fields['FRANCHISE'],
                'TYPE' => $fields['KIND'],
                'SCORE' => $fields['SCORE'],
                'STATUS' => $fields['STATUS'],
                'VOLUMES' => $fields['VOLUMES'],
                'CHAPTERS' => $fields['CHAPTERS'],
                'AIRED_ON' => $fields['AIRED_ON'],
                'RELEASED_ON' => $fields['RELEASED_ON'],
                'LICENSORS' => $fields['LICENSORS'],
                'GENRES' => $fields['GENRES'],
                'PUBLISHERS' => $fields['PUBLISHERS'],
                'PEOPLE' => $fields['PEOPLE'],
                'CHARACTERS' => $fields['CHARACTERS'],
            ]
        ];
    }
}