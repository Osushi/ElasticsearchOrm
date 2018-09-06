<?php

namespace Tests\Unit\Queries\Search;

use Osushi\ElasticsearchOrm\Queries\Search\Get;
use Tests\TestCase;

class GetTest extends TestCase
{
    private $get;

    public function setUp()
    {
        parent::setUp();

        $this->get = new Get();
    }

    public function testIndexAndGetIndex()
    {
        $this->assertTrue($this->get->index('index') instanceof Get);
        $this->assertTrue($this->get->getIndex() === 'index');
    }

    public function testTypeAndGetType()
    {
        $this->assertTrue($this->get->type('type') instanceof Get);
        $this->assertTrue($this->get->getType() === 'type');
    }

    public function testTakeAndGetTake()
    {
        $this->assertTrue($this->get->take(10) instanceof Get);
        $this->assertTrue($this->get->getTake() === 10);
    }

    public function testSkipAndGetSkip()
    {
        $this->assertTrue($this->get->skip(10) instanceof Get);
        $this->assertTrue($this->get->getSkip() === 10);
    }

    public function testScrollAndGetScroll()
    {
        $this->assertTrue($this->get->scroll('2m') instanceof Get);
        $this->assertTrue($this->get->getScroll() === '2m');
    }

    public function testScrollIdAndGetScrollId()
    {
        $this->assertTrue($this->get->scrollId('hash') instanceof Get);
        $this->assertTrue($this->get->getScrollId() === 'hash');
    }

    public function testConditionsAndGetConditions()
    {
        $this->assertTrue($this->get->conditions(['field' => 'dummy']) instanceof Get);
        $this->assertTrue($this->get->getConditions() === ['field' => 'dummy']);
    }

    public function testBuild()
    {
        $actual = $this->get->index('index')
                ->type('type')
                ->take(30)
                ->skip(10)
                ->scroll('1m')
                ->conditions(['field' => 'dummy']);

        $this->assertEquals([
            'index' => 'index',
            'type' => 'type',
            'from' => 10,
            'size' => 30,
            'scroll' => '1m',
            'body' => [
                'field' => 'dummy',
            ],
        ], $actual->build());

        $actual = $this->get->scroll('1m')
                ->scrollId('hash');

        $this->assertEquals([
            'scroll' => '1m',
            'scroll_id' => 'hash',
        ], $actual->build());
    }
}
