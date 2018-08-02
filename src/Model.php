<?php

namespace Osushi\ElasticsearchOrm;

abstract class Model
{
    protected $connection;

    protected $index;

    protected $type;

    protected $mappings = [];

    private $attributes = [];

    private $fields = [];

    private $inner_hits = [];

    private $excepts = ['_id'];

    private $exists = false;

    private $_id;

    public function __construct($attributes = [], $exists = false)
    {
        $this->fill($attributes);
        $this->exists = $exists;
        $this->connection = $this->getConnection();
    }

    public function fill(array $attributes)
    {
        $this->attributes = $attributes;
        return $this;
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

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setInnerHits(string $name, Collection $collection)
    {
        $this->inner_hits[$name] = $collection;
    }

    public function getInnerHits()
    {
        return $this->inner_hits;
    }

    public function getInnerHit(string $name)
    {
        if (array_key_exists($name, $this->inner_hits)) {
            return $this->inner_hits[$name];
        }
        return false;
    }

    public function newQuery()
    {
        $connection = new Connection();
        $query = $connection->connection($this->getConnection())->setModel($this);
        if ($index = $this->index) {
            $query->index($index);
        }
        if ($type = $this->type) {
            $query->type($type);
        }
        if ($type = $this->mappings) {
            $query->mappings($mappings);
        }
        return $query;
    }

    public function save()
    {
        $fields = array_except($this->attributes, $this->excepts);

        if ($this->exists) {
            $this->newQuery()->id($this->_id)->update($fields);
        } else {
            $created = $this->newQuery();
            $created = $created->insert($fields, $this->_id);
            $this->attributes['_id'] = $this->_id = $created->_id;
            $this->exists = true;
        }
        return $this;
    }

    public function delete()
    {
        if (!$this->exists) {
            return false;
        }
        $this->newQuery()->id($this->_id)->delete();
        $this->exists = false;
        return $this;
    }

    public function toArray()
    {
        $attributes = [];
        foreach ($this->attributes as $name => $value) {
            $attributes[$name] = $this->attributes[$name];
        }
        return array_except($this->attributes, $this->excepts);
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

    public function __isset(string $name)
    {
        return array_key_exists($name, $this->attributes);
    }

    public function __call(string $method, $parameters)
    {
        return $this->newQuery()->$method(...$parameters);
    }
}
