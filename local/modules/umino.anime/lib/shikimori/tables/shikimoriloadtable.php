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
 * <li> SID string
 * <li> PARENT_SID string
 * <li> TYPE string
 * <li> PRIORITY int
 * <li> IS_LOAD boolean
 * <li> DATE_CREATE datetime optional
 * <li> DATE_UPDATE datetime optional
 * </ul>
 *
 * @package Umino\Kodik\Tables
 **/
class ShikimoriLoadTable extends Entity\DataManager
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
            new Entity\StringField('SID', [
                'required' => true,
            ]),
            new Entity\StringField('PARENT_SID'),
            new Entity\StringField('TYPE', [
                'required' => true,
            ]),
            new Entity\StringField('PRIORITY', [
                'required' => true,
                'default_value' => 100,
            ]),
            new Entity\BooleanField('IS_LOAD', [
                'default_value' => false,
            ]),
            new Entity\DatetimeField('DATE_CREATE', [
                'default_value' => new Type\DateTime()
            ]),
            new Entity\DatetimeField('DATE_UPDATE', [
                'default_value' => new Type\DateTime()
            ]),
        ];
    }
}