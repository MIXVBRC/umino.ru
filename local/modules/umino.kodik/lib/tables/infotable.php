<?php


namespace Umino\Kodik\Tables;

use Bitrix\Main;

/* MySQL
TODO: CREATE TABLE IF NOT EXISTS `umino_kodik_info` (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    XML_ID VARCHAR(255) NOT NULL,
    ACTIVE BOOLEAN DEFAULT 1,
    DATE_CREATE DATETIME DEFAULT CURRENT_TIMESTAMP,
    DATE_UPDATE DATETIME DEFAULT CURRENT_TIMESTAMP,
    TYPE VARCHAR(255),
    TITLE VARCHAR(255) NOT NULL,
    TITLE_ORIGINAL VARCHAR(255),
    TITLE_OTHER VARCHAR(255),
    YEAR VARCHAR(255),
    IMAGE VARCHAR(255),
    DESCRIPTION TEXT,
    KODIK_ID VARCHAR(255),
    SHIKIMORI_ID VARCHAR(255),
    WORLDART_ID VARCHAR(255),
    KINOPOISK_ID VARCHAR(255),
    IMDB_ID VARCHAR(255));
*/

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
 * <li> IMAGE string
 * <li> DESCRIPTION string
 * <li> KODIK_ID string
 * <li> SHIKIMORI_ID string
 * <li> WORLDART_ID string
 * <li> KINOPOISK_ID string
 * <li> IMDB_ID string
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
            new Main\Entity\StringField('TITLE_OTHER'),
            new Main\Entity\StringField('YEAR'),
            new Main\Entity\StringField('IMAGE'),
            new Main\Entity\TextField('DESCRIPTION'),
            new Main\Entity\StringField('KODIK_ID'),
            new Main\Entity\StringField('SHIKIMORI_ID'),
            new Main\Entity\StringField('WORLDART_ID'),
            new Main\Entity\StringField('KINOPOISK_ID'),
            new Main\Entity\StringField('IMDB_ID'),
        ];
    }
}