<?php

namespace Tests\Unit\Requests;

use Tests\TestCase;
use Elasticsearch\ClientBuilder;
use Osushi\ElasticsearchOrm\Requests\Request;
use Mockery as m;

class BuilderTest extends TestCase
{
    private $request;

    private $client;

    public function setUp()
    {
        parent::setUp();

        $config = config('elasticsearch.connections.default');
        $this->client = ClientBuilder::create();
        $this->client->setHosts($config['servers']);

        $mock = m::mock($this->client->build())
              ->makePartial()
              ->shouldAllowMockingProtectedMethods();

        $mock->shouldReceive('indices')->andReturn(m::self());
        $mock->shouldReceive('create')->withAnyArgs()->andReturn(true);
        $mock->shouldReceive('drop')->withAnyArgs()->andReturn(true);
        $mock->shouldReceive('exists')->withAnyArgs()->andReturn(true);
        $mock->shouldReceive('refresh')->withAnyArgs()->andReturn(true);

        $mock->shouldReceive('index')->withAnyArgs()->andReturn(true);
        $mock->shouldReceive('update')->withAnyArgs()->andReturn(true);
        $mock->shouldReceive('deleteByQuery')->withAnyArgs()->andReturn(true);
        $mock->shouldReceive('delete')->withAnyArgs()->andReturn(true);
        $mock->shouldReceive('bulk')->withAnyArgs()->andReturn(true);

        $mock->shouldReceive('scroll')->withAnyArgs()->andReturn(true);
        $mock->shouldReceive('search')->withAnyArgs()->andReturn(true);
        $mock->shouldReceive('clearScroll')->withAnyArgs()->andReturn(true);
        $mock->shouldReceive('count')->withAnyArgs()->andReturn(true);

        $this->request = new Request($mock);
    }

    public function testSetParamsAndGetParams()
    {
        $this->assertTrue($this->request->setParams(['dummy']) instanceof Request);
        $this->assertTrue($this->request->getParams() === ['dummy']);
    }

    public function testSetRequestTypesAndGetRequestTypes()
    {
        $this->assertTrue($this->request->setRequestTypes(['dummy']) instanceof Request);
        $this->assertTrue($this->request->getRequestTypes() === ['dummy']);
    }

    public function testRequest()
    {
        $this->request->setRequestTypes(['index', 'create']);
        $this->assertTrue($this->request->request());

        $this->request->setRequestTypes(['index', 'create']);
        $this->assertTrue($this->request->request());

        $this->request->setRequestTypes(['index', 'drop']);
        $this->assertTrue($this->request->request());

        $this->request->setRequestTypes(['index', 'exist']);
        $this->assertTrue($this->request->request());

        $this->request->setRequestTypes(['index', 'refresh']);
        $this->assertTrue($this->request->request());

        try {
            $this->request->setRequestTypes(['index', 'invalid']);
            $this->request->request();
            $this->fail('Exception is not happen');
        } catch (\Exception $e) {
            $this->assertEquals('Invalid request type', $e->getMessage());
        }

        $this->request->setRequestTypes(['document', 'insert']);
        $this->assertTrue($this->request->request());

        $this->request->setRequestTypes(['document', 'update']);
        $this->assertTrue($this->request->request());

        $this->request->setRequestTypes(['document', 'delete']); // deleteByQuery
        $this->request->setParams(['body' => 'dummy']);
        $this->assertTrue($this->request->request());
        $this->request->setRequestTypes(['document', 'delete']); // delete
        $this->request->setParams([]);
        $this->assertTrue($this->request->request());

        $this->request->setRequestTypes(['document', 'bulk']);
        $this->assertTrue($this->request->request());

        try {
            $this->request->setRequestTypes(['document', 'invalid']);
            $this->request->request();
            $this->fail('Exception is not happen');
        } catch (\Exception $e) {
            $this->assertEquals('Invalid request type', $e->getMessage());
        }

        $this->request->setRequestTypes(['search', 'get']);
        $this->request->setParams(['scroll_id' => 'dummy']); // scroll
        $this->assertTrue($this->request->request());
        $this->request->setRequestTypes(['search', 'get']);
        $this->request->setParams([]); // search
        $this->assertTrue($this->request->request());

        $this->request->setRequestTypes(['search', 'clear', 'scroll']);
        $this->assertTrue($this->request->request());

        $this->request->setRequestTypes(['search', 'count']);
        $this->assertTrue($this->request->request());

        try {
            $this->request->setRequestTypes(['search', 'invalid']);
            $this->request->request();
            $this->fail('Exception is not happen');
        } catch (\Exception $e) {
            $this->assertEquals('Invalid request type', $e->getMessage());
        }
    }

    public function tearDown()
    {
        parent::tearDown();

        m::close();
    }
}
