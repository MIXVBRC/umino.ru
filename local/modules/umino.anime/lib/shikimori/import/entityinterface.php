<?php


namespace Umino\Anime\Shikimori\Import;


interface EntityInterface
{
    public static function rebaseFields(array $fields): array;
}