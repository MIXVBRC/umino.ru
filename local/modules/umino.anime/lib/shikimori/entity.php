<?php


namespace Umino\Anime\Shikimori;


use CUtil;

class Entity
{
    public static array $data = [];
    protected static array $loads = [];

    protected static bool $md5Id = false;

    protected string $id;
    protected string $xmlId;
    protected array $fields = [];

    public function __construct(string $id, array $fields = [])
    {
        $this->setId($id);
        $this->setXmlId(static::buildXmlId($id, static::getName()));
        if (empty($fields)) {
            static::addLoad($this->getId());
            $fields = static::load()[$this->getId()] ?: [];
        }
        $fields = $this->rebase($fields);

        $this->setFields($fields);
        $this->addData();
    }

    public static function create(string $id, array $fields = []): object
    {
        $id = static::buildId($id);

        if ($object = static::getById($id)) {
            return $object;
        } else {
            return new static($id, $fields);
        }
    }

    protected function setId(string $id)
    {
        $this->id = $id;
    }

    protected function getId(): string
    {
        return $this->id;
    }

    protected function setXmlId(string $xmlId)
    {
        $this->xmlId = $xmlId;
    }

    protected function getXmlId(): string
    {
        return $this->xmlId;
    }

    protected static function addLoad(string $id)
    {
        static::$loads[$id] = static::getUrl([$id]);
    }

    protected static function load(): array
    {
        $result = static::loadFromDataBase();
        if (static::$loads) {
            $result += static::loadFromRequest();
        }
        static::$loads = [];
        return $result;
    }

    protected static function loadFromDataBase(): array
    {
        $result = [];
        foreach (array_keys($result) as $id) {
            unset(static::$loads[$id]);
        }
        return $result;
    }

    protected static function loadFromRequest(): array
    {
        $request = new Request();
        $request->addToAsyncQueue(self::$loads);
        $request->initAsyncRequest();
        $result = $request->getResult();
        foreach (array_keys($result) as $id) {
            unset(static::$loads[$id]);
        }
        static::$loads = [];
        return $result;
    }

    protected static function getUrl(array $additional = []): string
    {
        return Request::buildApiURL(array_merge([static::getName()], $additional));
    }

    protected static function getClass(): string
    {
        return get_called_class();
    }

    protected static function getName(): string
    {
        $explode = explode('\\', static::getClass());
        return strtolower(end($explode));
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    protected function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    protected function rebase(array $fields): array
    {
        return $fields;
    }

    protected function addData()
    {
        static::$data[get_called_class()][$this->id] = $this;
    }

    protected static function rearrange(array $array): array
    {
        $result = [];

        foreach ($array as $item) {
            if (is_array($item)) {
                $result[] += static::rearrange($item);
            } else {
                $result[] = $item;
            }
        }

        return $result;
    }

    protected static function buildId(...$params): string
    {
        $result = static::rearrange($params);

        $result = implode('', $result);

        if (static::$md5Id) return md5($result);

        return $result;
    }

    protected static function buildXmlId(...$params): string
    {
        $result = static::rearrange($params);

        $result = implode('', $result);

        return md5($result);
    }

    protected static function buildCode(...$params): string
    {
        $result = static::rearrange($params);

        $result = implode('-', $result);

        return Cutil::translit(
            $result,
            'ru',
            [
                'max_len' => 255,
                'change_case' => 'L',
                'replace_space' => '-',
                'replace_other' => '-',
                'delete_repeat_replace ' => true,
                'safe_chars' => '',
            ]
        );
    }

    public static function getById(string $id): ?object
    {
        return static::$data[get_called_class()][$id];
    }

    public static function getByIds(array $ids): array
    {
        $result = [];
        $map = [];

        foreach ($ids as $key => $id) {
            if ($object = static::getById($id)) {
                $result[$key] = $object;
            } else {
                static::addLoad($id);
                $map[$key] = $id;
            }
        }

        foreach (static::load() as $id => $fields) {
            $result[array_search($id, $map)] = static::create($id, $fields);
        }
        ksort($result);
        return $result;
    }

    public static function getData():array
    {
        return self::$data;
    }
}