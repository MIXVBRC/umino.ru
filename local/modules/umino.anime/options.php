<?php

/** @global CMain $APPLICATION */

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;
use Umino\Anime\Core;
use Umino\Anime\Tables\LogTable;

Loc::loadMessages(__FILE__);

// получаем идентификатор модуля
$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialchars($request['mid'] != '' ? $request['mid'] : $request['id']);

// подключаем наш модуль
Loader::includeModule($module_id);

CModule::IncludeModule('iblock');
CModule::IncludeModule('highloadblock');

$iblocks[0] = 'Не выбрано';
foreach (IblockTable::getList(['select'=>['ID','CODE','NAME']])->fetchAll() as $item) {
    $iblocks[$item['ID']] = $item['NAME'] . ' [' . $item['CODE'] . ']';
}

$hl[0] = 'Не выбрано';
foreach (HighloadBlockTable::getList()->fetchAll() as $item) {
    $hl[$item['ID']] = $item['NAME'] . ' [' . $item['TABLE_NAME'] . ']';
}

$logsHTML = '';
$logs = LogTable::getList([
    'limit' => Core::getLogsShowCount(),
    'order' => ['DATE_CREATE' => 'DESC'],
    'select' => [
        'DATE_CREATE',
        'FILE',
        'LINE',
        'MESSAGE',
    ],
])->fetchAll();
if ($logs) {
    $logsHTML .= '<table class="umino_log">';
    $logsHTML .= '<thead>';
    $logsHTML .= '<tr>';
    $logsHTML .= '<td>Дата создания</td>';
    $logsHTML .= '<td>Файл</td>';
    $logsHTML .= '<td>Строка</td>';
    $logsHTML .= '<td>Данные</td>';
    $logsHTML .= '</tr>';
    $logsHTML .= '</thead>';
    $logsHTML .= '<tbody>';
    foreach ($logs as $log) {
        $logsHTML .= '<tr>';
        foreach ($log as $value) {
            $logsHTML .= '<td>';
            if (is_array($value)) {
                $logsHTML .= '<pre style="text-align:left;max-height:300px;max-width:1000px;overflow:auto;margin:unset;">';
                $logsHTML .= print_r($value, true);
                $logsHTML .= '</pre>';
            } else {
                $logsHTML .= $value;
            }
            $logsHTML .= '</td>';
        }
        $logsHTML .= '</tr>';
    }
    $logsHTML .= '</tbody>';
    $logsHTML .= '<style>
        .umino_log {
            padding: 15px;
            background-color: #fff;
            border: 1px solid #dce7ed;
            width: 100%;
        }
        .umino_log thead {
            background-color: #dedede;
        }
        .umino_log td {
            text-align: center;
            padding: 0 10px;
        }
        .umino_log tbody td {
            border: 1px solid #dedede;
        }
    </style>';
    $logsHTML .= '</table>';
    $logsHTML .= '</div>';
}

