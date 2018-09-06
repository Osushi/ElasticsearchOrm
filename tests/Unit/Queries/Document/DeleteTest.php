<?php

namespace Tests\Unit\Queries\Document;

use Osushi\ElasticsearchOrm\Queries\Document\Delete;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    private $delete;

    public function setUp()
    {
        parent::setUp();

        $this->delete = new Delete();
    }

    public function testIdAndGetId()
    {
        $this->assertTrue($this->delete->id('id') instanceof Delete);
        $this->assertTrue($this->delete->getId() === 'id');
    }

    public function testIndexAndGetIndex()
    {
        $this->assertTrue($this->delete->index('index') instanceof Delete);
        $this->assertTrue($this->delete->getIndex() === 'index');
    }

    public function testTypeAndGetType()
    {
        $this->assertTrue($this->delete->type('type') instanceof Delete);
        $this->assertTrue($this->delete->getType() === 'type');
    }

    public function testRefreshAndGetRefresh()
    {
        $this->assertTrue($this->delete->refresh(true) instanceof Delete);
        $this->assertTrue($this->delete->getRefresh());
    }

    public function testConditionsAndGetConditions()
    {
        $this->assertTrue($this->delete->conditions(['field' => 'dummy']) instanceof Delete);
        $this->assertTrue($this->delete->getConditions() === ['field' => 'dummy']);
    }

    public function testBuild()
    {
        $this->delete->id('id')
            ->index('index')
            ->type('type')
            ->refresh(true);

        $this->assertEquals([
            'index' => 'index',
            'type' => 'type',
            'refresh' => true,
            'id' => 'id',
        ], $this->delete->build());
    }

    public function testBuildWithConditions()
    {
        $this->delete->index('index')
            ->type('type')
            ->refresh(true)
            ->conditions(['field' => 'dummy']);

        $this->assertEquals([
            'index' => 'index',
            'type' => 'type',
            'refresh' => true,
            'body' => [
                'field' => 'dummy',
            ],
        ], $this->delete->build());
    }
}
