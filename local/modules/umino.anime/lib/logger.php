<?php


namespace Umino\Anime;


use Umino\Anime\Tables\LogTable;

class Logger
{
    public static function log($info)
    {
        if (empty($info['message'])) return;

        $trace = debug_backtrace();

        if (is_array($info)) {
            $info = serialize($info);
        }

        LogTable::add([
            'FILE' => $trace[0]['file'],
            'LINE' => $trace[0]['line'],
            'MESSAGE' => $info,
        ]);
    }
}