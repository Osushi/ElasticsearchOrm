<?php

namespace Tests\Unit\Queries\Index;

use Osushi\ElasticsearchOrm\Queries\Index\Drop;
use Tests\TestCase;

class DropTest extends TestCase
{
    private $drop;

    public function setUp()
    {
        parent::setUp();

        $this->drop = new Drop();
    }

    public function testIndexAndGetIndex()
    {
        $this->assertTrue($this->drop->index('index') instanceof Drop);
        $this->assertTrue($this->drop->getIndex() === 'index');
    }

    public function testBuild()
    {
        $this->assertEquals([
            'index' => 'index',
        ], $this->drop->index('index')->build());
    }
}
