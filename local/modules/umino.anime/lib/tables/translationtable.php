<?php


namespace Umino\Anime\Tables;

use Bitrix\Main;

/**
 * Class TranslationTable
 *
 * Fields:
 * <ul>
 * <li> ID int
 * <li> XML_ID string
 * <li> ACTIVE boolean
 * <li> DATE_CREATE datetime
 * <li> DATE_UPDATE datetime
 * <li> TITLE string
 * <li> KODIK_ID int
 * <li> TYPE string
 * </ul>
 *
 * @package Umino\Kodik\Tables
 **/
class TranslationTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'umino_anime_translation';
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
            new Main\Entity\StringField('XML_ID', [
                'required' => true,
            ]),
            new Main\Entity\BooleanField('ACTIVE', [
                'default_value' => true,
            ]),
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
            new Main\Entity\StringField('TITLE', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('KODIK_ID', [
                'required' => true,
            ]),
            new Main\Entity\StringField('TYPE', [
                'required' => true,
            ]),
        ];
    }
}