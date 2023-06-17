<?php


namespace Umino\Anime;


class Request
{
    protected static array $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_FRESH_CONNECT => true,
        CURLOPT_FORBID_REUSE => true,
        CURLOPT_HEADER => false,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.2924.87 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
            'Connection: keep-alive',
            'Cache-Control: max-age=0',
            'Upgrade-Insecure-Requests: 1'
        ],
    ];

    public array $asyncRequests = [];

    private static function convertJson(string $json)
    {
        $result = json_decode($json, true);

        foreach ($result as $key => $value) {
            if (!is_null($value)) continue;
            unset($result[$key]);
        }

        return $result;
    }

    /**
     * @param string $url
     * @param bool $isJson
     * @return array|null
     */
    public static function getResponse(string $url, bool $isJson = true): ?array
    {
        if ($curl = curl_init($url)) {
            curl_setopt_array($curl, self::$options);
            $result = curl_exec($curl);
            curl_close($curl);
        } else {
            $result = file_get_contents($url);
        }

        return $isJson ? self::convertJson($result) : $result;
    }

    /**
     * @param array $components
     * @param array $params
     * @return string
     */
    public static function buildURL(array $components, array $params = []): string
    {
        $components = array_merge([Core::getAPIUrl()], $components);

        foreach ($components as &$component) {
            if (!is_array($component)) continue;
            $component = self::buildURL($component);
        }

        $result = preg_replace('/^http[a-z]{0,1}[:]{1}[\/]{1,}/', '', implode('/', $components));

        foreach ($params as &$param) {
            if (!is_array($param)) continue;
            $param = implode(',', $param);
        }

        if (!empty($params)) {
            $result .= '?' . http_build_query($params);
        }

        return 'https://' . str_replace('//', '/', $result);
    }

    public function addAsyncRequest(array $urls, bool $isJson = true)
    {
        foreach ($urls as $key => $url) {
            $this->asyncRequests[$key] = [
                'URL' => $url,
                'JSON' => $isJson,
            ];
        }
    }

    public function getAsyncResponse(): array
    {
        $result = [];

        $this->asyncRequests = array_chunk($this->asyncRequests, 20, true);

        foreach ($this->asyncRequests as $asyncRequestChunk) {

            $curls = curl_multi_init();

            foreach ($asyncRequestChunk as &$asyncRequest) {
                $ch = self::getHandle($asyncRequest['URL']);
                $asyncRequest['CH'] = $ch;
                curl_multi_add_handle($curls, $ch);
            } unset($asyncRequest);

            do {
                $status = curl_multi_exec($curls, $active);
                if ($active) curl_multi_select($curls);
            } while ($active && $status == CURLM_OK);

            foreach ($asyncRequestChunk as $key => $asyncRequest) {

                $code = curl_getinfo($asyncRequest['CH'], CURLINFO_HTTP_CODE);
                $response = curl_multi_getcontent($asyncRequest['CH']);

                if ($code != 200) {
                    Logger::log([
                        'URL' => $asyncRequest['URL'],
                        'CODE' => $code,
                        'RESPONSE' => self::convertJson($response),
                    ]);
                }

                $result[$key] = $asyncRequest['JSON'] && !empty($response) ? self::convertJson($response) : $response;
                curl_multi_remove_handle($curls, $asyncRequest['CH']);
            }

            curl_multi_close($curls);

        }

        return $result;
    }

    private function getHandle(string $url)
    {
        $ch = curl_init();
        curl_setopt_array($ch, self::$options);
        curl_setopt($ch, CURLOPT_URL, $url);
        return $ch;
    }
}