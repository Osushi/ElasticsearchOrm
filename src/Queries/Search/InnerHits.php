<?php

namespace Osushi\ElasticsearchOrm\Queries\Search;

use Osushi\ElasticsearchOrm\Queries\Query;

class InnerHits implements Query
{
    private $name;

    private $take = 3;

    private $skip = 0;

    private $sort = [];

    private $select = [];

    private $buffers = [];

    public function name(
        string $name
    ) {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function take(
        int $take
    ) {
        $this->take = $take;
        return $this;
    }

    public function getTake()
    {
        return $this->take;
    }

    public function skip(
        int $skip
    ) {
        $this->skip = $skip;
        return $this;
    }

    public function getSkip()
    {
        return $this->skip;
    }

    public function orderBy(
        string $field,
        string $sortType = 'asc'
    ) {
        $this->sort[] = [
            $field => $sortType
        ];
        return $this;
    }

    public function getOrderBy()
    {
        return $this->sort;
    }

    public function select()
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            if (is_array($arg)) {
                $this->select = array_merge(
                    $this->select,
                    $arg
                );
            } else {
                $this->select[] = $arg;
            }
        }

        return $this;
    }

    public function getSelect()
    {
        return $this->select;
    }

    public function add()
    {
        $this->buffers[] = [
            'name' => $this->getName(),
            'size' => $this->getTake(),
            'from' => $this->getSkip(),
            'sort' => $this->getOrderBy(),
            '_source' => $this->getSelect(),
        ];

        $this->reset();
    }

    protected function reset()
    {
        $this->name = null;
        $this->take = 3;
        $this->skip = 0;
        $this->sort = [];
        $this->select = [];
    }

    public function build(): array
    {
        return $this->buffers;
    }
}
