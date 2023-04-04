<?php


namespace Umino\Anime\Tables;

use Bitrix\Main;

/**
 * Class KodikRequestTable
 *
 * Fields:
 * <ul>
 * <li> ID string
 * <li> DATE_REQUEST datetime
 * <li> URL string
 * <li> TIME string
 * <li> TOTAL int
 * <li> PREV_PAGE string
 * <li> NEXT_PAGE string
 * <li> RESULTS_COUNT int
 * </ul>
 *
 * @package Umino\Kodik\Tables
 **/
class KodikRequestTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'umino_anime_request';
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
            new Main\Entity\DatetimeField('DATE_REQUEST', [
                'required' => true,
                'default_value' => function () {
                    return new Main\Type\DateTime();
                }
            ]),
            new Main\Entity\StringField('URL', [
                'required' => true,
            ]),
            new Main\Entity\StringField('TIME'),
            new Main\Entity\IntegerField('TOTAL'),
            new Main\Entity\StringField('PREV_PAGE'),
            new Main\Entity\StringField('NEXT_PAGE'),
            new Main\Entity\IntegerField('RESULTS_COUNT', [
                'required' => true,
            ]),
        ];
    }
}