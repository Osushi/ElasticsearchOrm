<?php

namespace Osushi\ElasticsearchOrm\Queries\Search;

use Osushi\ElasticsearchOrm\Queries\Query;

class Count implements Query
{
    private $index;

    private $type;

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
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
        ];

        $conditions = $this->getConditions();
        if (count($conditions)) {
            unset(
                $conditions['body']['_source'],
                $conditions['body']['sort']
            );
            $params['body'] = $conditions;
        }

        return $params;
    }
}
