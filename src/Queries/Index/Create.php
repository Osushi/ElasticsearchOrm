<?php

namespace Osushi\ElasticsearchOrm\Queries\Index;

use Osushi\ElasticsearchOrm\Queries\Query;

class Create implements Query
{
    private $callback;

    private $index;

    private $shards = 5;

    private $replicas = 0;

    private $mappings = [];

    public function __construct(
        $callback = null
    ) {
        $this->callback = $callback;
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

    public function mappings(
        array $mappings = []
    ) {
        $this->mappings = $mappings;
        return $this;
    }

    public function getMappings()
    {
        return $this->mappings;
    }

    public function shards(
        int $shards
    ) {
        $this->shards = $shards;
        return $this;
    }

    public function getShards()
    {
        return $this->shards;
    }

    public function replicas(
        int $replicas
    ) {
        $this->replicas = $replicas;
        return $this;
    }

    public function getReplicas()
    {
        return $this->replicas;
    }

    public function build(): array
    {
        if (is_callback_function($this->callback)) {
            $callback = $this->callback;
            $callback($this);
        }

        $params = [
            'index' => $this->getIndex(),
            'body' => [
                'settings' => [
                    'number_of_shards' => $this->getShards(),
                    'number_of_replicas' => $this->getReplicas(),
                ],
            ],
        ];

        if (count($this->mappings)) {
            $params['body']['mappings'] = $this->getMappings();
        }

        return $params;
    }
}
