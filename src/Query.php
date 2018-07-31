<?php

namespace Osushi\ElasticsearchOrm;

use Osushi\ElasticsearchOrm\Model;
use Osushi\ElasticsearchOrm\Classes\Bulk;

class Query
{
    private $connection;

    private $id;

    private $index;

    private $type;

    private $mappings = [];

    private $model;

    private $scrollId;

    private $scroll;

    private $filter = [];

    private $must = [];

    private $source = [];

    private $body = [];

    private $take = 10;

    private $skip = 0;

    private $operators = [
        '=', '!=', '>', '>=', '<', '<=',
        'like',
    ];

    public function __construct($connection = null)
    {
        $this->connection = $connection;
    }

    public function index(string $index)
    {
        $this->index = $index;
        return $this;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function type(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function mappings(array $mappings = [])
    {
        $this->mappings = $mappings;
        return $this;
    }

    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    public function scrollId(string $scroll)
    {
        $this->scrollId = $scroll;
        return $this;
    }

    private function getBody()
    {
        if (count($this->source)) {
            $source = array_key_exists('_source', $this->body) ? $this->body['_source'] : [];
            $this->body['_source'] = array_unique(array_merge($source, $this->source));
        }

        if (count($this->must)) {
            $body['query']['bool']['must'] = $this->must;
        }

        if (count($this->filter)) {
            $this->body['query']['bool']['filter'] = $this->filter;
        }

        return $this->body;
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

    public function bulk($data)
    {
        $bulk = new Bulk($this);
        if (is_callback_function($data)) {
            $data($bulk);
        } else {
            $bulk = new Bulk($this);
            foreach ($data as $value) {
                if (array_key_exists('_id', $value)) {
                    $bulk->id($value['_id']);
                    unset($value['_id']);
                }
                $bulk->insert($value);
            }
        }
        $params = $bulk->getBody();

        return (object) $this->connection->bulk($params);
    }

    public function scroll(string $scroll)
    {
        $this->scroll = $scroll;
        return $this;
    }

    public function take(int $take)
    {
        $this->take = $take;
        return $this;
    }

    public function skip(int $skip)
    {
        $this->skip = $skip;
        return $this;
    }

    public function select()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $this->source = array_merge($this->source, $arg);
            } else {
                $this->source[] = $arg;
            }
        }
        return $this;
    }

    public function get()
    {
        $result = $this->request();
        return $this->hydrate($result);
    }

    public function where(string $name, string $operator = '=', $value = null)
    {
        if (!$this->isOperator($operator)) {
            $value = $operator;
            $operator = '=';
        }

        if ($operator == '=') {
            if ($name == '_id') {
                return $this->id($value);
            }
            $this->filter[] = ['term' => [$name => $value]];
        }

        if ($operator == '>') {
            $this->filter[] = ['range' => [$name => ['gt' => $value]]];
        }

        if ($operator == '>=') {
            $this->filter[] = ['range' => [$name => ['gte' => $value]]];
        }

        if ($operator == '<') {
            $this->filter[] = ['range' => [$name => ['lt' => $value]]];
        }

        if ($operator == '<=') {
            $this->filter[] = ['range' => [$name => ['lte' => $value]]];
        }

        if ($operator == 'like') {
            $this->must[] = ['match' => [$name => $value]];
        }

        return $this;
    }

    public function whereIn(string $name, array $value)
    {
        $this->filter[] = ['terms' => [$name => $value]];
        return $this;
    }

    public function clear()
    {
        return $this->connection->clearScroll([
            'scroll_id' => $this->scrollId,
        ]);
    }

    protected function isOperator(string $operator)
    {
        if (in_array($operator, $this->operators)) {
            return true;
        }
        return false;
    }

    private function request()
    {
        if ($this->scrollId) {
            $result = $this->connection->scroll([
                'scroll' => $this->scroll,
                'scroll_id' => $this->scrollId,
            ]);
        } else {
            $result = $this->connection->search($this->query());
        }
        return $result;
    }

    private function query()
    {
        $query = [];

        $query['index'] = $this->index;

        if ($this->type) {
            $query['type'] = $this->type;
        }

        $query['body'] = $this->getBody();
        $query['from'] = $this->skip;
        $query['size'] = $this->take;

        if ($this->scroll) {
            $query['scroll'] = $this->scroll;
        }

        return $query;
    }

    private function hydrate(array $result)
    {
        $models = [];
        foreach ($result['hits']['hits'] as $row) {
            $model = $this->model ? new $this->model($row['_source'], true) : new Model($row['_source'], true);
            $model->setConnection($model->getConnection());
            $model->setIndex($row['_index']);
            $model->setType($row['_type']);
            $model->_id = $row['_id'];

            $models[] = $model;
        }

        $collection = new Collection($models);
        $collection->total = $result['hits']['total'];
        $collection->max_score = $result['hits']['max_score'];
        $collection->took = $result['took'];
        $collection->timed_out = $result['timed_out'];
        $collection->scroll_id = isset($result['_scroll_id']) ? $result['_scroll_id'] : null;
        $collection->shards = (object) $result['_shards'];

        return $collection;
    }

    public function raw()
    {
        return $this->connection;
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
