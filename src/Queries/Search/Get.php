<?php

namespace Osushi\ElasticsearchOrm\Queries\Search;

use Osushi\ElasticsearchOrm\Queries\Query;

class Get implements Query
{
    private $index;

    private $type;

    private $take;

    private $skip;

    private $scroll;

    private $scrollId;

    private $conditions = [];

    public function index(
        string $index
    ) {
        $this->index = $index;
        return $this;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function type(
        string $type
    ) {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
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

    public function scroll(
        string $scroll
    ) {
        $this->scroll = $scroll;
        return $this;
    }

    public function getScroll()
    {
        return $this->scroll;
    }

    public function scrollId(
        string $scrollId
    ) {
        $this->scrollId = $scrollId;
        return $this;
    }

    public function getScrollId()
    {
        return $this->scrollId;
    }

    public function conditions(
        array $conditions
    ) {
        $this->conditions = $conditions;
        return $this;
    }

    public function getConditions()
    {
        return $this->conditions;
    }

    public function build(): array
    {
        if ($this->getScrollId()) {
            return [
                'scroll' => $this->getScroll(),
                'scroll_id' => $this->getScrollId(),
            ];
        }

        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'from' => $this->getSkip(),
            'size' => $this->getTake(),
        ];

        if ($scroll = $this->getScroll()) {
            $params['scroll'] = $scroll;
        }

        $conditions = $this->getConditions();
        if (count($conditions)) {
            $params['body'] = $conditions;
        }

        return $params;
    }
}
