<?php


namespace Umino\Anime\Shikimori\Import;


use Umino\Anime\Shikimori\Manager;

class AnimeRole extends Entity
{
    public function rebaseFields(array $fields): array
    {
        $fields = parent::rebaseFields($fields);

        if ($fields['TYPE'] == 'Character') {

            /** Character */

            Manager::addLoad(Character::getName(), $fields['PERSON']);

        } else {

            /** People */

            Manager::addLoad(People::getName(), $fields['PERSON']);

        }

        $fields['CODE'] = static::getCode($fields['PERSON'],$fields['NAME']);

        $fields['PERSON'] = self::getXmlId($fields['PERSON'], Manager::getIBCode($fields['TYPE']));

        return [
            'NAME' => $fields['NAME'],
            'XML_ID' => $fields['XML_ID'],
            'IBLOCK_ID' => $fields['IBLOCK_ID'],
            'CODE' => $fields['CODE'],
            'PROPERTY_VALUES' => [
                'ROLE_NAME' => $fields['ROLE_NAME'],
                'ROLE_NAME_ORIGIN' => $fields['ROLE_NAME_ORIGIN'],
                'PERSON' => $fields['PERSON'],
                'TYPE' => $fields['TYPE'],
            ]
        ];
    }
}