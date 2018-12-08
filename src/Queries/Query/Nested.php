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

    private $must = [];

    private $filter = [];

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

    public function build(): array
    {
        $conditions = [];
        if (count($this->must)) {
            $conditions['bool']['must'] = $this->getMust();
        }

        if (count($this->filter)) {
            $conditions['bool']['filter'] = $this->getFilter();
        }

        $this->mode = null;
        $this->must = [];
        $this->filter = [];

        return $conditions;
    }

    public function where(
        string $name,
        string $operator = '=',
        $value = null
    ) {
        if (!$this->isOperator($operator)) {
            $value = $operator;
            $operator = '=';
        }

        if ($operator === '=') {
            $this->filter[] = [
                'term' => [
                    $name => $value
                ],
            ];
        }

        if ($operator === '>') {
            $this->filter[] = [
                'range' => [
                    $name => ['gt' => $value],
                ],
            ];
        }

        if ($operator === '>=') {
            $this->filter[] = [
                'range' => [
                    $name => ['gte' => $value],
                ],
            ];
        }

        if ($operator === '<') {
            $this->filter[] = [
                'range' => [
                    $name => ['lt' => $value],
                ],
            ];
        }

        if ($operator === '<=') {
            $this->filter[] = [
                'range' => [
                    $name => ['lte' => $value],
                ],
            ];
        }

        if ($operator === 'like') {
            $this->must[] = [
                'match' => [
                    $name => $value,
                ],
            ];
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
}
