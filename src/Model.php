<?php

namespace Osushi\ElasticsearchOrm;

abstract class Model
{
    protected $connection;

    protected $index;

    protected $type;

    protected $mappings = [];

    protected $attributes = [];

    protected $exists = false;

    private $_id;

    public function __construct($attributes = [], $exists = false)
    {
        $this->attributes = $attributes;
        $this->exists = $exists;
        $this->connection = $this->getConnection();
    }

    public function getConnection()
    {
        return $this->connection ? $this->connection : config("elasticsearch.default");
    }

    public function setConnection(string $connection)
    {
        $this->connection = $connection;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function setIndex(string $index)
    {
        $this->index = $index;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType(string $type)
    {
        $this->type = $type;
    }

    public function getMappings()
    {
        return $this->mappings;
    }

    public function setMappings(array $mappings)
    {
        $this->mappings = $mappings;
    }

    public function getId()
    {
        return $this->_id;
    }

    protected function newQuery()
    {
        $connection = new Connection();
        $query = $connection->connection($this->getConnection())->setModel($this);
        if ($index = $this->getIndex()) {
            $query->index($index);
        }
        if ($type = $this->getType()) {
            $query->type($type);
        }
        if ($type = $this->getMappings()) {
            $query->mappings($mappings);
        }
        return $query;
    }

    public function save()
    {
        $fields = array_except($this->attributes, ['_id']);

        if ($this->exists) {
            $this->newQuery()->id($this->getId())->update($fields);
        } else {
            $query = $this->newQuery();
            $query->insert($fields, $this->getId());
            $this->exists = true;
        }
        return $this;
    }

    public function __set(string $name, $value)
    {
        if ($name == '_id') {
            $this->_id = $value;
        }
        $this->attributes[$name] = $value;
    }

    public function __get(string $name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        return null;
    }

    public function __call(string $method, $parameters)
    {
        return $this->newQuery()->$method(...$parameters);
    }
}
