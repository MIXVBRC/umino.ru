<?php


namespace Umino\Anime\Shikimori\API;


use Umino\Anime\Kodik\API;

class Episode extends Video
{
    protected static array $seasonTypeLang = [
        '' => '',
        'Сезон 2' => '',

        'Рекап' => 'Рекапы',
        'Рекапы' => 'Рекапы',

        'Спешл' => 'Спецвыпуски',
        'Спэшл' => 'Спецвыпуски',
        'Спешлы' => 'Спецвыпуски',
        'Спецвыпуск' => 'Спецвыпуски',
        'Спецвыпуски' => 'Спецвыпуски',
        'Спешлы без перевода' => 'Спецвыпуски без перевода',
        'SP - Gomer' => 'Спецвыпуски',

        '0 серия' => '0 серия',
        'Эпизод 0' => '0 серия',

        'OVA' => 'OVA',
        'ONA' => 'ONA',

        'Коллаж' => 'Коллаж',

        'Фильм' => 'Фильм',
        'Мини-эпизоды' => 'Мини-эпизоды',
        'Манга-эпизоды' => 'Манга-эпизоды',

        'эп-коллаж' => 'Эп-коллаж',
        'Эп-коллаж' => 'Эп-коллаж',

        '1 сезон, Режиссёрская версия' => 'Режиссёрская версия',
        'Дополнительный материал' => 'Дополнительные материалы',
    ];

    protected static function response(array $components = [], array $params = []): array
    {
        return [];
    }

    public function get(): array
    {
        $request = API::search(['shikimori_id' => $this->getParentId()]);

        $items = static::rebase($request);

        foreach ($items as $item) {
            if ($item['id'] != $this->getId()) continue;
            return $item;
        }

        return [];
    }

    public static function getAsync(): array
    {
        $parentIds = [];
        foreach (static::getIds() as $items) {
            $parentIds[$items->getParentId()][] = $items->getId();
        }

        $params = [];
        foreach (array_keys($parentIds) as $parentId) {
            $params[$parentId] = [
                'shikimori_id' => $parentId,
            ];
        }

        $request = API::searchAsync($params);

        foreach ($request as &$items) {
            $items = static::rebase($items);
        }

        $result = [];
        foreach (static::getIds() as $object) {

            $id = $object->getId();
            $parentId = $object->getParentId();

            foreach ($request[$parentId] as $item) {
                if ($item['id'] != $id) continue;
                $result[$id] = $item;
            }
        }

        return $result;
    }

    public static function rebase(array $items): array
    {
        $result = [];

        foreach ($items as $item) {

            $anime = (new Anime($item['shikimori_id']))->get();

            $item['title'] = $anime['russian'] ?: $anime['name'];

            if (empty($item['seasons'])) {
                $id = md5(serialize([trim($item['id'])]));
                $result[$id] = $item;
            } else {
                foreach ($item['seasons'] as $num => $season) {
                    $episodes = [];
                    foreach ($season['episodes'] as $episodeNum => $episode) {
                        $episodes[$episode['title'] ?: $episodeNum] = $episode['link'];
                    }
                    $id = md5(serialize([trim($item['id']), $num]));
                    $new = $item;
                    $new['id'] = $id;
                    $new['season'] = $num;
                    $new['link'] = $season['link'];
                    $new['season_type'] = static::$seasonTypeLang[$season['title']];
                    $new['episodes'] = $episodes;
                    $new['episodes_count'] = count($season['episodes']);
                    unset($new['seasons'], $new['screenshots']);
                    $result[$id] = $new;
                }
            }
        }

        return $result;
    }
}