<?php


namespace Umino\Anime;


use COption;

class Core
{
    protected static $module = 'umino.anime';

    public static function getAPILimit(): int
    {
        return (int) COption::GetOptionString(self::$module, 'api_limit');
    }

    public static function getAPIToken(): string
    {
        return COption::GetOptionString(self::$module, 'api_token');
    }

    public static function getAPIUrl(): string
    {
        return COption::GetOptionString(self::$module, 'api_url');
    }

    public static function getIBlock(): int
    {
        return (int) COption::GetOptionString(self::$module, 'fill_iblock_id');
    }

    public static function getFillElementCount(): int
    {
        return (int) COption::GetOptionString(self::$module, 'fill_elements_count');
    }

    public static function getLogsShowCount(): int
    {
        return (int) COption::GetOptionString(self::$module, 'logs_show_count');
    }

    public static function keysToUpperCase(array &$itemList)
    {
        $itemList = array_change_key_case($itemList, CASE_UPPER);
        foreach ($itemList as &$item) {
            if (!is_array($item)) continue;
            self::keysToUpperCase($item);
        }
    }
}