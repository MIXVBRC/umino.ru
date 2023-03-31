<?php


namespace Umino\Kodik\Tables;

use Bitrix\Main;

/**
 * Class DataTable
 *
 * Fields:
 * <ul>
 * <li> ID int
 * <li> XML_ID string
 * <li> ACTIVE boolean
 * <li> DATE_CREATE datetime
 * <li> DATE_UPDATE datetime
 * <li> TITLE string
 * <li> INFO_ID int
 * <li> TRANSLATION_ID int
 * <li> SEASON int
 * <li> EPISODES int
 * <li> EPISODES_ALL int
 * <li> QUALITY string
 * <li> KODIK_DATE_CREATE datetime
 * <li> KODIK_DATE_UPDATE datetime
 * <li> LINK string
 * <li> SCREENSHOTS string
 * </ul>
 *
 * @package Umino\Kodik\Tables
 **/
class DataTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'umino_kodik_data';
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
            new Main\Entity\IntegerField('TITLE', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('INFO_ID', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('TRANSLATION_ID', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('SEASON', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('EPISODES', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('EPISODES_ALL', [
                'required' => true,
            ]),
            new Main\Entity\StringField('QUALITY'),
            new Main\Entity\DatetimeField('KODIK_DATE_CREATE', [
                'required' => true,
            ]),
            new Main\Entity\DatetimeField('KODIK_DATE_UPDATE', [
                'required' => true,
            ]),

            new Main\Entity\StringField('LINK'),
            new Main\Entity\TextField('SCREENSHOTS', [
                'serialized' => true,
            ]),

            new Main\Entity\ReferenceField(
                'INFO',
                InfoTable::class,
                ['=this.INFO_ID' => 'ref.ID'],
                ['join_type' => 'INNER']
            ),
            new Main\Entity\ReferenceField(
                'TRANSLATION',
                TranslationTable::class,
                ['=this.TRANSLATION_ID' => 'ref.ID'],
                ['join_type' => 'INNER']
            ),
        ];
    }
}