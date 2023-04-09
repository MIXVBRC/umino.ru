<?php


namespace Umino\Anime\Tables;

use Bitrix\Main;

/**
 * Class KodikResultTable
 *
 * Fields:
 * <ul>
 * <li> ID string
 * <li> TYPE string
 * <li> DATE_CREATE datetime
 * <li> DATE_UPDATE datetime
 * <li> DATE_IMPORT datetime
 * <li> LINK string
 * <li> TITLE string
 * <li> TITLE_ORIG array
 * <li> OTHER_TITLE string
 * <li> TRANSLATION array
 * <li> YEAR string
 * <li> LAST_SEASON string
 * <li> LAST_EPISODE string
 * <li> EPISODES_COUNT string
 * <li> KINOPOISK_ID string
 * <li> WORLDART_LINK string
 * <li> SHIKIMORI_ID string
 * <li> QUALITY string
 * <li> CAMRIP string
 * <li> LGBT string
 * <li> BLOCKED_COUNTRIES array
 * <li> BLOCKED_SEASONS array
 * <li> CREATED_AT datetime
 * <li> UPDATED_AT datetime
 * <li> SCREENSHOTS array
 * <li> REQUEST_ID int
 * </ul>
 *
 * @package Umino\Kodik\Tables
 **/
class KodikResultTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'umino_anime_result';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new Main\Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new Main\Entity\StringField('KODIK_ID'),
            new Main\Entity\DatetimeField('DATE_CREATE', [
                'required' => true,
                'default_value' => function () {
                    return new Main\Type\DateTime();
                }
            ]),
            new Main\Entity\DatetimeField('DATE_UPDATE', [
                'required' => true,
                'default_value' => function () {
                    return new Main\Type\DateTime();
                }
            ]),
            new Main\Entity\DatetimeField('DATE_IMPORT'),
            new Main\Entity\StringField('TYPE', [
                'required' => true,
                'default_value' => 'unknown',
            ]),
            new Main\Entity\StringField('LINK', [
                'required' => true,
            ]),
            new Main\Entity\StringField('TITLE', [
                'required' => true,
            ]),
            new Main\Entity\StringField('TITLE_ORIG'),
            new Main\Entity\TextField('OTHER_TITLE', [
                'serialized' => true,
            ]),
            new Main\Entity\TextField('TRANSLATION', [
                'required' => true,
                'serialized' => true,
            ]),
            new Main\Entity\StringField('YEAR'),
            new Main\Entity\IntegerField('LAST_SEASON', [
                'default_value' => 1,
            ]),
            new Main\Entity\IntegerField('LAST_EPISODE', [
                'default_value' => 1,
            ]),
            new Main\Entity\IntegerField('EPISODES_COUNT', [
                'default_value' => 1,
            ]),
            new Main\Entity\StringField('KINOPOISK_ID'),
            new Main\Entity\StringField('WORLDART_LINK'),
            new Main\Entity\StringField('SHIKIMORI_ID'),
            new Main\Entity\StringField('QUALITY'),
            new Main\Entity\StringField('CAMRIP'),
            new Main\Entity\StringField('LGBT'),
            new Main\Entity\TextField('BLOCKED_COUNTRIES', [
                'serialized' => true,
            ]),
            new Main\Entity\TextField('BLOCKED_SEASONS', [
                'serialized' => true,
            ]),
            new Main\Entity\DatetimeField('CREATED_AT'),
            new Main\Entity\DatetimeField('UPDATED_AT'),
            new Main\Entity\TextField('SCREENSHOTS', [
                'serialized' => true,
            ]),
            new Main\Entity\IntegerField('REQUEST_ID', [
                'required' => true,
            ]),
        ];
    }
}