<?php


namespace Umino\Anime\Shikimori\API;


class People extends Entity
{
    protected static string $type = 'people';

    public static function search(array $params = []): array
    {
        return static::response([], $params);
    }

    public function get(): array
    {
        $result = parent::get();
        $result['image'] = static::getBaseUrl($result['image']['original']);
        $result['url'] = static::getBaseUrl($result['url']);
        return $result;
    }
}