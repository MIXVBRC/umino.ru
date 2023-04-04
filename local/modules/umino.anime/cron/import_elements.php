<?php

use Bitrix\Main\Loader;
use Umino\Anime\Import;
use Umino\Anime\Logger;

require_once 'config.php';

Loader::includeModule('umino.anime');

try {
    Import::start(true);
} catch (Exception $exception) {
    Logger::log($exception);
}

