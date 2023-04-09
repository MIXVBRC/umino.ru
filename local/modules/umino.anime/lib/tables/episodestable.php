<?php


namespace Umino\Anime\Tables;

use Bitrix\Main;

/**
 * Class EpisodesTable
 *
 * Fields:
 * <ul>
 * <li> ID int
 * <li> DATE_CREATE datetime
 * <li> RESULT_ID int
 * <li> DATA_ID int
 * <li> SEASON int
 * <li> EPISODE int
 * <li> SEASON_LINK string
 * <li> EPISODE_LINK string
 * </ul>
 *
 * @package Umino\Kodik\Tables
 **/
class EpisodesTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'umino_anime_episodes';
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
            new Main\Entity\IntegerField('RESULT_ID', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('DATA_ID'),
            new Main\Entity\IntegerField('SEASON', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('EPISODE', [
                'required' => true,
            ]),
            new Main\Entity\StringField('SEASON_LINK', [
                'required' => true,
            ]),
            new Main\Entity\StringField('EPISODE_LINK', [
                'required' => true,
            ]),

            new Main\Entity\ReferenceField(
                'RESULT',
                DataTable::class,
                ['=this.RESULT_ID' => 'ref.ID'],
                ['join_type' => 'INNER']
            ),
            new Main\Entity\ReferenceField(
                'DATA',
                DataTable::class,
                ['=this.DATA_ID' => 'ref.ID'],
                ['join_type' => 'INNER']
            ),
        ];
    }
}