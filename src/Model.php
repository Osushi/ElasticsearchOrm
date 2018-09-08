<?php

namespace Osushi\ElasticsearchOrm;

use Osushi\ElasticsearchOrm\Collection;

class Model
{
    const EXCEPTS = [
        '_id'
    ];

    private $attributes = [];

    protected $connection;

    protected $index;

    protected $type;

    protected $mappings = [];

    private $_id;

    private $fields = [];

    private $inner_hits = [];

    private $isExists = false;

    public function __construct(
        array $attributes = [],
        bool $isExists = false
    ) {
        $this->fill($attributes);
        $this->setConnection($this->getConnection());
        $this->setIsExists($isExists);
    }

    public function fill(
        array $attributes
    ) {
        $this->attributes = $attributes;
        if (array_key_exists('_id', $this->attributes)) {
            $this->setId($this->attributes['_id']);
        }
        return $this;
    }

    public function getConnection()
    {
        return $this->connection ? $this->connection : config('elasticsearch.default');
    }

    public function setConnection(
        string $connection
    ) {
        $this->connection = $connection;
    }

    public function setIndex(
        string $index
    ) {
        $this->index = $index;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function setType(
        string $type
    ) {
        $this->type = $type;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setMappings(
        array $mappings
    ) {
        $this->mappings = $mappings;
    }

    public function getMappings()
    {
        return $this->mappings;
    }

    public function setId(
        string $_id
    ) {
        $this->_id = $_id;
    }

    public function getId()
    {
        return $this->_id;
    }

    public function setFields(
        array $fields
    ) {
        $this->fields = $fields;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setInnerHits(
        string $name,
        Collection $collection
    ) {
        $this->inner_hits[$name] = $collection;
    }

    public function getInnerHits()
    {
        return $this->inner_hits;
    }

    public function getInnerHit(
        string $name
    ) {
        if (array_key_exists($name, $this->inner_hits)) {
            return $this->inner_hits[$name];
        }
        return false;
    }

    public function setIsExists(
        bool $isExists
    ) {
        $this->isExists = $isExists;
    }

    public function getIsExists()
    {
        return $this->isExists;
    }

    public function toArray()
    {
        return array_except($this->attributes, self::EXCEPTS);
    }

    public function __set(
        string $name,
        $value
    ) {
        if ($name == '_id') {
            $this->setId($value);
        }
        $this->attributes[$name] = $value;
    }

    public function __get(
        string $name
    ) {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }
        return;
    }

    public function __isset(
        string $name
    ) {
        return array_key_exists($name, $this->attributes);
    }

    public function __call(
        string $method,
        $parameters
    ) {
        return $this->newQuery()->$method(...$parameters);
    }

    public function newQuery()
    {
        $connection = new Connection();
        $builder = $connection
                 ->connect($this->getConnection())
                 ->setModel($this);

        if ($index = $this->getIndex()) {
            $builder->index($index);
        }
        if ($type = $this->getType()) {
            $builder->type($type);
        }
        if ($mappings = $this->getMappings()) {
            $builder->mappings($mappings);
        }

        return $builder;
    }

    public function save(
        $refresh = false
    ) {
        $fields = array_except($this->attributes, self::EXCEPTS);

        $query = $this->newQuery();
        if ($refresh) {
            $query->refresh($refresh);
        }
        if ($this->getIsExists()) {
            $query->update($fields, $this->getId());
        } else {
            $results = $query->insert($fields, $this->getId());
            $this->_id = $results->_id;
            $this->setIsExists(true);
        }

        return $this;
    }

    public function delete(
        $refresh = false
    ) {
        if (!$this->getIsExists()) {
            return false;
        }

        $query = $this->newQuery();
        if ($refresh) {
            $query->refresh($refresh);
        }
        $query->delete($this->getId());
        $this->setIsExists(false);

        return $this;
    }
}
