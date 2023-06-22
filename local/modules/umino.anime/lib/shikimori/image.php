<?php


namespace Umino\Anime\Shikimori;


class Image
{
    private string $url = '';

    public function __construct(string $url)
    {
        $this->url = self::rebase($url);

        return $this;
    }

    public function getFileArray(): array
    {
        return \CFile::GetFileArray($this->url);
    }

    private static function rebase($fields)
    {
        if (is_array($fields)) {
            $result = Request::buildURL([$fields['ORIGINAL']]);
        } else {
            $result = Request::buildURL([$fields]);
        }

        return $result;
    }

    public static function getCollection(array $urls): array
    {
        $result = [];

        foreach ($urls as $url) {
            if (empty($url)) continue;
            if (is_array($url)) {
                $result[] = new Image($url['ORIGINAL']);
            } else {
                $result[] = new Image($url);
            }

        }

        return $result;
    }


}