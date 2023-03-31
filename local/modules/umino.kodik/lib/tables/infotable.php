<?php


namespace Umino\Kodik\Tables;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main;

/**
 * Class InfoTable
 *
 * Fields:
 * <ul>
 * <li> ID int
 * <li> XML_ID string
 * <li> ACTIVE boolean
 * <li> DATE_CREATE datetime
 * <li> DATE_UPDATE datetime
 * <li> TYPE string
 * <li> TITLE string
 * <li> TITLE_ORIGINAL string
 * <li> TITLE_OTHER array
 * <li> YEAR string
 * <li> KODIK_ID string
 * <li> SHIKIMORI_ID string
 * <li> WORLDART_ID string
 * <li> KINOPOISK_ID string
 * <li> IMDB_ID string
 * <li> IBLOCK_ELEMENT_ID int
 * <li> IBLOCK_ELEMENT array
 * </ul>
 *
 * @package Umino\Kodik\Tables
 **/
class InfoTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'umino_kodik_info';
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
            new Main\Entity\StringField('TYPE'),
            new Main\Entity\StringField('TITLE', [
                'required' => true,
            ]),
            new Main\Entity\StringField('TITLE_ORIGINAL'),
            new Main\Entity\TextField('TITLE_OTHER', [
                'serialized' => true,
            ]),
            new Main\Entity\StringField('YEAR'),

            new Main\Entity\StringField('KODIK_ID'),
            new Main\Entity\StringField('SHIKIMORI_ID'),
            new Main\Entity\StringField('WORLDART_LINK'),
            new Main\Entity\StringField('KINOPOISK_ID'),
            new Main\Entity\StringField('IMDB_ID'),

            new Main\Entity\IntegerField('IBLOCK_ELEMENT_ID'),

            new Main\Entity\ReferenceField(
                'IBLOCK_ELEMENT',
                ElementTable::class,
                ['=this.IBLOCK_ELEMENT_ID' => 'ref.ID'],
                ['join_type' => 'LEFT']
            ),
        ];
    }
}