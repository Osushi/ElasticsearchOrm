<?php

namespace Osushi\ElasticsearchOrm\Queries\Sort;

use Osushi\ElasticsearchOrm\Queries\Query;

class Nested implements Query
{
    const OPERATORS = [
        '=', '!=', '>', '>=', '<', '<=',
        'like'
    ];

    private $version;

    private $path;

    private $filter = [];

    public function __construct(
        float $version
    ) {
        $this->version = $version;
    }

    public function path(
        string $path
    ) {
        $this->path = $path;
        return $this;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getFilter()
    {
        return $this->filter;
    }

    public function build(): array
    {
        $conditions = [];
        if (count($this->filter)) {
            if ($this->version >= 6.1) {
                if (!empty($this->getPath())) {
                    $conditions['path'] = $this->getPath();
                }
                $conditions['filter'] = $this->getFilter();
            } else {
                if (!empty($this->getPath())) {
                    $conditions['nested_path'] = $this->getPath();
                }
                $conditions['nested_filter'] = $this->getFilter();
            }
        }

        $this->path = null;
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
            $this->filter = [
                'term' => [
                    $name => $value
                ],
            ];
        }

        if ($operator === '>') {
            $this->filter = [
                'range' => [
                    $name => ['gt' => $value],
                ],
            ];
        }

        if ($operator === '>=') {
            $this->filter = [
                'range' => [
                    $name => ['gte' => $value],
                ],
            ];
        }

        if ($operator === '<') {
            $this->filter = [
                'range' => [
                    $name => ['lt' => $value],
                ],
            ];
        }

        if ($operator === '<=') {
            $this->filter = [
                'range' => [
                    $name => ['lte' => $value],
                ],
            ];
        }

        if ($operator === 'like') {
            $this->filter = [
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
