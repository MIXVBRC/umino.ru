<?php


namespace Umino\Kodik;


use COption;
use Umino\Kodik\Tables\ImportTable;

class API
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

    public static function getStages(): array
    {
        return self::$stages;
    }

    public static function getTypes(): array
    {
        return array_keys(self::$types);
    }

    /**
     * @param string $url
     * @return array
     */
    protected static function request(string $url): array
    {
        $xml_id = md5($url);

        if ($results = ImportTable::getByXmlId($xml_id, true)) {
            return $results;
        }

        $request = Request::getContent($url, true);

        if (empty($request) || !empty($request['error'])) return [];

        $request = array_change_key_case($request, CASE_UPPER);

        $results = array_merge($request, [
            'XML_ID' => $xml_id,
            'URL' => $url,
            'RESULTS_COUNT' => count($request['RESULTS']),
        ]);

        $id = ImportTable::add($results);

        if (!$id->isSuccess()) return [];

        return ImportTable::getByXmlId($xml_id, true)?:[];
    }

    /**
     * @param array $params
     * @return array
     */
    public static function search(array $params): array
    {
        $components = [
            Core::getAPIUrl(),
            __FUNCTION__
        ];

        $params = array_merge($params, [
            'with_episodes' => 'false',
            'token' => Core::getAPIToken(),
        ]);

        $url = Request::buildURL($components, $params);

        return self::request($url);
    }

    /**
     * @param string $type - type (film/cartoon/anime/serial/cartoon-serial/anime-serial)
     * @param int $count - results count
     * @return array
     */
    protected static function list(string $type, int $pageCount): array
    {
        $components = [
            Core::getAPIUrl(),
            __FUNCTION__
        ];

        $params = [
            'token' => Core::getAPIToken(),
            'limit' => Core::getAPILimit(),
            'types' => implode(',', self::$types[$type]),
        ];

        $url = Request::buildURL($components, $params);

        $result = $request = self::request($url);

        for ($page = 1; $page < $pageCount; $page++) {

            if (empty($request['NEXT_PAGE'])) break;

            $request = self::request($request['NEXT_PAGE']);
            $result['RESULTS'] = array_merge($result['RESULTS'], $request['RESULTS']);
            if (!is_array($result['TIME'])) {
                $result['TIME'] = [$result['TIME']];
            }
            $result['TIME'] = array_merge($result['TIME'], $request['TIME']);
        }

        return $result?:[];
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getFilmList(int $pageCount): array
    {
        return self::list('film', $pageCount);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getCartoonList(int $pageCount): array
    {
        return self::list('cartoon', $pageCount);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getAnimeList(int $pageCount): array
    {
        return self::list('anime', $pageCount);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getSerialList(int $pageCount): array
    {
        return self::list('serial', $pageCount);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getCartoonSerialList(int $pageCount): array
    {
        return self::list('cartoon-serial', $pageCount);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @param int $limit - max 100, default: 100
     * @return array
     */
    public static function getAnimeSerialList(int $pageCount): array
    {
        return self::list('anime-serial', $pageCount);
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