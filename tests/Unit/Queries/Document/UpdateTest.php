<?php

namespace Tests\Unit\Queries\Document;

use Osushi\ElasticsearchOrm\Queries\Document\Update;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    private $update;

    public function setUp()
    {
        parent::setUp();

        $this->update = new Update();
    }

    public function testIdAndGetId()
    {
        $this->assertTrue($this->update->id('id') instanceof Update);
        $this->assertTrue($this->update->getId() === 'id');
    }

    public function testIndexAndGetIndex()
    {
        $this->assertTrue($this->update->index('index') instanceof Update);
        $this->assertTrue($this->update->getIndex() === 'index');
    }

    public function testTypeAndGetType()
    {
        $this->assertTrue($this->update->type('type') instanceof Update);
        $this->assertTrue($this->update->getType() === 'type');
    }

    public function testRefreshAndGetRefresh()
    {
        $this->assertTrue($this->update->refresh(true) instanceof Update);
        $this->assertTrue($this->update->getRefresh());
    }

    public function testAttributesAndGetAttributes()
    {
        $this->assertTrue($this->update->attributes(['field' => 'dummy']) instanceof Update);
        $this->assertTrue($this->update->getAttributes() === ['field' => 'dummy']);
    }

    public function testBuild()
    {
        $this->update->id('id')
            ->index('index')
            ->type('type')
            ->refresh(true)
            ->attributes(['field' => 'dummy']);

        $this->assertEquals([
            'index' => 'index',
            'type' => 'type',
            'refresh' => true,
            'id' => 'id',
            'body' => [
                'doc' => ['field' => 'dummy'],
            ],
        ], $this->update->build());
    }
}
