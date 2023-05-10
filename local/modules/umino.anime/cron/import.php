<?php

use Bitrix\Main\Loader;
use Umino\Anime\API;
use Umino\Anime\Import;
use Umino\Anime\Lock;
use Umino\Anime\Logger;

require_once 'config.php';

Loader::includeModule('umino.anime');

$lock = new Lock('import');

if (!$lock->lock()) die;

try {
    if (empty(API::next('anime-serial'))) {
        API::getAnimeSerials(1);
    }
    Import::start(true);
} catch (Exception $exception) {
    Logger::log($exception);
}
