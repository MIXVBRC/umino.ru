<?php


namespace Umino\Kodik;


use COption;

Class API
{
    protected static $stages = [
        'shikimori',
        'worldart_animation',
        'kinopoisk',
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

    protected static function getToken(): string
    {
        return COption::GetOptionString("umino.kodik", "api_token");
    }

    protected static function getUrl(): string
    {
        return COption::GetOptionString("umino.kodik", "api_url");
    }

    public static function getStages(): array
    {
        return self::$stages;
    }

    public static function getTypes(): array
    {
        return array_keys(self::$types);
    }

    /**
     * @param string $page
     * @param array $params
     * @return array
     */
    protected static function request(string $page, array $params): array
    {
        $params = array_merge($params, ['token' => self::getToken()]);
        $url = self::getUrl() . $page . '?' . http_build_query($params);

        if ($curl = curl_init()) {

            $headers = array(
                'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.2924.87 Safari/537.36',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
                'Connection: keep-alive',
                'Cache-Control: max-age=0',
                'Upgrade-Insecure-Requests: 1'
            );
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $out = curl_exec($curl);
            $parse = json_decode($out, true);
            curl_close($curl);

        } else {

            $out = file_get_contents($url);
            $parse = json_decode($out, true);

        }

        return $parse ?: [];
    }

    /**
     * @param string $stage - stage name [getStages()]
     * @param int $id - id from stage
     * @return array
     */
    public static function search(array $filter): array
    {
        $filter['with_episodes'] = 'false';
        return self::request(__FUNCTION__, $filter)['results']?:[];
    }

    /**
     * @param string $type - type (film/cartoon/anime/serial/cartoon-serial/anime-serial)
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    protected static function list(string $type, int $pageCount, int $limit = 100): array
    {
        $request = self::request(__FUNCTION__, [
            'limit' => $limit,
            'types' => implode(',', self::$types[$type]),
        ]);

        if (empty($request) || !empty($request['error'])) return $request;

        if ($pageCount <= 0) {
            $pageCount = (int) ceil($request['total'] / $limit);
        }

        $result = $request['results'];

        for ($page = 1; $page < $pageCount; $page++) {

            if (empty($nextPage = $request['next_page'])) break;

            $params = self::getParams($nextPage);

            $request = self::request(__FUNCTION__, [
                'token' => self::getToken(),
                'page' => $params['page'],
            ]);

            if (empty($request)) break;

            $result = array_merge($result, $request['results']);

        }

        return $result?:[];
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getFilmList(int $pageCount, int $limit = 100): array
    {
        return self::list('film', $pageCount, $limit);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getCartoonList(int $pageCount, int $limit = 100): array
    {
        return self::list('cartoon', $pageCount, $limit);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getAnimeList(int $pageCount, int $limit = 100): array
    {
        return self::list('anime', $pageCount, $limit);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getSerialList(int $pageCount, int $limit = 100): array
    {
        return self::list('serial', $pageCount, $limit);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getCartoonSerialList(int $pageCount, int $limit = 100): array
    {
        return self::list('cartoon-serial', $pageCount, $limit);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getAnimeSerialList(int $pageCount, int $limit = 100): array
    {
        return self::list('anime-serial', $pageCount, $limit);
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