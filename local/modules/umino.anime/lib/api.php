<?php


namespace Umino\Anime;

use Bitrix\Main\Type\DateTime;
use Umino\Anime\Tables\KodikRequestTable;
use Umino\Anime\Tables\KodikResultTable;

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
    protected static function request(string $url, bool $full = true): array
    {
        $kodikRequest = KodikRequestTable::getList([
            'filter' => [
                'URL' => $url,
            ],
        ])->fetch();

        if ($kodikRequest) {
            $kodikResults = KodikResultTable::getList([
                'filter' => ['REQUEST_ID' => $kodikRequest['ID']]
            ])->fetchAll();

            if ($full) self::full($kodikResults);

            $kodikRequest['RESULTS'] = $kodikResults;

            return $kodikRequest;
        }

        $request = Request::getContent($url, true);

        Core::keysToUpperCase($request);

        $fields = array_merge($request, [
            'URL' => $url,
            'TYPE' => $url,
            'RESULTS_COUNT' => count($request['RESULTS'])
        ]);

        $kodikRequestAdd = KodikRequestTable::add($fields);

        Logger::log([
            'message' => $kodikRequestAdd->getErrorMessages(),
            'fields' => $fields,
        ]);

        unset($fields);

        foreach ($request['RESULTS'] as &$result) {
            $result['REQUEST_ID'] = $kodikRequestAdd->getId();
        } unset($result);

        $resultIds = array_map(function ($result) { return $result['ID']; }, $request['RESULTS']);

        $kodikResults = KodikResultTable::getList([
            'filter' => ['KODIK_ID' => $resultIds],
            'select' => ['ID', 'KODIK_ID'],
        ])->fetchAll();

        $kodikResultIds = [];

        foreach ($kodikResults as $kodikResult) {
            $kodikResultIds[$kodikResult['KODIK_ID']] = $kodikResult['ID'];
        }

        foreach ($request['RESULTS'] as $result) {

            $fields = array_merge($result, [
                'OTHER_TITLE' => array_map(function ($title) { return trim($title); }, explode('/', $result['OTHER_TITLE'])),
                'CREATED_AT' => DateTime::createFromTimestamp(strtotime($result['CREATED_AT'])),
                'UPDATED_AT' => DateTime::createFromTimestamp(strtotime($result['UPDATED_AT'])),
                'REQUEST_ID' => $kodikRequestAdd->getId(),
                'KODIK_ID' => $result['ID'],
            ]);

            unset($fields['ID']);

            if ($kodikResultIds && $kodikResultIds[$fields['KODIK_ID']]) {
                unset($fields['REQUEST_ID']);
                $update = KodikResultTable::update($kodikResultIds[$fields['KODIK_ID']], $fields);
                Logger::log([
                    'message' => $update->getErrorMessages(),
                    'id' => $kodikResultIds[$fields['KODIK_ID']],
                    'fields' => $fields,
                ]);
            } else {
                $add = KodikResultTable::add($fields);
                Logger::log([
                    'message' => $add->getErrorMessages(),
                    'fields' => $fields,
                ]);
            }

        }

        if ($full) self::full($request['RESULTS']);

        return $request;
    }

    /**
     * @param array $results
     */
    protected static function full(array &$results)
    {
        foreach ($results as $result) {
            if ($result['KINOPOISK_ID']) {
                $search = self::searchByKinopoiskId($result['KINOPOISK_ID'])['RESULTS'];
            } elseif ($result['SHIKIMORI_ID']) {
                $search = self::searchByShikimoriId($result['SHIKIMORI_ID'])['RESULTS'];
            }

            if (empty($search)) continue;

            $results = array_merge($results, $search);
        }
    }

    /**
     * @param string $type
     * @param int $pageCount
     * @return array
     */
    protected static function list(string $type, int $pageCount): array
    {
        $pageCount = $pageCount > 0 ? $pageCount : 1;

        $params = [
            'token' => Core::getAPIToken(),
            'limit' => Core::getAPILimit(),
            'types' => implode(',', self::$types[$type]),
        ];

        $url = Request::buildURL([__FUNCTION__], $params);

        $result = $request = self::request($url);

        $pageCount = $pageCount > 0 ? $pageCount : ceil($result['TOTAL'] / 100);

        for ($page = 1; $page < $pageCount; $page++) {

            if (empty($request['NEXT_PAGE'])) break;

            $request = self::request($request['NEXT_PAGE']);
            $result['RESULTS'] = array_merge($result['RESULTS'], $request['RESULTS']);
        }

        return $result?:[];
    }

    public static function next(string $type): array
    {
        $kodikRequest = KodikRequestTable::getList([
            'filter' => [
                [
                    'LOGIC' => 'AND',
                    ['%URL' => '/list?'],
                    ['%URL' => $type],
                ],
            ],
            'select' => ['NEXT_PAGE'],
            'order' => [
                'DATE_REQUEST' => 'DESC',
                'ID' => 'DESC',
            ],
            'limit' => 1,
        ]);

        $nextPage = $kodikRequest->fetch()['NEXT_PAGE'];

        if ($nextPage && $nextPage = self::getLast($nextPage)) return self::request($nextPage);

        $kodikRequestList = KodikRequestTable::getList(['select' => ['ID']])->fetchAll();

        foreach ($kodikRequestList as $kodikRequest) {
            $delete = KodikRequestTable::delete($kodikRequest['ID']);
            if (!$delete->isSuccess()) {
                Logger::log([
                    'message' => $delete->getErrorMessages(),
                    'fields' => ['ID' => $kodikRequest['ID']],
                ]);
            }
        }

        return [];
    }

    protected static function getLast(string $nextPage): string
    {
        $kodikRequest = KodikRequestTable::getList([
            'filter' => [
                'URL' => $nextPage,
            ],
            'select' => ['NEXT_PAGE'],
            'limit' => 1,
        ])->fetchObject();

        if (empty($kodikRequest)) return $nextPage;

        return self::getLast($kodikRequest->getNextPage());
    }

    /**
     * @param array $params
     * @return array
     */
    protected static function search(array $params): array
    {
        $params = array_merge($params, [
            'with_episodes' => 'false',
            'token' => Core::getAPIToken(),
        ]);

        $url = Request::buildURL([__FUNCTION__], $params);

        return self::request($url, false);
    }

    /**
     * @param $id
     * @return array
     */
    public static function searchByKinopoiskId($id): array
    {
        return self::search(['kinopoisk_id'=>$id]);
    }

    /**
     * @param $id
     * @return array
     */
    public static function searchByShikimoriId($id): array
    {
        return self::search(['shikimori_id'=>$id]);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @return array
     */
    public static function getFilms(int $pageCount): array
    {
        return self::list('film', $pageCount);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @return array
     */
    public static function getCartoons(int $pageCount): array
    {
        return self::list('cartoon', $pageCount);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @return array
     */
    public static function getAnime(int $pageCount): array
    {
        return self::list('anime', $pageCount);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @return array
     */
    public static function getSerials(int $pageCount): array
    {
        return self::list('serial', $pageCount);
    }

    /**
     * @param int $pageCount
     * @return array
     */
    public static function getCartoonSerials(int $pageCount): array
    {
        return self::list('cartoon-serial', $pageCount);
    }

    /**
     * @param int $pageCount - 0 is full pages
     * @return array
     */
    public static function getAnimeSerials(int $pageCount): array
    {
        $list = self::list('anime-serial', $pageCount);

        return $list;
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