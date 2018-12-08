<?php

namespace Tests\Unit\Queries\Sort;

use Osushi\ElasticsearchOrm\Queries\Sort\Nested;
use Tests\TestCase;
use Mockery as m;

class NestedTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testPathAndGetPath()
    {
        $nested = new Nested(6.5);
        $this->assertTrue($nested->path('path') instanceof Nested);
        $this->assertTrue($nested->getPath() === 'path');
    }

    public function testBuild_Where()
    {
        // version >= 6.1
        $nested = new Nested(6.5);
        $nested->path('path');
        $this->assertTrue($nested->where('field', '=', 2) instanceof Nested);
        $this->assertEquals(
            [
                'path' => 'path',
                'filter' => [
                    'term' => [
                        'field' => 2,
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(6.5);
        $nested->where('field', '=', 2);
        $this->assertEquals(
            [
                'filter' => [
                    'term' => [
                        'field' => 2,
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(6.5);
        $nested->where('field', 2);
        $this->assertEquals(
            [
                'filter' => [
                    'term' => [
                        'field' => 2,
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(6.5);
        $nested->where('field', '>', 2);
        $this->assertEquals(
            [
                'filter' => [
                    'range' => [
                        'field' => ['gt' => 2],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(6.5);
        $nested->where('field', '>=', 2);
        $this->assertEquals(
            [
                'filter' => [
                    'range' => [
                        'field' => ['gte' => 2],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(6.5);
        $nested->where('field', '<', 2);
        $this->assertEquals(
            [
                'filter' => [
                    'range' => [
                        'field' => ['lt' => 2],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(6.5);
        $nested->where('field', '<=', 2);
        $this->assertEquals(
            [
                'filter' => [
                    'range' => [
                        'field' => ['lte' => 2],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(6.5);
        $nested->where('field', 'like', 'test');
        $this->assertEquals(
            [
                'filter' => [
                    'match' => [
                        'field' => 'test',
                    ],
                ],
            ],
            $nested->build()
        );

        // version < 6.1
        $nested = new Nested(5.6);
        $nested->path('path');
        $this->assertTrue($nested->where('field', '=', 2) instanceof Nested);
        $this->assertEquals(
            [
                'nested_path' => 'path',
                'nested_filter' => [
                    'term' => [
                        'field' => 2,
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(5.6);
        $nested->where('field', '=', 2);
        $this->assertEquals(
            [
                'nested_filter' => [
                    'term' => [
                        'field' => 2,
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(5.6);
        $nested->where('field', 2);
        $this->assertEquals(
            [
                'nested_filter' => [
                    'term' => [
                        'field' => 2,
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(5.6);
        $nested->where('field', '>', 2);
        $this->assertEquals(
            [
                'nested_filter' => [
                    'range' => [
                        'field' => ['gt' => 2],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(5.6);
        $nested->where('field', '>=', 2);
        $this->assertEquals(
            [
                'nested_filter' => [
                    'range' => [
                        'field' => ['gte' => 2],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(5.6);
        $nested->where('field', '<', 2);
        $this->assertEquals(
            [
                'nested_filter' => [
                    'range' => [
                        'field' => ['lt' => 2],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(5.6);
        $nested->where('field', '<=', 2);
        $this->assertEquals(
            [
                'nested_filter' => [
                    'range' => [
                        'field' => ['lte' => 2],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested(5.6);
        $nested->where('field', 'like', 'test');
        $this->assertEquals(
            [
                'nested_filter' => [
                    'match' => [
                        'field' => 'test',
                    ],
                ],
            ],
            $nested->build()
        );

        $this->assertEquals([], $nested->getFilter());
        $this->assertNull($nested->getPath());
    }

    public function testIsOperator()
    {
        $mock = m::mock(new Nested(6.5))
              ->makePartial()
              ->shouldAllowMockingProtectedMethods();

        $this->assertTrue($mock->isOperator('like'));
        $this->assertFalse($mock->isOperator('('));
    }

    public function tearDown()
    {
        parent::tearDown();

        m::close();
    }
}
