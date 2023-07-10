<?php


namespace Umino\Anime\Shikimori\API;


class Character extends People
{
    protected static string $type = 'characters';

    public static function search(array $params = []): array
    {
        return static::response([], $params);
    }

    public function get(): array
    {
        $result = parent::get();
        $result['description'] = strip_tags($result['description_html'], '<br>');
        $result['spoiler'] = stristr($result['description'], 'спойлер');
        $result['spoiler'] = str_replace(['спойлер'], '', $result['spoiler']);
        $result['description'] = stristr($result['description'], 'спойлер', true);
        unset($result['description_html']);
        return $result;
    }
}