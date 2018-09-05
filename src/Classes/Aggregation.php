<?php

namespace Osushi\ElasticsearchOrm\Classes;

class Aggregation
{
    private $name;

    private $nest = false;

    private $aggregations = [];

    public function __construct(string $name, $nest = false)
    {
        $this->name = $name;
        $this->nest = $nest;
    }

    public function terms(string $field, int $size = 10)
    {
        $this->aggregations[$this->name]['terms'] = [
            'field' => $field,
            'size' => $size,
        ];
    }

    public function valueCount(string $field)
    {
        $this->aggregations[$this->name]['value_count'] = [
            'field' => $field,
        ];
    }

    public function cardinality(string $field)
    {
        $this->aggregations[$this->name]['cardinality'] = [
            'field' => $field,
        ];
    }

    public function min(string $field)
    {
        $this->aggregations[$this->name]['min'] = [
            'field' => $field,
        ];
    }

    public function max(string $field)
    {
        $this->aggregations[$this->name]['max'] = [
            'field' => $field,
        ];
    }

    public function topHits(array $sorts, array $columns = ['*'], int $size = 10, int $from = 0)
    {
        $this->aggregations[$this->name]['top_hits'] = [
            'sort' => $sorts,
            'size' => $size,
            'from' => $from,
        ];
        if ($columns !== ['*']) {
            $this->aggregations[$this->name]['top_hits']['_source'] = $columns;
        }
    }

    public function aggs(string $name, $callback)
    {
        return $this->aggregation($name, $callback);
    }

    public function aggregation(string $name, $callback)
    {
        if (!is_callback_function($callback)) {
            throw new \Exception("Must be closure on aggregation args");
        }
        $aggregation = new static($name, $this->name.'.aggregations.'.$name);
        $callback($aggregation);

        array_set($this->aggregations, $aggregation->getNest(), $aggregation->build()[$name]);
    }

    public function build()
    {
        $aggregations = $this->aggregations;
        $this->aggregations = [];
        return $aggregations;
    }

    public function getNest()
    {
        return $this->nest;
    }
}