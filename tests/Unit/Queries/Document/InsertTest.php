<?php

namespace Tests\Unit\Queries\Document;

use Osushi\ElasticsearchOrm\Queries\Document\Insert;
use Tests\TestCase;

class InsertTest extends TestCase
{
    private $insert;

    public function setUp()
    {
        parent::setUp();

        $this->insert = new Insert();
    }

    public function testIdAndGetId()
    {
        $this->assertTrue($this->insert->id('id') instanceof Insert);
        $this->assertTrue($this->insert->getId() === 'id');
    }

    public function testIndexAndGetIndex()
    {
        $this->assertTrue($this->insert->index('index') instanceof Insert);
        $this->assertTrue($this->insert->getIndex() === 'index');
    }

    public function testTypeAndGetType()
    {
        $this->assertTrue($this->insert->type('type') instanceof Insert);
        $this->assertTrue($this->insert->getType() === 'type');
    }

    public function testRefreshAndGetRefresh()
    {
        $this->assertTrue($this->insert->refresh(true) instanceof Insert);
        $this->assertTrue($this->insert->getRefresh());
    }

    public function testAttributesAndGetAttributes()
    {
        $this->assertTrue($this->insert->attributes(['field' => 'dummy']) instanceof Insert);
        $this->assertTrue($this->insert->getAttributes() === ['field' => 'dummy']);
    }

    public function testBuild()
    {
        $this->insert->id('id')
            ->index('index')
            ->type('type')
            ->refresh(true)
            ->attributes(['field' => 'dummy']);

        $this->assertEquals([
            'index' => 'index',
            'type' => 'type',
            'refresh' => true,
            'id' => 'id',
            'body' => ['field' => 'dummy'],
        ], $this->insert->build());
    }
}
