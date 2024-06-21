<?php

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$menuList = [
    [
        'parent_menu' => 'global_menu_umino',
        'sort' => 10,
        'text' => 'Настройки',
        'title' => 'Настройки',
        'url' => 'umino.php',
        'icon' => 'sys_menu_icon',
        'items_id' => 'umino',
        'items' => [
            [
                'text' => 'Shikimori',
                'title' => 'Shikimori',
                'url' => 'umino_shikimori.php',
                'icon' => 'umino_shikimori_menu_icon',
            ],
            [
                'text' => 'Kodik',
                'title' => 'Kodik',
                'url' => 'umino_kodik.php',
                'icon' => 'umino_kodik_menu_icon',
            ],
        ]
    ],
];

return isset($menuList) ? $menuList : array();