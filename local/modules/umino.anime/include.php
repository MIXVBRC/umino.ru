<?php

use Bitrix\Main\EventManager;
use Umino\Anime\Core;

$eventManager = EventManager::getInstance();

$events = [
    [
        'module' => Core::getModuleId(),
        'event' => 'OnImportStart',
        'callback' => ['Umino\Anime\Events\Import', 'OnImportStart'],
    ],
    [
        'module' => Core::getModuleId(),
        'event' => 'OnImportFinish',
        'callback' => ['Umino\Anime\Events\Import', 'OnImportFinish'],
    ],
    [
        'module' => Core::getModuleId(),
        'event' => 'OnBeforeImportUpdate',
        'callback' => ['Umino\Anime\Events\Import', 'OnBeforeImportUpdate'],
    ],
    [
        'module' => Core::getModuleId(),
        'event' => 'OnAfterImportUpdate',
        'callback' => ['Umino\Anime\Events\Import', 'OnAfterImportUpdate'],
    ],
    [
        'module' => Core::getModuleId(),
        'event' => 'OnImportEpisodesAdd',
        'callback' => ['Umino\Anime\Events\Import', 'OnImportEpisodesAdd'],
    ],
];

foreach ($events as $event) {
    $eventManager->addEventHandler(
        $event['module'],
        $event['event'],
        $event['callback'],
    );
}