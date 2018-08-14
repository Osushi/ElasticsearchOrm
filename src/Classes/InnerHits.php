<?php

namespace Osushi\ElasticsearchOrm\Classes;

class InnerHits
{
    private $name;

    private $take;

    private $skip;

    private $sort = [];

    private $source = [];

    private $build = [];

    public function name(string $name)
    {
        $this->name = $name;
        return $this;
    }

    public function take(int $take)
    {
        $this->take = $take;
        return $this;
    }

    public function skip(int $skip)
    {
        $this->skip = $skip;
        return $this;
    }

    public function orderBy(string $field, string $sortType = 'asc')
    {
        $this->sort = [$field => $sortType];
        return $this;
    }

    public function select()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $this->source = array_merge($this->source, $arg);
            } else {
                $this->source[] = $arg;
            }
        }
        return $this;
    }

    public function build()
    {
        $build = $this->build;
        $this->build = [];

        return $build;
    }

    public function add()
    {
        return $this->action('add');
    }

    public function action(string $action)
    {
        array_push($this->build, array_filter([
            'name' => $this->name,
            'size' => $this->take,
            'from' => $this->skip,
            'sort' => $this->sort,
            '_source' => $this->source,
        ]));

        $this->reset();

        return true;
    }

    public function reset()
    {
        $this->name = null;
        $this->take = null;
        $this->skip = null;
        $this->sort = [];
    }
}
