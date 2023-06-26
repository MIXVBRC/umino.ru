<?php


namespace Umino\Anime\Shikimori;


class Image extends Entity
{
    protected static bool $md5Id = true;

    protected function rebase(array $fields): array
    {
        return [
            'XML_ID' => $this->getId(),
            'URL' => Request::buildFileURL([$fields['URL']]),
        ];
    }

    public function getArray(): array
    {
        $fields = static::getFields();
        return \CFile::MakeFileArray($fields['URL']);
    }

    protected static function load(): array
    {
        return [];
    }
}