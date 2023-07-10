<?php


namespace Umino\Anime\Shikimori\Tables;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Entity;
use Bitrix\Main\Type;
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
class LoadTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'umino_anime_shikimori_load';
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new Entity\IntegerField('SID', [
                'required' => true,
            ]),
            new Entity\StringField('CLASS', [
                'required' => true,
            ]),
            new Entity\BooleanField('IS_LOAD', [
                'default_value' => false,
            ]),
            new Entity\DatetimeField('DATE_CREATE', [
                'required' => true,
                'default_value' => new Type\DateTime()
            ]),
            new Entity\DatetimeField('DATE_UPDATE', [
                'required' => true,
                'default_value' => new Type\DateTime()
            ]),
        ];
    }
}