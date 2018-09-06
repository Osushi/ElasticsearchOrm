<?php

namespace Tests\Unit\Queries\Index;

use Osushi\ElasticsearchOrm\Queries\Index\Exist;
use Tests\TestCase;

class ExistTest extends TestCase
{
    private $exist;

    public function setUp()
    {
        parent::setUp();

        $this->exist = new Exist();
    }

    public function testIndexAndGetIndex()
    {
        $this->assertTrue($this->exist->index('index') instanceof Exist);
        $this->assertTrue($this->exist->getIndex() === 'index');
    }

    public function testBuild()
    {
        $this->assertEquals([
            'index' => 'index',
        ], $this->exist->index('index')->build());
    }
}
