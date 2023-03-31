<?php


namespace Umino\Kodik;


use Exception;

class Parser
{
    /**
     * Возвращает html страницы по ссылке
     *
     * @param $url
     * @return bool|mixed|string
     */
    protected function getPageContent($url)
    {
        $page = '';

        try {
            if ($curl = curl_init($url)) {
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($curl, CURLOPT_HTTPHEADER, false);
                $page = curl_exec($curl);
                curl_close($curl);
            } else {
                $page = file_get_contents($url);
            }
        } catch (Exception $e) {
            pre($e->getMessage());
        }

        return $page;
    }
}