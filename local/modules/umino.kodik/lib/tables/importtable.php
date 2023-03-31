<?php


namespace Umino\Kodik\Tables;

use Bitrix\Main;
use Bitrix\Main\Type\DateTime;
use Umino\Kodik\Parser\Parser;

/**
 * Class ImportTable
 *
 * Fields:
 * <ul>
 * <li> ID int
 * <li> XML_ID string
 * <li> ACTIVE boolean
 * <li> DATE_CREATE datetime
 * <li> DATE_UPDATE datetime
 * <li> URL string
 * <li> TIME string
 * <li> TOTAL string
 * <li> PREV_PAGE string
 * <li> NEXT_PAGE string
 * <li> RESULTS string
 * </ul>
 *
 * @package Umino\Kodik\Tables
 **/
class ImportTable extends Main\Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName()
    {
        return 'umino_kodik_import';
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
            new Main\Entity\StringField('URL', [
                'required' => true,
            ]),
            new Main\Entity\StringField('TIME', [
                'required' => true,
            ]),
            new Main\Entity\IntegerField('TOTAL', [
                'required' => true,
            ]),
            new Main\Entity\StringField('PREV_PAGE'),
            new Main\Entity\StringField('NEXT_PAGE'),
            new Main\Entity\IntegerField('RESULTS_COUNT', [
                'required' => true,
            ]),
        ];
    }

    private static function remove()
    {
        $results = parent::getList([
            'filter' => [
                [
                    'LOGIC' => 'OR',
                    ['ACTIVE' => false],
                    ['<=DATE_CREATE' => (new DateTime())->add('-10 min')],
                ],
            ],
            'select' => ['ID'],
        ])->fetchAll();

        foreach ($results as $result) {
            self::delete($result['ID']);
        }
    }

    public static function getByXmlId(string $xml_id, bool $full = false)
    {
        $result = self::getList([
            'filter' => ['XML_ID' => $xml_id],
            'limit' => 1,
        ])->fetch();

        if ($result && $full) {
            $resultList = ImportResultsTable::getList([
                'filter' => ['IMPORT_ID' => $result['ID']]
            ])->fetchAll();

            $result['RESULTS'] = $resultList;
        }

        return $result;
    }

    public static function getList(array $parameters = array())
    {
        self::remove();

        return parent::getList($parameters); // TODO: Change the autogenerated stub
    }

    public static function add(array $data)
    {
        self::remove();

        $id = parent::add($data); // TODO: Change the autogenerated stub

        if ($id->isSuccess()) {

            foreach ($data['RESULTS'] as $item) {

                $item = array_change_key_case($item, CASE_UPPER);

                $xml_id = md5($item['ID']);

                if ($result = ImportResultsTable::getByXmlId($xml_id)) {
                    ImportResultsTable::update($result['ID'], $item);
                } else {
                    ImportResultsTable::add([
                        'XML_ID' => $xml_id,
                        'IMPORT_ID' => $id->getId(),
                        'RESULTS' => $item,
                    ]);
                }
            }
        }

        return $id;
    }

    public static function update($primary, array $data)
    {
        $data['DATE_UPDATE'] = new DateTime();
        return parent::update($primary, $data); // TODO: Change the autogenerated stub
    }

    public static function delete($primary)
    {
        $itemList = ImportResultsTable::getList([
            'filter' => ['IMPORT_ID' => $primary],
            'select' => ['ID'],
        ])->fetchAll();

        foreach ($itemList as $item) {
            ImportResultsTable::delete($item['ID']);
        }

        return parent::delete($primary); // TODO: Change the autogenerated stub
    }
}