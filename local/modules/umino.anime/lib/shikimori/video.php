<?php


namespace Umino\Anime\Shikimori;


class Video extends Entity
{
    public function __construct(array $fields)
    {
        $this->fields = self::rebase($fields);

        return $this;
    }

    private static function rebase(array $fields): array
    {
        return [
            'ID' => $fields['ID'],
            'NAME' => $fields['NAME'],
            'URL' => $fields['URL'],
            'IMAGE_URL' => $fields['IMAGE_URL'],
            'TYPE' => $fields['KIND'],
            'HOSTING' => $fields['HOSTING'],
        ];
    }

    public static function getCollection(array $videos): array
    {
        $result = [];

        foreach ($videos as $video) {
            if (empty($video)) continue;
            $result[] = new Video($video);
        }

        return $result;
    }
}