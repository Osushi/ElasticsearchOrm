<?php

namespace Tests\Unit\Queries\Index;

use Osushi\ElasticsearchOrm\Queries\Index\Create;
use Tests\TestCase;

class CreateTest extends TestCase
{
    private $create;

    public function setUp()
    {
        parent::setUp();

        $this->create = new Create();
    }

    public function testIndexAndGetIndex()
    {
        $this->assertTrue($this->create->index('index') instanceof Create);
        $this->assertTrue($this->create->getIndex() === 'index');
    }

    public function testMappingsAndGetMappings()
    {
        $this->assertTrue($this->create->mappings(['dummy']) instanceof Create);
        $this->assertTrue($this->create->getMappings() === ['dummy']);
    }

    public function testShardsAndGetShards()
    {
        $this->assertTrue($this->create->shards(10) instanceof Create);
        $this->assertTrue($this->create->getShards() === 10);
    }

    public function testReplicasAndGetReplicas()
    {
        $this->assertTrue($this->create->replicas(10) instanceof Create);
        $this->assertTrue($this->create->getReplicas() === 10);
    }

    public function testBuild()
    {
        $this->assertEquals([
            'index' => 'index',
            'body' => [
                'settings' => [
                    'number_of_shards' => 5,
                    'number_of_replicas' => 0,
                ],
            ],
        ], $this->create->index('index')->build());

        $this->create->index('index');
        $this->create->mappings(['dummy']);
        $this->create->shards(10);
        $this->create->replicas(10);
        $this->assertEquals([
            'index' => 'index',
            'body' => [
                'settings' => [
                    'number_of_shards' => 10,
                    'number_of_replicas' => 10,
                ],
                'mappings' => [
                    0 => 'dummy',
                ],
            ],
        ], $this->create->build());

        $actual = new Create(function ($create) {
            $create->index('index')->mappings(['dummy'])->shards(10)->replicas(10);
        });
        $this->assertEquals([
            'index' => 'index',
            'body' => [
                'settings' => [
                    'number_of_shards' => 10,
                    'number_of_replicas' => 10,
                ],
                'mappings' => [
                    0 => 'dummy',
                ],
            ],
        ], $actual->build());
    }
}
