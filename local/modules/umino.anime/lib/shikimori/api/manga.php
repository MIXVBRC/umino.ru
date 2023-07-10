<?php


namespace Umino\Anime\Shikimori\API;


class Manga extends Entity
{
    protected static string $type = 'mangas';

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
        return $this->response([$this->id, __FUNCTION__]);
    }

    public function similar(): array
    {
        return $this->response([$this->id, __FUNCTION__]);
    }

    public function related(): array
    {
        return $this->response([$this->id, __FUNCTION__]);
    }

    public function franchise(): array
    {
        return $this->response([$this->id, __FUNCTION__]);
    }

    public function externalLinks(): array
    {
        return $this->response([$this->id, 'external_links']);
    }

    public function topics(): array
    {
        return $this->response([$this->id, __FUNCTION__]);
    }
}