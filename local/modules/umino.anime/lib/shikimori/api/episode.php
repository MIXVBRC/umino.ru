<?php


namespace Umino\Anime\Shikimori\API;


use Umino\Anime\Kodik\API;

class Episode extends Entity
{
    protected static function response(array $components = [], array $params = []): array
    {
        return [];
    }

    public function get(): array
    {
        return API::search(['id' => $this->getId()]);
    }

    public static function getAsync(): array
    {
        $params = [];
        foreach (static::getIds() as $object) {
            $params[$object->getId()] = [
                'id' => $object->getId(),
            ];
        }
        
        return API::searchAsync($params);
    }
}