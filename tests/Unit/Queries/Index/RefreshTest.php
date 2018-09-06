<?php

namespace Tests\Unit\Queries\Index;

use Osushi\ElasticsearchOrm\Queries\Index\Refresh;
use Tests\TestCase;

class RefreshTest extends TestCase
{
    private $refresh;

    public function setUp()
    {
        parent::setUp();

        $this->refresh = new Refresh();
    }

    public function testIndexAndGetIndex()
    {
        $this->assertTrue($this->refresh->index('index') instanceof Refresh);
        $this->assertTrue($this->refresh->getIndex() === 'index');
    }

    public function testBuild()
    {
        $this->assertEquals([
            'index' => 'index',
        ], $this->refresh->index('index')->build());
    }
}
