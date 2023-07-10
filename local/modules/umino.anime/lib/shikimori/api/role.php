<?php


namespace Umino\Anime\Shikimori\API;


use Umino\Anime\Request;

class AnimeRole extends Video
{
    protected static string $type = 'animes';
    protected static string $typeChild = 'roles';

    protected static array $cache = [];

    public function get(): array
    {
        $items = static::response([$this->getParentId(), static::$typeChild]);
        $items = static::rebase($items);

        foreach ($items as $item) {
            if ($item['id'] != $this->getId()) continue;
            return $item;
        }

        return [];
    }

    public static function getAsync(): array
    {
        $urls = [];
        foreach (static::getIds() as $object) {
            $urls[$object->getId()] = Request::buildURL(array_merge([static::$url, 'api', static::$type, $object->getParentId(), static::$typeChild]));
        }

        $response = Request::getResponseAsync($urls);
        foreach ($response as &$items) {
            $items = static::rebase($items);
        }

        return $response;
    }

    public static function rebase(array $items)
    {
        $cacheKey = md5(serialize($items));

        $results = static::$cache[$cacheKey];

        if ($results) return $results;

        foreach ($items as $item) {
            foreach ($item['roles'] as $key => $role) {
                if ($item['character']) {
                    $class = Character::class;
                    $person = $item['character']['id'];
                } else {
                    $class = Character::class;
                    $person = $item['person']['id'];
                }

                $results[] = [
                    'id' => md5(serialize([$class, $role, $person])),
                    'name' => $item['roles_russian'][$key] ?: $role,
                    'name_origin' => $role,
                    'person' => $person,
                    'class' => $class,
                ];
            }
        }

        return static::$cache[$cacheKey] = $results;
    }
}