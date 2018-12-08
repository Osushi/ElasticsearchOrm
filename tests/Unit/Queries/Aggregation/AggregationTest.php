<?php

namespace Tests\Unit\Queries\Search;

use Osushi\ElasticsearchOrm\Queries\Aggregation\Aggregation;
use Tests\TestCase;

class AggregationTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testGetNameAndGetNest()
    {
        $aggs = new Aggregation('name', 'nest');
        $this->assertEquals('name', $aggs->getName());
        $this->assertEquals('nest', $aggs->getNest());
    }

    public function testTerms()
    {
        $aggs = new Aggregation('name');
        $aggs->terms('field');
        $this->assertEquals([
            'name' => [
                'terms' => [
                    'field' => 'field',
                    'size' => 10,
                ],
            ],
        ], $aggs->getAggregations());
    }

    public function testValueCount()
    {
        $aggs = new Aggregation('name');
        $aggs->valueCount('field');
        $this->assertEquals([
            'name' => [
                'value_count' => [
                    'field' => 'field',
                ],
            ],
        ], $aggs->getAggregations());
    }

    public function testCardinality()
    {
        $aggs = new Aggregation('name');
        $aggs->cardinality('field');
        $this->assertEquals([
            'name' => [
                'cardinality' => [
                    'field' => 'field',
                ],
            ],
        ], $aggs->getAggregations());
    }

    public function testMin()
    {
        $aggs = new Aggregation('name');
        $aggs->min('field');
        $this->assertEquals([
            'name' => [
                'min' => [
                    'field' => 'field',
                ],
            ],
        ], $aggs->getAggregations());
    }

    public function testMax()
    {
        $aggs = new Aggregation('name');
        $aggs->max('field');
        $this->assertEquals([
            'name' => [
                'max' => [
                    'field' => 'field',
                ],
            ],
        ], $aggs->getAggregations());
    }

    public function testTopHits()
    {
        $aggs = new Aggregation('name');
        $aggs->topHits(
            ['price' => 'asc'],
            ['name']
        );
        $this->assertEquals([
            'name' => [
                'top_hits' => [
                    'sort' => ['price' => 'asc'],
                    'size' => 10,
                    'from' => 0,
                    '_source' => ['name'],
                ],
            ],
        ], $aggs->getAggregations());
    }

    public function testNested()
    {
        $aggs = new Aggregation('name');
        $aggs->nested('path');

        $this->assertEquals([
            'name' => [
                'nested' => [
                    'path' => 'path',
                ],
            ],
        ], $aggs->getAggregations());
    }

    public function testBuild()
    {
        $aggs = new Aggregation('name');
        $aggs->terms('field');
        $this->assertEquals([
            'name' => [
                'terms' => [
                    'field' => 'field',
                    'size' => 10,
                ],
            ],
        ], $aggs->build());
        $this->assertEquals([], $aggs->getAggregations());
    }

    public function testAggsAndAggregation()
    {
        $aggs = new Aggregation('name');
        $aggs->terms('field1');
        $aggs->aggs('nest', function ($min) {
            $min->min('field2');
        });

        $actual = [
            'name' => [
                'terms' => [
                    'field' => 'field1',
                    'size' => 10,
                ],
                'aggregations' => [
                    'nest' =>  [
                        'min' => [
                            'field' => 'field2',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertEquals($actual, $aggs->build());

        $aggs = new Aggregation('name');
        $aggs->terms('field1');
        $aggs->aggregation('nest', function ($min) {
            $min->min('field2');
        });

        $this->assertEquals($actual, $aggs->build());
    }
}
