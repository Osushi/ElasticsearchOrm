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

    public function testBuild_Query()
    {
        // response
        $this->assertTrue($this->nested->match('field', '=', 2) instanceof Nested);
        $this->assertTrue($this->nested->where('field', '=', 2) instanceof Nested);
        $this->assertTrue($this->nested->orWhere('field', '=', 2) instanceof Nested);
        $this->assertTrue($this->nested->notWhere('field', '=', 2) instanceof Nested);

        foreach (['filter' => 'where', 'must' => 'match', 'should' => 'orWhere', 'must_not' => 'notWhere'] as $key => $method) {
            $nested = new Nested();
            $nested->$method('field', '=', 2);
            $this->assertEquals(
                [
                    'bool' => [
                        $key => [
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
            $nested->$method('field', 2);
            $this->assertEquals(
                [
                    'bool' => [
                        $key => [
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
            $nested->$method('field', '>', 2);
            $this->assertEquals(
                [
                    'bool' => [
                        $key => [
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
            $nested->$method('field', '>=', 2);
            $this->assertEquals(
                [
                    'bool' => [
                        $key => [
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
            $nested->$method('field', '<', 2);
            $this->assertEquals(
                [
                    'bool' => [
                        $key => [
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
            $nested->$method('field', '<=', 2);
            $this->assertEquals(
                [
                    'bool' => [
                        $key => [
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
            $nested->$method('field', 'like', 'test');
            $this->assertEquals(
                [
                    'bool' => [
                        $key => [
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
        }

        $this->assertEquals([], $nested->getFilter());
        $this->assertEquals([], $nested->getMust());
        $this->assertEquals([], $nested->getMustNot());
        $this->assertEquals([], $nested->getShould());
        $this->assertNull($nested->getMode());
    }

    public function testBuild_WhereIn()
    {
        $this->assertTrue($this->nested->whereIn('field', [2]) instanceof Nested);
        $this->assertTrue($this->nested->orWhereIn('field', [2]) instanceof Nested);
        $this->assertTrue($this->nested->notWhereIn('field', [2]) instanceof Nested);


        foreach (['filter' => 'whereIn', 'should' => 'orWhereIn', 'must_not' => 'notWhereIn'] as $key => $method) {
            $nested = new Nested();
            $nested->$method('field', [2]);
            $this->assertEquals(
                [
                    'bool' => [
                        $key => [
                            [
                                'terms' => [
                                    'field' => [2],
                                ],
                            ],
                        ],
                    ],
                ],
                $nested->build()
            );
        }
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
