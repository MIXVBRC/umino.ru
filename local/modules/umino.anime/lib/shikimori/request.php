<?php


namespace Umino\Anime\Shikimori;


use Umino\Anime\Core;

class Request extends \Umino\Anime\Request
{
    private static string $url = 'https://shikimori.one';
    private static string $api = '/api';

    public static function buildApiURL(array $components, array $params = []): string
    {
        $components = array_merge([self::$url, self::$api], $components);
        return parent::buildURL($components, $params);
    }

    public static function buildFileURL(array $components, array $params = []): string
    {
        $components = array_merge([self::$url], $components);
        return parent::buildURL($components, $params);
    }

    public function getResult(): array
    {
        $result = parent::getResult();
        Core::keysToUpperCase($result);
        return $result;
    }

    public static function getResponse(string $url, bool $isJson = true): ?array
    {
        $result = parent::getResponse($url, $isJson);
        Core::keysToUpperCase($result);
        return $result;
    }
}