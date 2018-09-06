<?php

namespace Osushi\ElasticsearchOrm\Queries\Search;

use Osushi\ElasticsearchOrm\Queries\Query;

class ClearScroll implements Query
{
    private $scrollId;

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

    public function build(): array
    {
        return [
            'scroll_id' => $this->getScrollId(),
        ];
    }
}
