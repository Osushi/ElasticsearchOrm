<?php

namespace Osushi\ElasticsearchOrm;

use Osushi\ElasticsearchOrm\Model;

class Query
{
    private $connection;

    private $id;

    private $index;

    private $type;

    private $mappings = [];

    private $model;

    public function __construct($connection = null)
    {
        $this->connection = $connection;
    }

    public function index(string $index)
    {
        $this->index = $index;
        return $this;
    }

    public function type(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function mappings(array $mappings = [])
    {
        $this->mappings = $mappings;
        return $this;
    }

    public function id($id = false)
    {
        $this->id = $id;
        return $this;
    }

    public function setModel(Model $model)
    {
        $this->model = $model;
        return $this;
    }

    public function create($callback = false)
    {
        $index = new Index($this->index, $callback);
        $index->setConnection($this->connection);
        $index->mappings($this->mappings);

        return $index->create();
    }

    public function drop()
    {
        $index = new Index($this->index);
        $index->setConnection($this->connection);

        return $index->drop();
    }

    public function exists()
    {
        $index = new Index($this->index);
        $index->setConnection($this->connection);

        return $index->exists();
    }

    public function insert(array $attributes, $id = null)
    {
        if ($id) {
            $this->id($id);
        }

        $params = [
            'body' => $attributes,
        ];

        if ($this->index) {
            $params['index'] = $this->index;
        }
        if ($this->type) {
            $params['type'] = $this->type;
        }
        if ($this->id) {
            $params['id'] = $this->id;
        }

        return (object) $this->connection->index($params);
    }

    public function __call(string $method, $parameters)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$parameters);
        } else {
            $method = 'scope' . ucfirst($method);
            if (method_exists($this->model, $method)) {
                $parameters = array_merge([$this], $parameters);
                $this->model->$method(...$parameters);
                return $this;
            }
        }

        throw new \Exception("Missing to find `" . $method . "` method");
    }
}
