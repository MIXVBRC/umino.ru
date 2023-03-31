<?php


namespace Umino\Kodik;


class Request
{
    protected static $headers = [
        'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.2924.87 Safari/537.36',
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
        'Connection: keep-alive',
        'Cache-Control: max-age=0',
        'Upgrade-Insecure-Requests: 1'
    ];

    /**
     * @param string $url
     * @param bool $isJson
     */
    public static function getContent(string $url, bool $isJson = false)
    {
        if ($curl = curl_init($url)) {
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, self::$headers);
            $result = curl_exec($curl);
            curl_close($curl);
        } else {
            $result = file_get_contents($url);
        }

        if ($isJson) {
            $result = json_decode($result, true);

            foreach ($result as $key => $value) {
                if (!is_null($value)) continue;
                unset($result[$key]);
            }
        }

        return $result;
    }

    /**
     * @param array $components
     * @param array $params
     * @return string
     */
    public static function buildURL(array $components, array $params = []): string
    {
        foreach ($components as &$component) {
            if (!is_array($component)) continue;
            $component = self::buildURL($component);
        }

        $result = preg_replace('/^http[a-z]{0,1}[:]{1}[\/]{1,}/', '', implode('/', $components));

        if (!empty($params)) {
            $result .= '?' . http_build_query($params);
        }

        return str_replace('//', '/', $result);
    }
}