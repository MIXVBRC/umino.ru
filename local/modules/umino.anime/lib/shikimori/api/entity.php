<?php


namespace Umino\Anime\Shikimori\API;


use Umino\Anime\Request;


class Entity
{
    protected static string $url = 'https://shikimori.one';

    protected static string $type = '';

    protected static array $ids;

    protected string $id;

    public function __construct(string $id)
    {
        $this->id = trim($id);
        static::$ids[get_called_class()][] = $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    protected static function response(array $components = [], array $params = []): array
    {
        $url = Request::buildURL(array_merge([static::$url, 'api', static::$type], $components), $params);
        return Request::getResponse($url) ?: [];
    }

    public function get(): array
    {
        return static::response([$this->getId()]);
    }

    public static function getAsync(): array
    {
        $urls = [];
        foreach (static::getIds() as $object) {
            $urls[$object->getId()] = Request::buildURL(array_merge([static::$url, 'api', static::$type, $object->getId()]));
        }
        return Request::getResponseAsync($urls);
    }

    protected static function getIds(): array
    {
        return static::$ids[get_called_class()];
    }

    protected static function getBaseUrl(string $url = ''): string
    {
        if (!$url) return '';

        $url = explode('?', $url)[0];
        return Request::buildURL([static::$url, $url]);
    }
}