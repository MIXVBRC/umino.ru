<?php


namespace Umino\Anime\Shikimori\API;


use Umino\Anime\Request;

class Video extends Entity
{
    protected static string $type = 'animes';
    protected static string $typeChild = 'videos';

    protected string $parent_id;

    public function __construct(string $id, string $parent_id)
    {
        parent::__construct($id);
        $this->parent_id = $parent_id;
    }

    public function getParentId(): string
    {
        return $this->parent_id;
    }

    public function get(): array
    {
        $items = static::response([$this->getParentId(), static::$typeChild]);

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

        return Request::getResponseAsync($urls);
    }
}