<?php

namespace Tests\Unit;

use Tests\TestCase;
use Osushi\ElasticsearchOrm\Connection;
use Osushi\ElasticsearchOrm\Builder;
use Mockery as m;

class ConnectionTest extends TestCase
{
    private $connection;

    public function setUp()
    {
        parent::setUp();

        $this->connection = m::mock(new Connection())
                          ->makePartial()
                          ->shouldAllowMockingProtectedMethods();
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('elasticsearch', [
            'default' => 'default',
            'connections' => [
                'default' => [
                    'servers' => [
                        [
                            'host' => '127.0.0.1',
                            'port' => 9200,
                            'user' => '',
                            'pass' => '',
                            'scheme' => 'http',
                        ],
                    ],
                    'logging' => [
                        'enabled' => true,
                        'level' => 'all',
                        'location' => base_path('storage/logs/elasticsearch.log'),
                    ],
                ],
            ],
        ]);
    }

    public function testIsLoaded()
    {
        $this->assertFalse(
            $this->connection->isLoaded('default')
        );

        $this->connection->connect('default');
        $this->assertTrue(
            $this->connection->isLoaded('default')
        );
    }

    public function testNewQuery()
    {
        $this->connection->connect('default');
        $this->assertTrue(
            $this->connection->newQuery('default') instanceof Builder
        );
    }

    public function testCreate()
    {
        $connection = Connection::create([
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 9200,
                    'user' => '',
                    'pass' => '',
                    'scheme' => 'http',
                ],
            ],
            'logging' => [
                'enabled' => true,
                'level' => 'all',
                'location' => base_path('storage/logs/elasticsearch.log'),
            ],
        ]);
        $this->assertTrue(
            $connection instanceof Builder
        );
    }

    public function testConnect()
    {
        $this->assertTrue(
            $this->connection->connect('default') instanceof Builder
        );

        // Test for using connection pool
        $this->assertTrue(
            $this->connection->connect('default') instanceof Builder
        );

        try {
            $this->connection->connect('invalid');
            $this->fail('Unbale to get builder class');
        } catch (\Exception $e) {
            $this->assertEquals('Invalid elasticsearch connection driver `invalid`', $e->getMessage());
        }
    }

    public function tearDown()
    {
        parent::tearDown();

        m::close();
    }
}
