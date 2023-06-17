<?php


namespace Umino\Anime\Tables;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main;
use Bitrix\Main\ORM\Fields\Relations\Reference;

/**
 * Class EpisodesTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE bool optional default "Y"
 * <li> NAME string
 * <li> XML_ID string
 * <li> SERIAL_XML_ID string
 * <li> TRANSLATION_XML_ID string
 * <li> SEASON int optional
 * <li> ANIME_LINK string optional
 * <li> SEASON_LINK string optional
 * <li> EPISODES string optional
 * <li> EPISODES_COUNT int optional
 * <li> TYPE string optional
 * <li> QUALITY string optional
 * <li> KODIK_TYPE string optional
 * <li> KODIK_ID string optional
 * <li> DATE_CREATE datetime optional
 * <li> DATE_UPDATE datetime optional
 * <li>
 * <li> SERIAL_ELEMENT reference
 * <li> TRANSLATION_ELEMENT reference
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
            new Main\Entity\StringField('ACTIVE', [
                'default_value' => 'Y',
            ]),
            new Main\Entity\StringField('NAME', [
                'required' => true,
            ]),
            new Main\Entity\StringField('XML_ID', [
                'required' => true,
            ]),
            new Main\Entity\StringField('SERIAL_XML_ID', [
                'required' => true,
            ]),
            new Main\Entity\StringField('TRANSLATION_XML_ID', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('SEASON'),
            new Main\Entity\StringField('ANIME_LINK'),
            new Main\Entity\StringField('SEASON_LINK'),
            new Main\Entity\StringField('EPISODES', [
                'serialized' => true,
            ]),
            new Main\Entity\IntegerField('EPISODES_COUNT'),
            new Main\Entity\StringField('TYPE'),
            new Main\Entity\StringField('QUALITY'),
            new Main\Entity\StringField('KODIK_TYPE'),
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

            'SERIAL' => new Reference(
                'SERIAL_ELEMENT',
                ElementTable::class,
                ['=this.SERIAL' => 'ref.XML_ID'],
                ['join_type' => 'INNER']
            ),
            'TRANSLATION' => new Reference(
                'TRANSLATION_ELEMENT',
                ElementTable::class,
                ['=this.TRANSLATION' => 'ref.XML_ID'],
                ['join_type' => 'INNER']
            ),
        ];
    }
}