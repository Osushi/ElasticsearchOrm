<?php

namespace Osushi\ElasticsearchOrm\Queries\Aggregation;

use Osushi\ElasticsearchOrm\Queries\Query;

class Aggregation implements Query
{
    private $name;

    private $nest;

    private $aggregations = [];

    public function __construct(
        string $name,
        ?string $nest = null
    ) {
        $this->name = $name;
        $this->nest = $nest;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getNest()
    {
        return $this->nest;
    }

    public function aggs(
        string $name,
        \Closure $callback
    ) {
        return $this->aggregation($name, $callback);
    }

    public function aggregation(
        string $name,
        \Closure $callback
    ) {
        $aggregation = new static($name, $this->getName().'.aggregations.'.$name);
        $callback($aggregation);

        array_set(
            $this->aggregations,
            $aggregation->getNest(),
            $aggregation->build()[$name]
        );
    }

    public function getAggregations()
    {
        return $this->aggregations;
    }

    public function build(): array
    {
        $aggregations = $this->getAggregations();
        $this->aggregations = [];

        return $aggregations;
    }

    public function terms(
        string $field,
        int $size = 10
    ) {
        $this->aggregations[$this->getName()]['terms'] = [
            'field' => $field,
            'size' => $size,
        ];
    }

    public function valueCount(
        string $field
    ) {
        $this->aggregations[$this->getName()]['value_count'] = [
            'field' => $field,
        ];
    }

    public function cardinality(
        string $field
    ) {
        $this->aggregations[$this->getName()]['cardinality'] = [
            'field' => $field,
        ];
    }

    public function min(
        string $field
    ) {
        $this->aggregations[$this->getName()]['min'] = [
            'field' => $field,
        ];
    }

    public function max(
        string $field
    ) {
        $this->aggregations[$this->getName()]['max'] = [
            'field' => $field,
        ];
    }

    public function topHits(
        array $sorts,
        array $columns = ['*'],
        int $size = 10,
        int $from = 0
    ) {
        $this->aggregations[$this->getName()]['top_hits'] = [
            'sort' => $sorts,
            'size' => $size,
            'from' => $from,
        ];

        if ($columns !== ['*']) {
            $this->aggregations[$this->getName()]['top_hits']['_source'] = $columns;
        }
    }
}
