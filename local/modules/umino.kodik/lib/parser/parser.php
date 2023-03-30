<?php


namespace Umino\Kodik\Parser;


use Exception;
use phpQuery;

class Parser
{
    protected $id;
    protected $url;
    protected $phpQuery;

    public function __construct($id)
    {
        $this->id = $id;
        $this->url .= $this->id;

        if (empty($page = self::getPageContent($this->url))) return null;

        $this->phpQuery = phpQuery::newDocument($page);
    }

    /**
     * Возвращает html страницы по ссылке
     *
     * @param $url
     * @return bool|mixed|string
     */
    public static function getPageContent($url)
    {
        $page = '';

        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/90.0.2924.87 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3',
            'Connection: keep-alive',
            'Cache-Control: max-age=0',
            'Upgrade-Insecure-Requests: 1'
        );

        try {
            if ($curl = curl_init($url)) {
                
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

                $page = curl_exec($curl);

                curl_close($curl);
            } else {
                $page = file_get_contents($url);
            }
        } catch (Exception $e) {
            pre($e->getMessage());
        }

        //usleep(100000);

        return $page;
    }
}