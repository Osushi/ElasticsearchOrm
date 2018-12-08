<?php

namespace Tests\Unit\Queries\Query;

use Osushi\ElasticsearchOrm\Queries\Query\Nested;
use Tests\TestCase;
use Mockery as m;

class NestedTest extends TestCase
{
    private $nested;

    public function setUp()
    {
        parent::setUp();

        $this->nested = new Nested();
    }

    public function testModeAndGetMode()
    {
        $this->assertTrue($this->nested->mode('mode') instanceof Nested);
        $this->assertTrue($this->nested->getMode() === 'mode');
    }

    public function testBuild_Where()
    {
        $this->assertTrue($this->nested->where('field', '=', 2) instanceof Nested);

        // where
        $nested = new Nested();
        $nested->where('field', '=', 2);
        $this->assertEquals(
            [
                'bool' => [
                    'filter' => [
                        [
                            'term' => [
                                'field' => 2,
                            ],
                        ],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested();
        $nested->where('field', 2);
        $this->assertEquals(
            [
                'bool' => [
                    'filter' => [
                        [
                            'term' => [
                                'field' => 2,
                            ],
                        ],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested();
        $nested->where('field', '>', 2);
        $this->assertEquals(
            [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'field' => ['gt' => 2],
                            ],
                        ],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested();
        $nested->where('field', '>=', 2);
        $this->assertEquals(
            [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'field' => ['gte' => 2],
                            ],
                        ],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested();
        $nested->where('field', '<', 2);
        $this->assertEquals(
            [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'field' => ['lt' => 2],
                            ],
                        ],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested();
        $nested->where('field', '<=', 2);
        $this->assertEquals(
            [
                'bool' => [
                    'filter' => [
                        [
                            'range' => [
                                'field' => ['lte' => 2],
                            ],
                        ],
                    ],
                ],
            ],
            $nested->build()
        );

        $nested = new Nested();
        $nested->where('field', 'like', 'test');
        $this->assertEquals(
            [
                'bool' => [
                    'must' => [
                        [
                            'match' => [
                                'field' => 'test',
                            ],
                        ],
                    ],
                ],
            ],
            $nested->build()
        );


        $this->assertEquals([], $nested->getFilter());
        $this->assertEquals([], $nested->getMust());
        $this->assertNull($nested->getMode());
    }

    public function testIsOperator()
    {
        $mock = m::mock(new Nested())
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
