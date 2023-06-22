<?php

namespace Umino\Anime;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Event;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\Type\DateTime;
use CDatabase;
use COption;

class Core
{
    /** ID модуля */
    protected static string $module_id = 'umino.anime';

    /** GET */

    public static function getModuleId(): string
    {
        return self::$module_id;
    }

    /** Основные настройки */

    /** Kodik API токен */
    public static function getAPIToken(): string
    {
        return (string) COption::GetOptionString(self::getModuleId(), 'api_token');
    }

    /** Kodik API ссылка */
    public static function getAPIUrl(): string
    {
        return (string) COption::GetOptionString(self::getModuleId(), 'api_url');
    }

    /** Полный импорт */
    public static function getAPIFullImport(): bool
    {
        return COption::GetOptionString(self::getModuleId(), 'api_full_import') === 'Y';
    }

    public static function setAPIFullImport(bool $bool): void
    {
        self::setOption('api_full_import', self::getBitrixBool($bool));
    }

    /** Импорт по дате обновления */
    public static function getAPIDateUpdateImport(): bool
    {
        return COption::GetOptionString(self::getModuleId(), 'api_date_update_import') === 'Y';
    }

    public static function setAPIDateUpdateImport(bool $bool): void
    {
        self::setOption('api_date_update_import', self::getBitrixBool($bool));
    }

    /** Количество результатов в запросе */
    public static function getAPILimit(): int
    {
        return (int) COption::GetOptionInt(self::getModuleId(), 'api_limit');
    }

    public static function setAPILimit(int $limit)
    {
        $limit = $limit < 100 ? $limit : 100;
        $limit = $limit > 0 ? $limit : 1;
        self::setOption('api_limit', $limit);
    }

    /** Количество результатов в запросе */
    public static function getAPILimitPage(): int
    {
        return (int) COption::GetOptionInt(self::getModuleId(), 'api_limit_page');
    }

    public static function setAPILimitPage(int $limit)
    {
        $limit = $limit < 10 ? $limit : 10;
        $limit = $limit > 0 ? $limit : 1;
        self::setOption('api_limit_page', $limit);
    }

    /** Заполнять результаты запроса поиском? (увеличение нагрузки) */
    public static function getAPIFill(): bool
    {
        return COption::GetOptionString(self::getModuleId(), 'api_fill') === 'Y';
    }

    /** Сохранять следующую страницу пагинации? */
    public static function getAPISaveNextPage(): bool
    {
        return COption::GetOptionString(self::getModuleId(), 'api_save_next_page') === 'Y';
    }

    public static function setAPISaveNextPage(bool $bool): void
    {
        self::setOption('api_save_next_page', self::getBitrixBool($bool));
    }

    /** Получить последнюю дату обновления */
    public static function getAPILastDateUpdate(): string
    {
        return COption::GetOptionString(self::getModuleId(), 'api_last_date_update');
    }

    public static function setAPILastDateUpdate(string $date)
    {
        self::setOption('api_last_date_update', self::getDate($date));
    }

    /** Настройки наполнения */

    /** Инфоблоки для заполнения */

    public static function getAnimeIBlockID(): int
    {
        return (int) COption::GetOptionInt(self::getModuleId(), 'anime_iblock_id');
    }

    public static function getTranslationsIBlockID(): int
    {
        return (int) COption::GetOptionInt(self::getModuleId(), 'translations_iblock_id');
    }

    public static function getStudiosIBlockID(): int
    {
        return (int) COption::GetOptionInt(self::getModuleId(), 'studios_iblock_id');
    }

    public static function getPeopleIBlockID(): int
    {
        return (int) COption::GetOptionInt(self::getModuleId(), 'people_iblock_id');
    }

    public static function getGenresIBlockID(): int
    {
        return (int) COption::GetOptionInt(self::getModuleId(), 'genres_iblock_id');
    }

    /** Логирование */

    /** Сколько логов выводить */
    public static function getLogsShowCount(): int
    {
        return (int) COption::GetOptionInt(self::getModuleId(), 'logs_show_count');
    }

    /** SET */

    public static function setOption(string $name, string $value): bool
    {
        if (is_numeric($value)) {
            return COption::SetOptionInt(self::getModuleId(), $name, $value);
        } else {
            return COption::SetOptionString(self::getModuleId(), $name, $value);
        }
    }

    /** Вспомогательные функции */

    /** Приводит ключи массива к UPPERCASE */
    public static function keysToUpperCase(array &$itemList)
    {
        $itemList = array_change_key_case($itemList, CASE_UPPER);
        foreach ($itemList as &$item) {
            if (!is_array($item)) continue;
            self::keysToUpperCase($item);
        }
    }

    /** Получает ID инфоблока по символьному коду */
    public static function getIblockId(string $code): int
    {
        if (!$id = IblockTable::getRow(['filter'=>['CODE' => $code],'select'=>['ID']])['ID']) {
            $id = 0;
        }
        return (int) $id;
    }

    /** Последняя ссылка */
    public static function getAPINextPage(): string
    {
        return (string) COption::GetOptionString(self::getModuleId(), 'api_next_page');
    }
    public static function setAPINextPage(string $link): bool
    {
        return COption::SetOptionString(self::getModuleId(), 'api_next_page', $link);
    }

    protected static function getBitrixBool(bool $bool): string
    {
        return $bool ? 'Y' : 'N';
    }

    /**
     * Получить HL-блок по ID
     *
     * @param int $id
     * @return DataManager
     */
    public static function getHLBlock(int $id): string
    {
        return HighloadBlockTable::compileEntity(
            HighloadBlockTable::getById($id)->fetch()
        )->getDataClass();
    }

    /**
     * Добавить событие
     *
     * @param string $name
     * @param array $params
     */
    public static function addEvent(string $name, array $params = [])
    {
        (new Event(self::getModuleId(), $name, $params))->send();
    }

    /**
     * @param string $date
     * @return DateTime|string
     */
    public static function getDate(string $date): string
    {
        return DateTime::createFromTimestamp(strtotime(str_replace(['Z','T'], ['',' '], $date)))->toString();
    }

    /**
     * Сверяет даты
     *
     * <ul>
     * <li>1 - A новее B старее
     * <li>0 - A и B одинаковые
     * <li>-1 - A старее B новее
     * </ul>
     *
     * @param string $data_A - сверяемая дата
     * @param string $data_B - с чем сверяем
     * @return int
     */
    public static function checkDate(string $data_A, string $data_B): int
    {
        return (new CDatabase)->CompareDates(
            $data_A,
            $data_B
        );
    }
}