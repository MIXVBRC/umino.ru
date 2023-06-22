<?php


namespace Umino\Anime\Shikimori;


class Studio extends Entity
{
    public function __construct(int $id)
    {
        if (empty(self::$data) || empty(self::$data[$id])) {
            $response = Request::getResponse(Request::buildURL(['studios']));
            foreach ($response as $item) {
                self::$data[$item['ID']] = self::rebase($item);
            }
        }

        $this->fields = self::$data[$id];

        return $this;
    }

    private static function rebase(array $fields): array
    {
        return [
            'ID' => $fields['ID'],
            'NAME' => $fields['FILTERED_NAME'] ?: $fields['NAME'],
            'NAME_ORIGIN' => $fields['NAME'],
            'IMAGE' => $fields['IMAGE'],
        ];
    }

    public static function getCollection(array $ids): array
    {
        $result = [];

        foreach ($ids as $id) {
            if (empty($id)) continue;
            $result[] = new Studio((int) $id);
        }

        return $result;
    }
}