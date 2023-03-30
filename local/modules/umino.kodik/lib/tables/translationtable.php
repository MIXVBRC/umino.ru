<?php


namespace Umino\Kodik\Tables;

use Bitrix\Main;

/* MySQL
TODO: CREATE TABLE IF NOT EXISTS `umino_kodik_data` (
    ID INT PRIMARY KEY AUTO_INCREMENT,
    XML_ID VARCHAR(255) NOT NULL,
    ACTIVE BOOLEAN DEFAULT 1,
    DATE_CREATE DATETIME DEFAULT CURRENT_TIMESTAMP,
    DATE_UPDATE DATETIME DEFAULT CURRENT_TIMESTAMP,
    TITLE VARCHAR(255) NOT NULL,
    KODIK_ID INT NOT NULL,
    TYPE VARCHAR(255) NOT NULL);
*/

/**
 * Class TranslationTable
 *
 * Fields:
 * <ul>
 * <li> ID int
 * <li> XML_ID string
 * <li> ACTIVE boolean
 * <li> DATE_CREATE datetime
 * <li> DATE_UPDATE datetime
 * <li> KODIK_ID int
 * <li> TYPE string
 * </ul>
 *
 * @package Umino\Kodik\Tables
 **/
class TranslationTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'umino_kodik_translation';
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
            new Main\Entity\StringField('TITLE', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('KODIK_ID', [
                'required' => true,
            ]),
            new Main\Entity\StringField('TYPE', [
                'required' => true,
            ]),
        ];
    }
}