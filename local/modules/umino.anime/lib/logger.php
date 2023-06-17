<?php


namespace Umino\Anime;


use Umino\Anime\Tables\LogTable;

class Logger
{
    public static function log($info)
    {
        if (empty($info)) return;

        $trace = debug_backtrace();

        $fields = [
            'FILE' => $trace[0]['file'],
            'LINE' => $trace[0]['line'],
            'MESSAGE' => $info,
        ];

        $result = LogTable::add($fields);

        if (!$result->isSuccess()) {
            var_dump($result->getErrorMessages());
        }
    }
}