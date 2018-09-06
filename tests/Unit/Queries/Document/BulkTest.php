<?php

namespace Tests\Unit\Queries\Document;

use Osushi\ElasticsearchOrm\Queries\Document\Bulk;
use Tests\TestCase;

class BulkTest extends TestCase
{
    private $bulk;

    public function setUp()
    {
        parent::setUp();

        $this->bulk = new Bulk(
            [
                ['field' => 'dummy1'],
                ['field' => 'dummy2'],
            ]
        );
    }

    public function testIdAndGetId()
    {
        $this->assertTrue($this->bulk->id('id') instanceof Bulk);
        $this->assertTrue($this->bulk->getId() === 'id');
    }

    public function testBaseIndexAndGetBaseIndex()
    {
        $this->assertTrue($this->bulk->baseIndex('index') instanceof Bulk);
        $this->assertTrue($this->bulk->getBaseIndex() === 'index');
    }

    public function testIndexAndGetIndex()
    {
        $this->assertTrue($this->bulk->index('index') instanceof Bulk);
        $this->assertTrue($this->bulk->getIndex() === 'index');
    }

    public function testBaseTypeAndGetBaseType()
    {
        $this->assertTrue($this->bulk->baseType('type') instanceof Bulk);
        $this->assertTrue($this->bulk->getBaseType() === 'type');
    }

    public function testTypeAndGetType()
    {
        $this->assertTrue($this->bulk->type('type') instanceof Bulk);
        $this->assertTrue($this->bulk->getType() === 'type');
    }

    public function testRefreshAndGetRefresh()
    {
        $bulk = new Bulk(['field' => 'dummy'], true);
        $this->assertTrue($bulk->getRefresh());
    }

    public function testBuild()
    {
        $bulk = new Bulk(
            [
                ['field' => 'dummy1'],
                ['field' => 'dummy2', '_id' => 'id'],
            ],
            true
        );
        $bulk->baseIndex('index')
            ->baseType('type');

        $this->assertEquals([
            'body' => [
                [
                    'index' => [
                        '_index' => 'index',
                        '_type' => 'type',
                    ],
                ],
                [
                    'field' => 'dummy1',
                ],
                [
                    'index' => [
                        '_index' => 'index',
                        '_type' => 'type',
                        '_id' => 'id',
                    ],
                ],
                [
                    'field' => 'dummy2',

                ],
            ],
            'refresh' => true,
        ], $bulk->build());
    }

    public function testInsertAndDeleteAndUpdateWithCallback()
    {
        $bulk = new Bulk(
            function ($object) {
                $object->id('id')->index('new_index')->type('new_type')->insert(['field' => 'dummy']);
                $object->insert(['field' => 'dummy']);
                $object->id('id')->index('new_index')->type('new_type')->update(['field' => 'new_dummy']);
                $object->id('id')->index('new_index')->type('new_type')->delete();
            },
            'wait_for'
        );
        $bulk->baseIndex('index')
            ->baseType('type');

        $this->assertEquals([
            'body' => [
                [
                    'index' => [
                        '_index' => 'new_index',
                        '_type' => 'new_type',
                        '_id' => 'id',
                    ],
                ],
                [
                    'field' => 'dummy',
                ],
                [
                    'index' => [
                        '_index' => 'index',
                        '_type' => 'type',
                    ],
                ],
                [
                    'field' => 'dummy',
                ],
                [
                    'update' => [
                        '_index' => 'new_index',
                        '_type' => 'new_type',
                        '_id' => 'id',
                    ],
                ],
                [
                    'doc' => [
                        'field' => 'new_dummy',
                    ],
                ],
                [
                    'delete' => [
                        '_index' => 'new_index',
                        '_type' => 'new_type',
                        '_id' => 'id',
                    ],
                ],
            ],
            'refresh' => 'wait_for',
        ], $bulk->build());
    }
}
