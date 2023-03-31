<?php


namespace Umino\Kodik;


use COption;
use Umino\Kodik\Parser\ParserShikimori;
use Umino\Kodik\Parser\ParserWorldArt;

class Core
{
    public static function getAPILimit(): int
    {
        return (int) COption::GetOptionString('umino.kodik', 'api_limit');
    }

    public static function getAPIToken(): string
    {
        return COption::GetOptionString('umino.kodik', 'api_token');
    }

    public static function getAPIUrl(): string
    {
        return COption::GetOptionString('umino.kodik', 'api_url');
    }

    public static function getIBlock(): int
    {
        return COption::GetOptionString('umino.kodik', 'iblock_video_id');
    }
}