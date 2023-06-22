<?php


namespace Umino\Anime\Shikimori;


class Anime extends Entity
{
    public function __construct(int $id)
    {
        $request = new Request();

        $request->addToAsyncQueue([
            'ANIME' => Request::buildURL(['animes', $id]),
            'ROLES' => Request::buildURL(['animes', $id, 'roles']),
            'SCREENSHOTS' => Request::buildURL(['animes', $id, 'screenshots']),
        ]);

        $request->initAsyncRequest();

        $response = $request->getResult();

        if (empty($response['ANIME'])) return null;

        $response['ANIME']['SCREENSHOTS'] = $response['SCREENSHOTS'];
        $response['ANIME']['ROLES'] = $response['ROLES'];

        $this->fields = self::rebase($response['ANIME']);

        return $this;
    }

    private static function rebase(array $fields): array
    {
        return [
            'ID' => $fields['ID'],
            'NAME' => $fields['RUSSIAN'] ?: $fields['NAME'],
            'NAME_ORIGIN' => $fields['NAME'],
            'NAME_OTHER' => array_merge(
                $fields['LICENSE_NAME_RU'],
                $fields['ENGLISH'],
                $fields['SYNONYMS'],
                $fields['JAPANESE'],
            ),
            'IMAGE' => $fields['IMAGE']['ORIGINAL'],
            'SCREENSHOTS' => Image::getCollection($fields['SCREENSHOTS']),
            'TYPE' => $fields['KIND'],
            'SCORE' => $fields['SCORE'],
            'STATUS' => $fields['STATUS'],
            'EPISODES' => $fields['EPISODES'],
            'EPISODES_AIRED' => $fields['EPISODES_AIRED'],
            'AIRED_ON' => $fields['AIRED_ON'],
            'RELEASED_ON' => $fields['RELEASED_ON'],
            'RATING' => $fields['RATING'],
            'DURATION' => $fields['DURATION'],
            'DESCRIPTION' => strip_tags($fields['DESCRIPTION_HTML']),
            'LICENSORS' => $fields['LICENSORS'],
            'GENRES' => Genre::getCollection(array_column($fields['GENRES'], 'ID')),
            'STUDIOS' => Studio::getCollection(array_column($fields['STUDIOS'], 'ID')),
            'VIDEOS' => Video::getCollection($fields['VIDEOS']),
            'ROLES' => Role::getCollection($fields['ROLES']),
        ];
    }

    public static function getCollection(array $ids): array
    {
        $result = [];

        foreach ($ids as $id) {
            if (empty($id)) continue;
            $result[] = new Anime($id);
        }

        return $result;
    }
}