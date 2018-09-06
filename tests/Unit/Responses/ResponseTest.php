<?php

namespace Tests\Unit\Responses;

use Tests\TestCase;
use Osushi\ElasticsearchOrm\Responses\Response;
use Osushi\ElasticsearchOrm\Model;
use Osushi\ElasticsearchOrm\Collection;
use Mockery as m;

class ResponseTest extends TestCase
{
    private $responseFixture = [
        'took' => 4,
        'timed_out' => false,
        '_shards' => [
            'total' => 5,
            'successful' => 5,
            'skipped' => 0,
            'failed' => 0,
        ],
        'hits' => [
            'total' => 1,
            'max_score' => 1.0,
            'hits' => [
                [
                    '_index' => 'index',
                    '_type' => 'type',
                    '_id' => 'mssXrWUB3uqw6z95slaX',
                    '_score' => 1.0,
                    '_source' => [
                        'field' => 'dummy1',
                        'category' => 1,
                    ],
                    'fields' => ['dummy'],
                    'inner_hits' => [
                        'inner' => [
                            'hits' => [
                                'total' => 1,
                                'max_score' => 1.0,
                                'hits' => [
                                    [
                                        '_index' => 'index',
                                        '_type' => 'type',
                                        '_id' => 'mssXrWUB3uqw6z95slaX',
                                        '_score' => 1.0,
                                        '_source' => [
                                            'field' => 'dummy1',
                                            'category' => 1,
                                        ],
                                        'fields' => ['dummy'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ];

    public function setUp()
    {
        parent::setUp();
    }

    public function testSetRequestTypesAndGetRequestTypes()
    {
        $response = new Response([]);
        $this->assertTrue($response->setRequestTypes(['dummy']) instanceof Response);
        $this->assertTrue($response->getRequestTypes() === ['dummy']);
    }

    public function testSetModelAndGetModel()
    {
        $response = new Response([]);
        $this->assertTrue($response->setModel(new Model) instanceof Response);
        $this->assertTrue($response->getModel() instanceof Model);
    }

    public function testMake()
    {
        $response = new Response([]);

        $response->setRequestTypes(['index', 'create']);
        $this->assertEquals(new \stdClass, $response->make());

        $response->setRequestTypes(['index', 'drop']);
        $this->assertEquals(new \stdClass, $response->make());

        $response->setRequestTypes(['index', 'exist']);
        $this->assertEquals([], $response->make());

        $response->setRequestTypes(['index', 'refresh']);
        $this->assertEquals(new \stdClass, $response->make());

        try {
            $response->setRequestTypes(['index', 'invalid']);
            $response->make();
            $this->fail('Exception is not happen');
        } catch (\Exception $e) {
            $this->assertEquals('Invalid response type', $e->getMessage());
        }

        $response = new Response(['_id' => 'id']);
        $response->setRequestTypes(['document', 'insert']);
        $response->setModel(new Model);
        $this->assertTrue($response->make() instanceof Model);
        $response = new Response([]);
        $response->setRequestTypes(['document', 'insert']);
        $this->assertEquals(new \stdClass, $response->make());

        $response = new Response([]);
        $response->setRequestTypes(['document', 'update']);
        $response->setModel(new Model);
        $this->assertTrue($response->make() instanceof Model);
        $response = new Response([]);
        $response->setRequestTypes(['document', 'update']);
        $this->assertEquals(new \stdClass, $response->make());

        $response = new Response([]);
        $response->setRequestTypes(['document', 'delete']);
        $response->setModel(new Model);
        $this->assertTrue($response->make() instanceof Model);
        $response = new Response([]);
        $response->setRequestTypes(['document', 'delete']);
        $this->assertEquals(new \stdClass, $response->make());

        $response = new Response([]);
        $response->setRequestTypes(['document', 'bulk']);
        $this->assertEquals(new \stdClass, $response->make());

        try {
            $response->setRequestTypes(['document', 'invalid']);
            $response->make();
            $this->fail('Exception is not happen');
        } catch (\Exception $e) {
            $this->assertEquals('Invalid response type', $e->getMessage());
        }

        $response = new Response($this->responseFixture);
        $response->setRequestTypes(['search', 'get']);
        $this->assertTrue($response->make() instanceof Collection);

        $response = new Response([]);
        $response->setRequestTypes(['search', 'clear', 'scroll']);
        $this->assertEquals(new \stdClass, $response->make());

        $response = new Response(['count' => 100]);
        $response->setRequestTypes(['search', 'count']);
        $this->assertEquals(100, $response->make());

        try {
            $response->setRequestTypes(['search', 'invalid']);
            $response->make();
            $this->fail('Exception is not happen');
        } catch (\Exception $e) {
            $this->assertEquals('Invalid response type', $e->getMessage());
        }
    }

    public function testHydrate()
    {
        $mock = m::mock(new Response($this->responseFixture))
              ->makePartial()
              ->shouldAllowMockingProtectedMethods();

        $this->assertTrue($mock->hydrate($this->responseFixture) instanceof Collection);
    }

    public function tearDown()
    {
        parent::tearDown();

        m::close();
    }
}
