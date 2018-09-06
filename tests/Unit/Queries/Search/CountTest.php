<?php

namespace Tests\Unit\Queries\Search;

use Osushi\ElasticsearchOrm\Queries\Search\Count;
use Tests\TestCase;

class CountTest extends TestCase
{
    private $count;

    public function setUp()
    {
        parent::setUp();

        $this->count = new Count();
    }

    public function testIndexAndGetIndex()
    {
        $this->assertTrue($this->count->index('index') instanceof Count);
        $this->assertTrue($this->count->getIndex() === 'index');
    }

    public function testTypeAndGetType()
    {
        $this->assertTrue($this->count->type('type') instanceof Count);
        $this->assertTrue($this->count->getType() === 'type');
    }

    public function testConditionsAndGetConditions()
    {
        $this->assertTrue($this->count->conditions(['field' => 'dummy']) instanceof Count);
        $this->assertTrue($this->count->getConditions() === ['field' => 'dummy']);
    }

    public function testBuild()
    {
        $actual = $this->count->index('index')
                ->type('type')
                ->conditions(['field' => 'dummy']);

        $this->assertEquals([
            'index' => 'index',
            'type' => 'type',
            'body' => [
                'field' => 'dummy',
            ],
        ], $actual->build());
    }
}
