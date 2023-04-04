<?php

/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Config\Option;

Loc::loadMessages(__FILE__);

// получаем идентификатор модуля
$request = HttpApplication::getInstance()->getContext()->getRequest();
$module_id = htmlspecialchars($request['mid'] != '' ? $request['mid'] : $request['id']);

// подключаем наш модуль
Loader::includeModule($module_id);

CModule::IncludeModule('iblock');

$iblockList = \Bitrix\Iblock\IblockTable::getList([
    'select' => ['ID','CODE','NAME'],
])->fetchAll();
foreach ($iblockList as $key => $iblock) {
    unset($iblockList[$key]);
    $iblockList[$iblock['ID']] = $iblock['NAME'] . ' [' . $iblock['CODE'] . ']';
}
$iblockList[0] = 'Не выбрано';
ksort($iblockList);


// Параметры модуля со значениями по умолчанию
$aTabs = [

    [ // Основные настройки
        'DIV'     => 'edit1',
        'TAB'     => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_GENERAL'),
        'TITLE'   => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_GENERAL'),
        'OPTIONS' => [
            [
                'api_token',
                Loc::getMessage('UMINO_ANIME_OPTIONS_API_TOKEN'),
                '2dada2cef52fa5c7f93ba82a5ee32e39',
                ['text', 32]
            ],
            [
                'api_url',
                Loc::getMessage('UMINO_ANIME_OPTIONS_API_URL'),
                'https://kodikapi.com/',
                ['text', 32]
            ],
            [
                'api_limit',
                Loc::getMessage('UMINO_ANIME_OPTIONS_API_LIMIT'),
                100,
                ['text', 5]
            ],
        ]
    ],

    [ // Настройки заполнения
        'DIV'     => 'edit2',
        'TAB'     => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_FILLING'),
        'TITLE'   => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_FILLING'),
        'OPTIONS' => [
            [
                'fill_iblock_id',
                Loc::getMessage('UMINO_ANIME_OPTIONS_IBLOCK_ELEMENTS'),
                '0',
                [
                    'selectbox',
                    $iblockList
                ]
            ],
            [
                'fill_elements_count',
                Loc::getMessage('UMINO_ANIME_OPTIONS_ELEMENTS_COUNT'),
                '5',
                ['text', 5]
            ],
        ]
    ],

    [ // Логи
        'DIV'     => 'edit3',
        'TAB'     => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_LOGS'),
        'TITLE'   => Loc::getMessage('UMINO_ANIME_OPTIONS_TAB_LOGS'),
        'OPTIONS' => [
            [
                'logs_show_count',
                Loc::getMessage('UMINO_ANIME_OPTIONS_LOGS_SHOW_COUNT'),
                '5',
                ['text', 5]
            ]
        ]
    ],
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

            if ($aTab['DIV'] === 'edit3'):?>

                <?
//                    \Umino\Anime\Tables\LogTable::getList([
//                            'order' => []
//                    ])
                ?>

            <?endif;
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

    foreach ($aTabs as $aTab) { // цикл по вкладкам
        foreach ($aTab['OPTIONS'] as $arOption) {
            if (!is_array($arOption)) { // если это название секции
                continue;
            }
            if ($arOption['note']) { // если это примечание
                continue;
            }
            if ($request['apply']) { // сохраняем введенные настройки
                $optionValue = $request->getPost($arOption[0]);
                if ($arOption[0] == 'api_limit') {
                    $optionValue = (int) $optionValue;
                    if (empty($optionValue) || $optionValue > 100) {
                        $optionValue = 100;
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
                if ($arOption[0] == 'fill_elements_count') {
                    $optionValue = (int) $optionValue;
                    if (empty($optionValue) || $optionValue > 50) {
                        $optionValue = 50;
                    } elseif ($optionValue < 1) {
                        $optionValue = 1;
                    }
                }
                Option::set($module_id, $arOption[0], is_array($optionValue) ? implode(',', $optionValue) : $optionValue);
            } else if ($request['default']) { // устанавливаем по умолчанию
                Option::set($module_id, $arOption[0], $arOption[2]);
            }
        }
    }

    LocalRedirect($APPLICATION->getCurPage().'?mid='.$module_id.'&lang='.LANGUAGE_ID);

}
?>