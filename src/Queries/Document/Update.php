<?php

namespace Osushi\ElasticsearchOrm\Queries\Document;

use Osushi\ElasticsearchOrm\Queries\Query;

class Update implements Query
{
    private $id;

    private $index;

    private $type;

    private $refresh = false;

    private $attributes = [];

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

    public function attributes(
        array $attributes
    ) {
        $this->attributes = $attributes;
        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function build(): array
    {
        $params = [
            'index' => $this->getIndex(),
            'type' => $this->getType(),
            'body' => [
                'doc' => $this->getAttributes(),
            ],
        ];

        if ($id = $this->getId()) {
            $params['id'] = $id;
        }

        if ($refresh = $this->getRefresh()) {
            $params['refresh'] = $refresh;
        }

        return $params;
    }
}
