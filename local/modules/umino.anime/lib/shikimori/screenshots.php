<?php


namespace Umino\Anime\Shikimori;


class Screenshots extends Entity
{
    protected function rebase(array $fields): array
    {
        $result = [];

        foreach ($fields as $item) {
            $image = Image::create($item['ORIGINAL'], [
                'URL' => $item['ORIGINAL'],
            ]);
            $result[] = [
                'ID' => $image->getId(),
                'IMAGE' => $image,
            ];
        }

        return $result;
    }

    protected static function getUrl(array $additional = []): string
    {
        return Request::buildApiURL(array_merge([Animes::getName()], $additional, [static::getName()]));
    }

    public function getArrays(): array
    {
        $fields = $this->getFields();

        foreach ($fields as &$item) {
            $item = $item['IMAGE']->getArray();
        }

        return $fields;
    }
}