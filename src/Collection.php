<?php

namespace Osushi\ElasticsearchOrm;

use Illuminate\Support\Collection as IlluminateCollection;
use Illuminate\Contracts\Support\Arrayable;

class Collection extends IlluminateCollection implements Arrayable
{
    public function toArray()
    {
        return array_map(function ($item) {
            return $item->toArray();
        }, $this->items);
    }
}
