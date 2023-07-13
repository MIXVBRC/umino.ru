<?php


namespace Umino\Anime\Shikimori\API;


use Umino\Anime\Request;

class Franchise extends Video
{
    protected static string $type = 'animes';
    protected static string $typeChild = 'franchise';

    public function get(): array
    {
        $response = static::response([$this->getParentId(), static::$typeChild]);

        $response['name'] = $this->getId();

        return $response;
    }

    public static function getAsync(): array
    {
        $urls = [];
        foreach (static::getIds() as $object) {
            $urls[$object->getId()] = Request::buildURL(array_merge([static::$url, 'api', static::$type, $object->getParentId(), static::$typeChild]));
        }

        $response = Request::getResponseAsync($urls);
        foreach ($response as $id => &$item) {
            $item['name'] = $id;
        }

        return $response;
    }
}