<?php

namespace Osushi\ElasticsearchOrm\Queries\Query;

use Osushi\ElasticsearchOrm\Queries\Query;

class Nested implements Query
{
    const OPERATORS = [
        '=', '!=', '>', '>=', '<', '<=',
        'like'
    ];

    private $mode;

    private $filter = [];

    private $must = [];

    private $should = [];

    private $mustnot = [];

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

        $this->mode = null;
        $this->must = [];
        $this->filter = [];
        $this->should = [];
        $this->mustnot = [];

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

    protected function isOperator(
        string $operator
    ) {
        if (in_array($operator, self::OPERATORS)) {
            return true;
        }
        return false;
    }
}