$options = [
    'note_1' => [
        'note' => Loc::getMessage('UMINO_ANIME_OPTIONS_API_NOTE_1', ['#DIR#' => __DIR__]),
    ],
    'api_token' => [
        'api_token',
        Loc::getMessage('UMINO_ANIME_OPTIONS_API_TOKEN'),
        '2dada2cef52fa5c7f93ba82a5ee32e39',
        ['text', 32]
    ],
    'api_url' => [
        'api_url',
        Loc::getMessage('UMINO_ANIME_OPTIONS_API_URL'),
        'https://kodikapi.com/',
        ['text', 32]
    ],
    'api_full_import' => [
        'api_full_import',
        Loc::getMessage('UMINO_ANIME_OPTIONS_API_FULL_IMPORT'),
        'Y',
        ['checkbox']
    ],
    'api_date_update_import' => [
        'api_date_update_import',
        Loc::getMessage('UMINO_ANIME_OPTIONS_API_DATE_UPDATE_IMPORT'),
        'Y',
        ['checkbox']
    ],
    'note_4' => [
        'note' => Loc::getMessage('UMINO_ANIME_OPTIONS_API_NOTE_4', ['#DATE_UPDATE#' => Core::getAPILastDateUpdate()]),
    ],
    'api_limit' => [
        'api_limit',
        Loc::getMessage('UMINO_ANIME_OPTIONS_API_LIMIT'),
        100,
        ['text', 5]
    ],
    'api_limit_page' => [
        'api_limit_page',
        Loc::getMessage('UMINO_ANIME_OPTIONS_API_LIMIT_PAGE'),
        1,
        ['text', 5]
    ],
    'api_fill' => [
        'api_fill',
        Loc::getMessage('UMINO_ANIME_OPTIONS_API_FILL'),
        'N',
        ['checkbox']
    ],
    'api_save_next_page' => [
        'api_save_next_page',
        Loc::getMessage('UMINO_ANIME_OPTIONS_API_SAVE_NEXT_PAGE'),
        'Y',
        ['checkbox']
    ],
    'note_3' => [
        'note' => Loc::getMessage('UMINO_ANIME_OPTIONS_API_NOTE_3')
    ],
    'note_2' => [
        'note' => Loc::getMessage('UMINO_ANIME_OPTIONS_API_NOTE_2', ['#NEXT_PAGE#' => Core::getAPINextPage()]),
    ],
];

if (!Core::getAPISaveNextPage()) {
    unset($options['note_2']);
    unset($options['note_3']);
}
if (!Core::getAPIDateUpdateImport()) {
    unset($options['note_4']);
}

$tab_api = [ // Основные настройки
    'DIV'     => 'api',
    'TAB'     => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_GENERAL'),
    'TITLE'   => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_GENERAL'),
    'OPTIONS' => $options
];

$options = [
    'anime_iblock_id' => [
        'anime_iblock_id',
        Loc::getMessage('UMINO_ANIME_OPTIONS_FILLING_ANIME_IBLOCK_ID'),
        Core::getIblockId('anime'),
        [
            'selectbox',
            $iblocks
        ]
    ],
    'translations_iblock_id' => [
        'translations_iblock_id',
        Loc::getMessage('UMINO_ANIME_OPTIONS_FILLING_TRANSLATIONS_IBLOCK_ID'),
        Core::getIblockId('translations'),
        [
            'selectbox',
            $iblocks
        ]
    ],
    'studios_iblock_id' => [
        'studios_iblock_id',
        Loc::getMessage('UMINO_ANIME_OPTIONS_FILLING_STUDIOS_IBLOCK_ID'),
        Core::getIblockId('studios'),
        [
            'selectbox',
            $iblocks
        ]
    ],
    'persons_iblock_id' => [
        'persons_iblock_id',
        Loc::getMessage('UMINO_ANIME_OPTIONS_FILLING_PERSONS_IBLOCK_ID'),
        Core::getIblockId('persons'),
        [
            'selectbox',
            $iblocks
        ]
    ],
    'genres_iblock_id' => [
        'genres_iblock_id',
        Loc::getMessage('UMINO_ANIME_OPTIONS_FILLING_GENRES_IBLOCK_ID'),
        Core::getIblockId('genres'),
        [
            'selectbox',
            $iblocks
        ]
    ],
];

$tab_filling = [ // Настройки заполнения
    'DIV'     => 'filling',
    'TAB'     => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_FILLING'),
    'TITLE'   => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_FILLING'),
    'OPTIONS' => $options,
];

$options = [
    'logs_show_count' => [
        'logs_show_count',
        Loc::getMessage('UMINO_ANIME_OPTIONS_LOGS_SHOW_COUNT'),
        '5',
        ['text', 5]
    ],
    'logs_clear' => [
        'logs_clear',
        Loc::getMessage('UMINO_ANIME_OPTIONS_LOGS_CLEAR'),
        'N',
        ['checkbox']
    ],
    'note_1' => [
        'note' => $logsHTML ?: Loc::getMessage('UMINO_ANIME_OPTIONS_LOGS_NOTE_1'),
    ],
];

