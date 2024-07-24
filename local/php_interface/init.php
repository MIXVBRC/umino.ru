<?php

define('LOG_FILENAME', $_SERVER['DOCUMENT_ROOT'].'/local/logs/log.txt');

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/functions.php')) {
    require $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/include/functions.php';
}

CModule::IncludeModule('umino.anime');
