<?php

/**
 * если запуск не из командной строки
 */
if (empty($_SERVER['SHELL'])) {
    die;
}

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('CHK_EVENT', true);
define('BX_NO_ACCELERATOR_RESET', true);
define("BX_CAT_CRON", true);

define('PHP_SCRIPT', true);

set_time_limit(0);

$_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__, 3);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';