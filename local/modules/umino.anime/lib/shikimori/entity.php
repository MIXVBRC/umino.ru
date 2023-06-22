<?php


namespace Umino\Anime\Shikimori;


class Entity
{
    public static array $data = [];
    protected static array $loads = [];

    protected int $id;
    protected array $fields = [];

    public function __construct(int $id, array $fields = [])
    {
        $this->setId($id);
        $this->setFields($fields);
        $this->addData();
        return $this;
    }

    protected function setId(int $id)
    {
        $this->id = $id;
    }

    protected function getId(): int
    {
        return $this->id;
    }

    protected static function addToLoad(int $id)
    {
        static::$loads[$id] = static::getUrl([$id]);
    }

    protected static function load(): array
    {
        $request = new Request();
        $request->addToAsyncQueue(self::$loads);
        $request->initAsyncRequest();
        static::$loads = [];
        return $request->getResult();
    }

    protected static function getUrl(array $additional = []): string
    {
        return Request::buildURL(array_merge([static::getName()], $additional));
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

    protected function rebase(array $fields): array
    {
        return $fields;
    }

    protected function addData()
    {
        static::$data[get_called_class()][$this->id] = $this;
    }

    public static function getById(int $id): ?object
    {
        return static::$data[get_called_class()][$id];
    }

    protected function setFields(array $fields)
    {
        if (empty($fields)) {
            static::addToLoad($this->getId());
            $fields = static::load()[$this->getId()];
        }
        $this->fields = $this->rebase($fields);
    }

    public static function getCollection(array $ids): array
    {
        $result = [];

        foreach ($ids as $id) {
            if ($object = static::getById($id)) {
                $result[$id] = $object;
            } else {
                static::addToLoad($id);
            }
        }

        $class = static::getClass();
        foreach (static::load() as $id => $fields) {
            $result[$id] = new $class((int) $id, $fields);
        }

        return $result;
    }
}