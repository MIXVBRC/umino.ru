<?php


namespace Umino\Anime\Shikimori\API;


use Umino\Anime\Kodik\API;

class Translation extends Genre
{
    protected static function response(array $components = [], array $params = []): array
    {
        return API::translations();
    }
}