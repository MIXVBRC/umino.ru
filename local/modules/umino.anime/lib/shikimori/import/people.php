<?php


namespace Umino\Anime\Shikimori\Import;


use Bitrix\Main\Type\Date;

class People extends Entity
{
    public function rebaseFields(array $fields): array
    {
        $fields = parent::rebaseFields($fields);

        $fields['NAME_ORIGIN'] = $fields['NAME'];
        $fields['NAME'] = $fields['RUSSIAN'] ?: $fields['NAME'];
        $fields['CODE'] = static::getCode($this->getId(),$fields['NAME']);

        $fields['BIRTHDAY'] = $fields['BIRTHDAY'] ?: $fields['BIRTH_ON'];
        $fields['BIRTHDAY'] = implode('.', [
            $fields['BIRTHDAY']['DAY'],
            $fields['BIRTHDAY']['MONTH'],
            $fields['BIRTHDAY']['YEAR']
        ]);
        $fields['BIRTHDAY'] = Date::createFromTimestamp(strtotime($fields['BIRTHDAY']))->toString();

        return [
            'NAME' => $fields['NAME'],
            'XML_ID' => $fields['XML_ID'],
            'IBLOCK_ID' => $fields['IBLOCK_ID'],
            'CODE' => $fields['CODE'],
            'DETAIL_PICTURE' => $fields['IMAGE'],
            'PROPERTY_VALUES' => [
                'NAME_EN' => $fields['NAME_ORIGIN'],
                'NAME_JP' => $fields['JAPANESE'],
                'JOB' => $fields['JOB_TITLE'],
                'WEBSITE' => $fields['WEBSITE'],
                'BIRTHDAY' => $fields['BIRTHDAY'],
            ]
        ];
    }
}