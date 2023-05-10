<?php


namespace Umino\Anime;


use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Type\DateTime;

/**
 * Class InsertIntoQuery
 * @deprecated Bitrix Application class have method addMulti
 * @package Umino\Anime
 */
class InsertIntoQuery
{
    private string $table;
    private string $query = '';
    private array $fields = [];
    private array $add = [];

    public function __construct(string $table)
    {
        if (empty($table)) return null;

        $this->table = $table;
    }

    public function add(array $array): InsertIntoQuery
    {
        foreach ($array as $values) {
            foreach ($values as $name => $value) {
                if (empty($name)) continue;

                $this->fields[] = $name;
            }
        }

        $this->fields = array_unique($this->fields);

        if ($this->fields) {
            $this->add = array_merge($this->add, $array);
        }

        return $this;
    }

    private function getQueryFields(): array
    {
        return [
            '' => [
                '(',
                ', ' => $this->fields,
                ')',
            ]
        ];
    }

    private function getQueryValues(): array
    {
        $result = [];

        foreach ($this->add as $values) {
            $array = [];
            foreach ($this->fields as $name) {

                $value = $values[$name];

                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }

                $array[] = '\''.($value?:'').'\'';
            }
            $result[] = [
                '' => [
                    '(',
                    ', ' => $array,
                    ')',
                ]

            ];
        }

        return [
            ', ' => $result,
        ];
    }

    private static function queryBuilder(array $array): string
    {
        $result = '';

        foreach ($array as $aKey => $values) {
            foreach ($values as $bKey => &$value) {
                if (!is_array($value)) continue;
                $value = self::queryBuilder([$bKey=>$value]);
            }
            $result .= implode($aKey, $values);
        }
        return $result;
    }

    public function getQuery(): string
    {
        if ($this->query) return $this->query;

        $query = [
            ' ' => [
                'INSERT INTO',
                '`'.$this->table.'`',
                $this->getQueryFields(),
                'VALUES',
                $this->getQueryValues(),
            ],
        ];

        return $this->query = self::queryBuilder($query);
    }

    public function query(): Result
    {
        $result = new Result();

        $error = null;

        $connection = Application::getConnection();

        if (empty($this->add)) {
            $error = new Error('Not correctly added [add()].');
        } else if ($connection->isTableExists($this->table)) {
            $error = new Error('Table "' . $this->table . '" not exists.');
        } else {
            try {

                $connection->query($this->getQuery());
            } catch (\Exception $exception) {
                $error = new Error($exception->getMessage());
            }
        }

        if ($error) {
            $result->addError($error);
        }

        return $result;
    }
}