<?php

namespace Osushi\ElasticsearchOrm\Responses;

use Osushi\ElasticsearchOrm\Model;
use Osushi\ElasticsearchOrm\Collection;

class Response
{
    private $results;

    private $requestTypes = [];

    private $model;

    public function __construct(
        $results
    ) {
        $this->results = $results;
    }

    public function setRequestTypes(
        array $requestTypes
    ) {
        $this->requestTypes = $requestTypes;
        return $this;
    }

    public function getRequestTypes()
    {
        return $this->requestTypes;
    }

    public function setModel(
        Model $model
    ) {
        $this->model = $model;
        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function make()
    {
        switch ($this->requestTypes[0]) {
        case 'index':
            if ($this->requestTypes[1] === 'create') {
                return (object) $this->results;
            }
            if ($this->requestTypes[1] === 'drop') {
                return (object) $this->results;
            }
            if ($this->requestTypes[1] === 'exist') {
                return $this->results;
            }
            if ($this->requestTypes[1] === 'refresh') {
                return (object) $this->results;
            }
            break;
        case 'document':
            $results = (object) $this->results;
            if ($this->requestTypes[1] === 'insert') {
                if ($model = $this->getModel()) {
                    $model->setId($results->_id);
                    return $model;
                }
                return $results;
            }
            if ($this->requestTypes[1] === 'update') {
                if ($model = $this->getModel()) {
                    return $model;
                }
                return $results;
            }
            if ($this->requestTypes[1] === 'delete') {
                if ($model = $this->getModel()) {
                    return $model;
                }
                return $results;
            }
            if ($this->requestTypes[1] === 'bulk') {
                return $results;
            }
            break;
        case 'search':
            if ($this->requestTypes[1] === 'get') {
                return $this->hydrate($this->results);
            }
            if ($this->requestTypes[1] === 'clear') {
                return (object) $this->results;
            }
            if ($this->requestTypes[1] === 'count') {
                return $this->results['count'];
            }
            break;
        }

        throw new \Exception('Invalid response type');
    }

    protected function hydrate(
        array $result
    ) {
        $models = [];
        foreach ($result['hits']['hits'] as $row) {
            $model = $this->getModel();
            $model = $model ? new $model($row['_source'], true) : new Model($row['_source'], true);
            $model->setIndex($row['_index']);
            $model->setType($row['_type']);
            $model->setId($row['_id']);
            if (isset($row['fields'])) {
                $model->setFields($row['fields']);
            }
            if (isset($row['inner_hits'])) {
                foreach ($row['inner_hits'] as $name => $innerHits) {
                    $model->setInnerHits($name, $this->hydrate($innerHits));
                }
            }
            $models[] = $model;
        }

        $collection = new Collection($models);
        $collection->total = isset($result['hits']['total']) ? $result['hits']['total'] : null;
        $collection->max_score = isset($result['hits']['max_score']) ? $result['hits']['max_score'] : null;
        $collection->took = isset($result['took']) ? $result['took'] : null;
        $collection->timed_out = isset($result['timed_out']) ? $result['timed_out'] : null;
        $collection->scroll_id = isset($result['_scroll_id']) ? $result['_scroll_id'] : null;
        $collection->shards = isset($result['_shards']) ? (object) $result['_shards'] : null;
        $collection->aggregations = isset($result['aggregations']) ? (object) $result['aggregations'] : null;

        return $collection;
    }
}
