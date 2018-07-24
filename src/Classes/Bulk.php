<?php

namespace Osushi\ElasticsearchOrm\Classes;

use Osushi\ElasticsearchOrm\Query;

class Bulk
{
    private $query;

    private $id;

    private $index;

    private $type;

    private $body = [];

    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    public function index(string $index)
    {
        $this->index = $index;
        return $this;
    }

    public function getIndex()
    {
        return !empty($this->index) ? $this->index : $this->query->getIndex();
    }

    public function type(string $type)
    {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return !empty($this->type) ? $this->type : $this->query->getType();
    }

    public function id($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function insert(array $data)
    {
        return $this->action('index', $data);
    }

    public function action(string $action, array $data)
    {
        $spec = [
            $action => [
                '_index' => $this->getIndex(),
                '_type' => $this->getType(),
                '_id' => $this->id
            ]
        ];
        if (empty($spec[$action]['_id'])) {
            unset($spec[$action]['_id']);
        }

        $this->body['body'][] = $spec;

        if (!empty($data)) {
            $this->body['body'][] = $data;
        }

        $this->reset();

        return true;
    }

    public function reset()
    {
        $this->index = null;
        $this->type = null;
        $this->id = null;
    }
}
