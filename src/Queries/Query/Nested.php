<?php

namespace Osushi\ElasticsearchOrm\Queries\Query;

use Osushi\ElasticsearchOrm\Queries\Query;

class Nested implements Query
{
    const QUERY_OPTIONS = [
        'minimum_should_match'
    ];

    const OPERATORS = [
        '=', '!=', '>', '>=', '<', '<=',
        'like'
    ];

    private $mode;

    private $filter = [];

    private $must = [];

    private $should = [];

    private $mustnot = [];

    private $queryOptions = [];

    public function mode(
        string $mode
    ) {
        $this->mode = $mode;
        return $this;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function getMust()
    {
        return $this->must;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function getShould()
    {
        return $this->should;
    }

    public function getMustNot()
    {
        return $this->mustnot;
    }

    public function getQueryOptions()
    {
        return $this->queryOptions;
    }

    public function build(): array
    {
        $conditions = [];
        if (count($this->must)) {
            $conditions['bool']['must'] = $this->getMust();
        }

        if (count($this->filter)) {
            $conditions['bool']['filter'] = $this->getFilter();
        }

        if (count($this->should)) {
            $conditions['bool']['should'] = $this->getShould();
        }

        if (count($this->mustnot)) {
            $conditions['bool']['must_not'] = $this->getMustNot();
        }

        if (count($this->queryOptions)) {
            foreach ($this->getQueryOptions() as $key => $value) {
                $conditions['bool'][$key] = $value;
            }
        }

        $this->mode = null;
        $this->must = [];
        $this->filter = [];
        $this->should = [];
        $this->mustnot = [];
        $this->queryOptions = [];

        return $conditions;
    }

    public function match(
        string $name,
        string $operator = '=',
        $value = null
    ) {
        $this->query('must', $name, $operator, $value);

        return $this;
    }

    public function where(
        string $name,
        string $operator = '=',
        $value = null
    ) {
        $this->query('filter', $name, $operator, $value);

        return $this;
    }

    public function orWhere(
        string $name,
        string $operator = '=',
        $value = null
    ) {
        $this->query('should', $name, $operator, $value);

        return $this;
    }

    public function notWhere(
        string $name,
        string $operator = '=',
        $value = null
    ) {
        $this->query('mustnot', $name, $operator, $value);

        return $this;
    }

    protected function query(
        string $occur,
        string $name,
        string $operator = '=',
        $value = null
    ) {
        if (!$this->isOperator($operator)) {
            $value = $operator;
            $operator = '=';
        }

        if ($operator === '=') {
            $this->$occur[] = [
                'term' => [
                    $name => $value
                ],
            ];
        }

        if ($operator === '>') {
            $this->$occur[] = [
                'range' => [
                    $name => ['gt' => $value],
                ],
            ];
        }

        if ($operator === '>=') {
            $this->$occur[] = [
                'range' => [
                    $name => ['gte' => $value],
                ],
            ];
        }

        if ($operator === '<') {
            $this->$occur[] = [
                'range' => [
                    $name => ['lt' => $value],
                ],
            ];
        }

        if ($operator === '<=') {
            $this->$occur[] = [
                'range' => [
                    $name => ['lte' => $value],
                ],
            ];
        }

        if ($operator === 'like') {
            $this->$occur[] = [
                'match' => [
                    $name => $value,
                ],
            ];
        }

        return $this;
    }

    public function matchBetween(
        string $name,
        array $range,
        array $equals = [false, false]
    ) {
        $this->between('must', $name, $range, $equals);

        return $this;
    }

    public function whereBetween(
        string $name,
        array $range,
        array $equals = [false, false]
    ) {
        $this->between('filter', $name, $range, $equals);

        return $this;
    }

    public function orWhereBetween(
        string $name,
        array $range,
        array $equals = [false, false]
    ) {
        $this->between('should', $name, $range, $equals);

        return $this;
    }

    public function notWhereBetween(
        string $name,
        array $range,
        array $equals = [false, false]
    ) {
        $this->between('mustnot', $name, $range, $equals);

        return $this;
    }

    protected function between(
        string $occur,
        string $name,
        array $range,
        array $equals
    ) {
        $g = 'gt';
        $l = 'lt';
        if ($equals[0]) {
            $g = 'gte';
        }
        if ($equals[1]) {
            $l = 'lte';
        }

        $this->$occur[] = [
            'range' => [
                $name => [$g => $range[0], $l => $range[1]],
            ]
        ];

        return $this;
    }

    public function matchIn(
        string $name,
        array $value
    ) {
        $this->must[] = [
            'terms' => [
                $name => $value,
            ],
        ];
        return $this;
    }

    public function whereIn(
        string $name,
        array $value
    ) {
        $this->filter[] = [
            'terms' => [
                $name => $value,
            ],
        ];
        return $this;
    }

    public function orWhereIn(
        string $name,
        array $value
    ) {
        $this->should[] = [
            'terms' => [
                $name => $value,
            ],
        ];
        return $this;
    }

    public function notWhereIn(
        string $name,
        array $value
    ) {
        $this->mustnot[] = [
            'terms' => [
                $name => $value,
            ],
        ];
        return $this;
    }

    public function queryOption(
        string $name,
        $value = null
    ) {
        if ($this->isQueryOption($name)) {
            $this->queryOptions[$name] = $value;
        }
        return $this;
    }

    protected function isOperator(
        string $operator
    ) {
        if (in_array($operator, self::OPERATORS)) {
            return true;
        }
        return false;
    }

    protected function isQueryOption(
        string $name
    ) {
        if (in_array($name, self::QUERY_OPTIONS)) {
            return true;
        }
        return false;
    }
}
