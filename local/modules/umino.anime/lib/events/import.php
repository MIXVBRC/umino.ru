<?php

namespace Umino\Anime\Events;

use Bitrix\Main\Event;

class Import
{
    public static function OnImportStart(Event $event)
    {
        $fields = $event->getParameters();
//        pre($fields);
    }
    public static function OnImportFinish(Event $event)
    {
        $fields = $event->getParameters();
//        pre($fields);
    }
    public static function OnBeforeImportUpdate(Event $event)
    {
        $fields = $event->getParameters();
//        pre($fields);
    }

    public static function OnAfterImportUpdate(Event $event)
    {
        $fields = $event->getParameters();
//        pre($fields);
    }

    public static function OnImportEpisodesAdd(Event $event)
    {
        $fields = $event->getParameters();
//        pre($fields);
    }
}