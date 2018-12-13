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

        $nested = new Nested();
        $nested->orWhere('field', '=', 2)->queryOption('minimum_should_match', 1);
        ;
        $this->assertEquals(
            [
                'bool' => [
                    'should' => [
                        [
                            'term' => [
                                'field' => 2,
                            ],
                        ],
                    ],
                    'minimum_should_match' => 1,
                ],
            ],
            $nested->build()
        );

        $this->assertEquals([], $nested->getFilter());
        $this->assertEquals([], $nested->getMust());
        $this->assertEquals([], $nested->getMustNot());
        $this->assertEquals([], $nested->getShould());
        $this->assertEquals([], $nested->getQueryOptions());
        $this->assertNull($nested->getMode());
    }

    public function testBuild_WhereIn()
    {
        $this->assertTrue($this->nested->whereIn('field', [2]) instanceof Nested);
        $this->assertTrue($this->nested->orWhereIn('field', [2]) instanceof Nested);
        $this->assertTrue($this->nested->notWhereIn('field', [2]) instanceof Nested);


        foreach (['must' => 'matchIn', 'filter' => 'whereIn', 'should' => 'orWhereIn', 'must_not' => 'notWhereIn'] as $key => $method) {
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

    public function testBuild_Between()
    {
        $this->assertTrue($this->nested->matchBetween('field', [2, 4]) instanceof Nested);
        $this->assertTrue($this->nested->whereBetween('field', [2, 4]) instanceof Nested);
        $this->assertTrue($this->nested->orWhereBetween('field', [2, 4]) instanceof Nested);
        $this->assertTrue($this->nested->notWhereBetween('field', [2, 4]) instanceof Nested);

        foreach (['must' => 'matchBetween', 'filter' => 'whereBetween', 'should' => 'orWhereBetween', 'must_not' => 'notWhereBetween'] as $key => $method) {
            $nested = new Nested();
            $nested->$method('field', [2, 4], [true, true]);
            $this->assertEquals(
                [
                    'bool' => [
                        $key => [
                            [
                                'range' => [
                                    'field' => ['gte' => 2, 'lte' => 4],
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

    public function testIsQueryOption()
    {
        $mock = m::mock(new Nested())
              ->makePartial()
              ->shouldAllowMockingProtectedMethods();

        $this->assertTrue($mock->isQueryOption('minimum_should_match'));
        $this->assertFalse($mock->isQueryOption('('));
    }

    public function tearDown()
    {
        parent::tearDown();

        m::close();
    }
}
