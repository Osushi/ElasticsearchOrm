<?php

namespace Osushi\ElasticsearchOrm\Queries\Document;

use Osushi\ElasticsearchOrm\Queries\Query;

class Delete implements Query
{
    private $id;

    private $index;

    private $type;

    private $refresh = false;

    private $conditions = [];

    public function id(
        ?string $id
    ) {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

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

    public function refresh(
        $refresh
    ) {
        $this->refresh = $refresh;
        return $this;
    }

    public function getRefresh()
    {
        return $this->refresh;
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

        if ($refresh = $this->getRefresh()) {
            $params['refresh'] = $refresh;
        }

        if ($id = $this->getId()) {
            $params['id'] = $id;
            return $params;
        }

        $conditions = $this->getConditions();
        if (count($conditions)) {
            $params['body'] = $conditions;
        }

        return $params;
    }
}
