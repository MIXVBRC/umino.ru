<?php


namespace Umino\Anime\Tables;

use Bitrix\Main;

/**
 * Class LogTable
 *
 * Fields:
 * <ul>
 * <li> ID int
 * <li> DATE_CREATE datetime
 * <li> FILE string
 * <li> LINE string
 * <li> MESSAGE string
 * </ul>
 *
 * @package Umino\Kodik\Tables
 **/
class LogTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'umino_anime_log';
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
            new Main\Entity\DatetimeField('DATE_CREATE', [
                'required' => true,
                'default_value' => function () {
                    return new Main\Type\DateTime();
                }
            ]),
            new Main\Entity\StringField('FILE', [
                'required' => true,
            ]),
            new Main\Entity\StringField('LINE', [
                'required' => true,
            ]),
            new Main\Entity\TextField('MESSAGE', [
                'serialized' => true,
                'required' => true,
            ]),
        ];
    }
}