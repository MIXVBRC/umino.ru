<?php


namespace Umino\Anime\Shikimori\API;


use Umino\Anime\Kodik\API;
use Umino\Anime\Request;

class Anime extends Entity
{
    protected static string $type = 'animes';

    public function get(): array
    {
        $response = parent::get();

        $response['image'] = static::getBaseUrl($response['image']['original']);

        $response['description'] = strip_tags($response['description_html'], '<br>');
        unset($response['description_html']);

        return $response;
    }

    public static function getList(array $params = []): array
    {
        return static::response([], $params);
    }

    public function roles(): array
    {
        return static::response([$this->getId(), __FUNCTION__]);
    }

    public function screenshots(int $max = 0): array
    {
        $results = [];

        $response = static::response([$this->getId(), __FUNCTION__]);

        foreach ($response as $item) {
            $max--;
            $url = explode('?', $item['original'])[0];
            $results[] = Request::buildURL([static::$url, $url]);
            if ($max == 0) break;
        }

        return $results;
    }

    public function similar(): array
    {
        return static::response([$this->getId(), __FUNCTION__]);
    }

    public function related(): array
    {
        return static::response([$this->getId(), __FUNCTION__]);
    }

    public function franchise(): array
    {
        return static::response([$this->getId(), __FUNCTION__]);
    }

    public function externalLinks(): array
    {
        return static::response([$this->getId(), 'external_links']);
    }

    public function topics(): array
    {
        return static::response([$this->getId(), __FUNCTION__]);
    }

    public function videos(): array
    {
        return static::response([$this->getId(), __FUNCTION__]);
    }

    public function episodes(): array
    {
        return API::search(['shikimori_id'=>$this->getId()]);
    }
}