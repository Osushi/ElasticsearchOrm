<?php

namespace Tests\Unit\Queries\Search;

use Osushi\ElasticsearchOrm\Queries\Search\ClearScroll;
use Tests\TestCase;

class ClearScrollTest extends TestCase
{
    private $clearScroll;

    public function setUp()
    {
        parent::setUp();

        $this->clearScroll = new ClearScroll();
    }

    public function testScrollIdAndGetScrollId()
    {
        $this->assertTrue($this->clearScroll->scrollId('hash') instanceof ClearScroll);
        $this->assertTrue($this->clearScroll->getScrollId() === 'hash');
    }

    public function testBuild()
    {
        $actual = $this->clearScroll->scrollId('hash');

        $this->assertEquals([
            'scroll_id' => 'hash',
        ], $actual->build());
    }
}
