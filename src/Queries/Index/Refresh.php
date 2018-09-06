<?php

namespace Osushi\ElasticsearchOrm\Queries\Index;

use Osushi\ElasticsearchOrm\Queries\Query;

class Refresh implements Query
{
    private $index;

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

    public function build(): array
    {
        $params = [
            'index' => $this->getIndex(),
        ];

        return $params;
    }
}