foreach ($options as $name => $option) {
    switch ($name) {
        case 'logs_clear':
            if ($logs) break;
            unset($options[$name]);
    }
}

$tab_logs = [ // Логи
    'DIV'     => 'logs',
    'TAB'     => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_LOGS'),
    'TITLE'   => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_LOGS'),
    'OPTIONS' => $options,
];

// Параметры модуля со значениями по умолчанию
$aTabs = [
    $tab_api,
    $tab_filling,
    $tab_logs,
];

// Создаем форму для редактирвания параметров модуля
$tabControl = new CAdminTabControl(
    'tabControl',
    $aTabs
);

$tabControl->begin();
?>
    <form action="<?= $APPLICATION->getCurPage(); ?>?mid=<?=$module_id; ?>&lang=<?= LANGUAGE_ID; ?>" method="post">
        <?= bitrix_sessid_post(); ?>
        <?php
        foreach ($aTabs as $aTab) { // цикл по вкладкам
            if ($aTab['OPTIONS']) {
                $tabControl->beginNextTab();
                __AdmSettingsDrawList($module_id, $aTab['OPTIONS']);
            }
        }
        $tabControl->buttons();
        ?>
        <input type="submit" name="apply" value="<?= Loc::GetMessage('UMINO_ANIME_OPTIONS_INPUT_APPLY'); ?>" class="adm-btn-save" />
        <input type="submit" name="default" value="<?= Loc::GetMessage('UMINO_ANIME_OPTIONS_INPUT_DEFAULT'); ?>" />
    </form>
<?php
$tabControl->end();

// Обрабатываем данные после отправки формы
if ($request->isPost() && check_bitrix_sessid()) {

    // цикл по вкладкам
    foreach ($aTabs as $aTab) {
        foreach ($aTab['OPTIONS'] as $arOption) {

            // если это название секции
            if (!is_array($arOption)) {
                continue;
            }

            // если это примечание
            if ($arOption['note']) {
                continue;
            }

            // сохраняем введенные настройки
            if ($request['apply']) {

                $optionValue = $request->getPost($arOption[0]);

                if (in_array($arOption[0],['api_next_page','note'])) continue;

                if (empty($arOption[0])) continue;

                if ($arOption[0] == 'api_limit') {
                    $optionValue = (int) $optionValue;
                    if ($optionValue > 100) {
                        $optionValue = 100;
                    } elseif ($optionValue < 1) {
                        $optionValue = 1;
                    }
                    if ($optionValue !== Core::getAPILimit()) {
                        Core::setAPINextPage('');
                    }
                }

                if ($arOption[0] == 'api_limit_page') {
                    $optionValue = (int) $optionValue;
                    if ($optionValue > 10) {
                        $optionValue = 10;
                    } elseif ($optionValue < 1) {
                        $optionValue = 1;
                    }
                }

                if ($arOption[0] == 'fill_iblock_id') {
                    $optionValue = (int) $optionValue;
                    if (empty($optionValue)) {
                        $optionValue = 0;
                    }
                }

                if (in_array($arOption[0], ['api_fill', 'api_save_next_page', 'api_full_import', 'api_date_update_import'])) {
                    $optionValue = $optionValue? : 'N';
                }

                if ($arOption[0] == 'logs_clear') {
                    if ($optionValue === 'Y') {
                        $connection = Application::getConnection();
                        $connection->truncateTable(LogTable::getTableName());
                        $optionValue = 'N';
                    }
                    continue;
                }

                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(',', $optionValue) : $optionValue);

            // устанавливаем по умолчанию
            } else if ($request['default']) {

                Option::set($module_id, $arOption[0], $arOption[2]);

            }
        }
    }

    LocalRedirect($APPLICATION->getCurPage().'?mid='.$module_id.'&lang='.LANGUAGE_ID);

}
?>