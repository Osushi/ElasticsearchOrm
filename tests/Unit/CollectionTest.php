<?php

namespace Tests\Unit;

use Tests\TestCase;
use Osushi\ElasticsearchOrm\Collection;
use Osushi\ElasticsearchOrm\Model;

class CollectionExample extends Model
{
    protected $index = 'index';
    protected $type = 'type';
}

class CollectionTest extends TestCase
{
    private $collection;

    public function setUp()
    {
        parent::setUp();

        $this->collection = new Collection([
            new CollectionExample([
                'field' => 'dummy'
            ]),
            new CollectionExample([
                'field' => 'dummy'
            ]),
        ]);
    }

    public function testToArray()
    {
        $this->assertEquals(
            [
                [
                'field' => 'dummy'
                ],
                [
                'field' => 'dummy'
                ],
            ],
            $this->collection->toArray()
        );
    }
}
