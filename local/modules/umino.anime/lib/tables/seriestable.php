<?php


namespace Umino\Anime\Tables;

use Bitrix\Main;

/**
 * Class SeriesTable
 *
 * Fields:
 * <ul>
 * <li> ID int
 * <li> DATE_CREATE datetime
 * <li> DATA_ID int
 * <li> LINK string
 * </ul>
 *
 * @package Umino\Kodik\Tables
 **/
class SeriesTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'umino_anime_series';
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
            new Main\Entity\IntegerField('DATA_ID', [
                'required' => true,
            ]),
            new Main\Entity\StringField('LINK', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('EPISODE', [
                'required' => true,
            ]),
            new Main\Entity\ReferenceField(
                'DATA',
                DataTable::class,
                ['=this.DATA_ID' => 'ref.ID'],
                ['join_type' => 'INNER']
            ),
        ];
    }
}