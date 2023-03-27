<?php

/**
 * @param $data
 * @param bool $var_dump
 * @param bool $die
 */
function pre($data, $var_dump = false, $die = false)
{
    $style = 'background-color: #fff; color:#000; padding: 10px; border-radius: 5px;';

    $trace = debug_backtrace();
    $file = str_replace($_SERVER["DOCUMENT_ROOT"], '', $trace[0]['file']);
    $arInfo = $file.':'.$trace[0]['line'];

    echo '<pre style="'.$style.'">';
    print_r($arInfo);
    echo '<br>';
    $var_dump ? var_dump($data) : print_r($data);
    echo '</pre>';

    if ($die) die;
}