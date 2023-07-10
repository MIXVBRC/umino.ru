<?php


namespace Umino\Anime\Kodik;


use Umino\Anime\Core;
use Umino\Anime\Request;


class API
{
    protected static $stages = [
        'shikimori',
        'worldart_animation',
        'kinopoisk',
    ];

    protected static $searchList = [
        'KINOPOISK_ID',
        'SHIKIMORI_ID',
        'IMDB_ID',
        'WORLDART_LINK',
    ];

    protected static $types = [

        // Фильмы
        'film' => [
            'russian-movie',
            'foreign-movie',
        ],

        // Мультфильмы
        'cartoon' => [
            'soviet-cartoon',
            'foreign-cartoon',
            'russian-cartoon',
        ],

        // Аниме
        'anime' => [
            'anime',
        ],

        // Сериалы
        'serial' => [
            'russian-serial',
            'foreign-serial',
            'multi-part-film',
        ],

        // Мультсериалы
        'cartoon-serial' => [
            'cartoon-serial',
            'russian-cartoon-serial',
        ],

        // Аниме сериалы
        'anime-serial' => [
            'anime-serial',
        ],
    ];

    protected static bool $saveNextPage = true;

    public static function getSearchList(): array
    {
        return self::$searchList;
    }

    public static function saveNextPage(bool $flag): void
    {
        self::$saveNextPage = $flag;
    }

    public static function getStages(): array
    {
        return self::$stages;
    }

    public static function getAllTypes(): array
    {
        return array_keys(self::$types);
    }

    public static function getDefaultParams(): array
    {
        return [
            'with_seasons' => 'true',
            'with_episodes' => 'true',
//            'with_material_data' => 'true',
            'token' => Core::getAPIToken(),
        ];
    }

    public static function getTypes(array $types): ?array
    {
        $result = [];
        foreach ($types as $type => $value) {
            if (in_array($type, ['custom'], true)) {
                foreach ($value as $kind) {pre($kind);
                    $result = array_merge($result, [$kind]);
                }
            } else {
                $result = array_merge($result, self::$types[$value]);
            }
        }
        return array_unique($result);
    }

    protected static function request(string $url): array
    {
        $result = [];

        for ($page = 0; $page < Core::getAPILimitPage(); $page++) {

            $request = Request::getResponse($url);

            Core::keysToUpperCase($request);

            $url = $request['NEXT_PAGE'] ? : '';

            if (Core::getAPISaveNextPage()) {
                Core::setAPINextPage($url);
                if (empty($url)) {
                    Core::setAPIFullImport(false);
                    Core::setAPISaveNextPage(false);
                    Core::setAPIDateUpdateImport(true);
                }
            }

            if (empty($url)) break;

            if (Core::getAPIFill()) {
                self::fill($request['RESULTS']);
            }

            $result = array_merge($result, $request['RESULTS']);
        }

        return $result;
    }

    /**
     * @param array $types
     * @return array
     */
    protected static function list(array $types): array
    {
        $params = array_merge(
            self::getDefaultParams(),
            [
                'limit' => Core::getAPILimit(),
                'types' => self::getTypes($types),
            ]
        );

        $url = Request::buildURL([Core::getAPIUrl(),__FUNCTION__], $params);

        return self::request($url);
    }

    public static function next(): array
    {
        $nextPage = Core::getAPINextPage();
        if (empty($nextPage)) return [];
        return self::request($nextPage);
    }

    /**
     * Поиск по параметрам
     *
     * @param array $param
     * @return array
     */
    public static function search(array $param): array
    {
        $request = Request::getResponse(self::getSearchUrl($param))['results'];

        return $request ?: [];
    }

    /**
     * Асинхронный поиск по параметрам
     *
     * @param array $params
     * @return array|null
     */
    public static function searchAsync(array $params): ?array
    {
        $result = [];

        $urls = [];
        foreach ($params as $key => $param) {
            $urls[$key] = self::getSearchUrl($param);
        }

        $responses = self::getAsyncResponse($urls);

        foreach ($responses as $key => $response) {
            if (empty($response['RESULTS'])) continue;
            $result[$key] = $response['RESULTS'];
        }

        return $result;
    }

    protected static function getSearchUrl(array $params): string
    {
        return Request::buildURL([Core::getAPIUrl(),'search'], array_merge(
            $params,
            self::getDefaultParams(),
            ['limit'=>100],
        ));
    }

    /**
     * @param array $request
     */
    protected static function fill(array &$request)
    {
        $params = [];
        foreach ($request as $result) {
            foreach (self::getSearchList() as $property) {
                if (empty($result[$property])) continue;
                $params[$result[$property]] = [strtolower($property)=>$result[$property]];
                break;
            }
        }

        $searches = self::searchAsync($params);

        foreach ($searches as $search) {
            $request = array_merge($request, $search);
        }
    }

    public static function lastUpdate(array $items): array
    {
        $dateLastUpdate = Core::getAPILastDateUpdate();
        $dateNew = $dateLastUpdate;

        foreach ($items as $key => $item) {
            $date = Core::getDate($item['UPDATED_AT']);

            if (Core::checkDate($date, $dateNew) > 0) {
                $dateNew = $date;
            }

            if (Core::checkDate($date, $dateLastUpdate) > 0) continue;
            unset($items[$key]);
        }

        if ($dateNew != $dateLastUpdate) {
            Core::setAPILastDateUpdate($dateNew);
        }

        return $items;
    }

    private static function getAsyncResponse(array $urls): array
    {
        $request = new Request();
        $request->addToAsyncQueue($urls);
        $request->initAsyncRequest();
        $responses = $request->getResult();

        Core::keysToUpperCase($responses);

        return $responses;
    }

    /**
     * @return array
     */
    public static function getFilms(): array
    {
        return self::list(['film']);
    }

    /**
     * @return array
     */
    public static function getCartoons(): array
    {
        return self::list(['cartoon']);
    }

    /**
     * @return array
     */
    public static function getAnime(): array
    {
        return self::list(['anime']);
    }

    /**
     * @return array
     */
    public static function getSerials(): array
    {
        return self::list(['serial']);
    }

    /**
     * @return array
     */
    public static function getCartoonSerials(): array
    {
        return self::list(['cartoon-serial']);
    }

    /**
     * @return array
     */
    public static function getAnimeSerials(): array
    {
        return self::list(['anime-serial']);
    }

    /**
     * @return array
     */
    public static function getFullAnime(): array
    {
        return self::list(['anime', 'anime-serial']);
    }

    /**
     * @param array $types
     * @return array
     */
    public static function getByTypes(array $types): array
    {
        return self::list($types);
    }

    /**
     * @return array
     */
    public static function getCustom(array $types): array
    {
        return self::list(['custom' => $types]);
    }

    public static function translations(): array
    {
        $url = Request::buildURL([Core::getAPIUrl(),__FUNCTION__], [
            'token' => Core::getAPIToken(),
        ]);
        return Request::getResponse($url);
    }

    /**
     * @param string $url
     * @return array
     */
    protected static function getParams(string $url): array
    {
        $params = explode('&',explode('?',$url)[1]);
        foreach ($params as $key => $param) {
            unset($params[$key]);
            $param = explode('=', $param);
            $params[$param[0]] = $param[1];
        }
        return $params;
    }
}