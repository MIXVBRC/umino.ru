<?php

use Bitrix\Iblock\ElementPropertyTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Join;
use Bitrix\Main\ORM\Query\Query;
use Umino\Anime\API;
use Umino\Anime\Core;
use Umino\Anime\Import;
use Umino\Anime\Lock;
use Umino\Anime\Logger;
use Umino\Anime\Tables\EpisodesTable;

require_once 'config.php';

Loader::includeModule('umino.anime');

$lock = new Lock('import');

if (!$lock->lock()) die;

try {

    \Umino\Anime\Shikimori\Manager::load(500);

    /*
    // Полный импорт
    if (Core::getAPIFullImport()) {
        $result = Core::getAPISaveNextPage() ? API::next() : [];
        $import = new Import();
        $import->start($result ? : API::getFullAnime());
    }

    // Обновление уже существующих
    if (Core::getAPIDateUpdateImport()) {
        $limit = Core::getAPILimit();
        $import = new Import();
        if ($result = API::lastUpdate(API::getFullAnime())) {
            $import->start($result);
        }
    }
    */

    /*
    // Получение отсутствующих эпизодов для сериалов
    $entitySPUser = ElementTable::getEntity();
    $query = new Query($entitySPUser);
    $query
        ->setFilter([
            'IBLOCK_ID' => Core::getAnimeIBlockID(),
            'EPISODES.ID' => false,
            'PROPERTY.CODE' => API::getSearchList(),
        ])
        ->setLimit(Core::getAPILimit())
        ->setSelect([
            'XML_ID',
            'PROPERTY_CODE' => 'PROPERTY.CODE',
            'PROPERTY_VALUE' => 'ELEMENT_PROPERTY.VALUE',
        ])
        ->registerRuntimeField('EPISODES', [
            'data_type' => EpisodesTable::class,
            'reference' => Join::on('this.XML_ID', 'ref.SERIAL'),
            'join_type' => 'left',
        ])
        ->registerRuntimeField('PROPERTY', [
            'data_type' => PropertyTable::class,
            'reference' => Join::on('ref.IBLOCK_ID', 'this.IBLOCK_ID'),
            'join_type' => 'inner',
        ])
        ->registerRuntimeField('ELEMENT_PROPERTY', [
            'data_type' => ElementPropertyTable::class,
            'reference' => Join::on('ref.IBLOCK_PROPERTY_ID', 'this.PROPERTY.ID')
                ->whereColumn('ref.IBLOCK_ELEMENT_ID', 'this.ID')
                ->whereNotNull('ref.VALUE'),
            'join_type' => 'inner',
        ]);

    $items = [];
    foreach ($query->fetchAll() as $item) {
        $items[$item['XML_ID']][strtolower($item['PROPERTY_CODE'])] = $item['PROPERTY_VALUE'];
    }

    $params = [];
    foreach ($items as $item) {
        foreach ($item as $name => $value) {
            if (empty($value)) continue;
            $params[] = [$name => $value];
            break;
        }
    }

    $import = new Import();
    $import->start(API::asyncSearch($params));
    */

} catch (\Exception $exception) {
    Logger::log($exception->getMessage());
}
