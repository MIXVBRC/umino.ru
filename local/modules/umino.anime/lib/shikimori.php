<?php


namespace Umino\Anime;


class Shikimori
{
    protected int $id;

    protected static string $url = 'https://shikimori.one';
    protected static string $api = '/api';

    protected array $data = [];

    protected array $collects = [];
    protected array $expand = [];

    public function __construct(int $id)
    {
        $this->id = $id;

        $this->addCollect('ANIME', [$id]);
        $this->addCollect('ROLES', [$id]);
        $this->addCollect('SCREENSHOTS', [$id]);

        $request = new Request();

        $request->addToAsyncQueue([
            'ANIME' => Request::buildURL([self::$url, self::$api, 'animes', $this->id]),
            'ROLES' => Request::buildURL([self::$url, self::$api, 'animes', $this->id, 'roles']),
            'SCREENSHOTS' => Request::buildURL([self::$url, self::$api, 'animes', $this->id, 'screenshots']),
        ]);

        $request->initAsyncRequest();

        $response = $request->getResult();

        $this->addExpand('ANIME', [$id => $response['ANIME']]);
        $this->addExpand('ROLES', [$id]);
        $this->addExpand('SCREENSHOTS', [$id]);

        Core::keysToUpperCase($response);

        $this->rebaseScreenshots($response['SCREENSHOTS']);

        $response['ANIME']['SCREENSHOTS'] = $response['SCREENSHOTS'];
        unset($response['SCREENSHOTS']);

        $this->rebaseRoles($response['ROLES']);

        pre($this->collects);
        pre($this->expand);
    }

    public static function getLastId(): int
    {
        $url = Request::buildURL([self::$url], [
            'limit' => 1,
            'order' => 'id_desc',
        ]);

        $result = Request::getResponse($url);

        return current($result)['id'];
    }

    protected function rebaseRoles(array &$roles)
    {
        foreach ($roles as $key => $role) {
            unset($roles[$key]);
            if (empty($role['PERSON'])) {
                $roles['CHARACTERS'][$role['CHARACTER']['ID']] = $role['CHARACTER']['ID'];
            } else {
                $roles['PEOPLE'][$role['PERSON']['ID']] = $role['PERSON']['ID'];
            }
        }

        $this->addCollect('CHARACTERS', $roles['CHARACTERS']);
        $this->addCollect('PEOPLE', $roles['PEOPLE']);
    }



    protected function rebaseCharacters(&$characters)
    {
        foreach ($characters as &$character) {

            foreach ($character['SEYU'] as &$people) {
                $people = $people['ID'];
                $this->addCollect('PEOPLE', [$people['ID']]);
            }

            foreach ($character['ANIMES'] as &$anime) {
                $anime = $anime['ID'];
                $this->addCollect('ANIME', [$anime['ID']]);
            }

            foreach ($character['MANGAS'] as &$manga) {
                $manga = $manga['ID'];
                $this->addCollect('MANGA', [$manga['ID']]);
            }

            $this->addCollect('CHARACTERS', [$character['ID']]);

            $character = [
                'ID' => $character['ID'],
                'NAME' => $character['RUSSIAN']?:$character['NAME'],
                'DETAIL_PICTURE' => Request::buildURL([self::$url,$character['IMAGE']['ORIGINAL']]),
                'ALTNAME' => $character['ALTNAME'],
                'JAPANESE' => $character['JAPANESE'],
                'DETAIL_TEXT' => $character['DESCRIPTION'],
                'SEYU' => $character['SEYU'],
                'ANIMES' => $character['ANIMES'],
                'MANGAS' => $character['MANGAS'],
            ];
        }
    }

    protected function rebaseScreenshots(&$screenshots)
    {
        foreach ($screenshots as &$screenshot) {
            $screenshot = Request::buildURL([self::$url,$screenshot['ORIGINAL']]);
        }
    }

    protected function addCollect(string $type, array $ids)
    {
        foreach ($ids as $id) {
            if (in_array($id,$this->collects[$type])) continue;
            $this->collects[$type][] = $id;
        }
    }

    protected function expand(string $type, array &$data)
    {
        $urls = [];

        foreach ($data as $id) {
            if ($this->isExpand($type, $id)) {

            } else {
                $urls[$id] = Request::buildURL([self::$url, self::$api, strtolower($type), $id]);
            }

        }

        $request = new Request();
        $request->addToAsyncQueue($urls);
        $request->initAsyncRequest();
        $data = $request->getResult();
        Core::keysToUpperCase($data);

        $this->addExpand($type, $data);
    }

    protected function addExpand(string $type, array $data)
    {
        foreach ($data as $id => $value) {
            if ($this->isExpand($type, $id)) continue;
            $this->expand[$type][$id] = $value;
        }
    }

    protected function getExpand(string $type, int $id): array
    {
        return $this->expand[$type][$id] ?: [];
    }

    protected function isExpand(string $type, int $id): bool
    {
        return (bool) $this->expand[$type][$id];
    }
}