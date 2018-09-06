<?php

namespace Osushi\ElasticsearchOrm\Queries;

interface Query
{
    public function build(): array;
}
