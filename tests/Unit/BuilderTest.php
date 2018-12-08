<?php

namespace Tests\Unit;

use Tests\TestCase;
use Osushi\ElasticsearchOrm\Model;
use Elasticsearch\ClientBuilder;
use Osushi\ElasticsearchOrm\Builder;
use Elasticsearch\Client;
use Osushi\ElasticsearchOrm\Queries\Index\Create;
use Mockery as m;

class BuilderExample extends Model
{
    protected $index = 'index';
    protected $type = 'type';
}

class BuilderTest extends TestCase
{
    private $builder;

    private $client;

    public function setUp()
    {
        parent::setUp();

        $config = config('elasticsearch.connections.default');
        $this->client = ClientBuilder::create();
        $this->client->setHosts($config['servers']);
        $this->builder = new Builder($this->client->build());
    }

    public function testIndexAndGetIndex()
    {
        $this->assertTrue($this->builder->index('index') instanceof Builder);
        $this->assertTrue($this->builder->getIndex() === 'index');
    }

    public function testTypeAndGetType()
    {
        $this->assertTrue($this->builder->type('type') instanceof Builder);
        $this->assertTrue($this->builder->getType() === 'type');
    }

    public function testMappingAndGetMappings()
    {
        $this->assertTrue($this->builder->mappings(['mappings']) instanceof Builder);
        $this->assertTrue($this->builder->getMappings() === ['mappings']);
    }

    public function testSetModelAndGetModel()
    {
        $this->assertTrue($this->builder->setModel(new BuilderExample) instanceof Builder);
        $this->assertTrue($this->builder->getModel() instanceof Model);
    }

    public function testRefreshAndGetRefresh()
    {
        $this->assertTrue($this->builder->refresh() instanceof Builder);
        $this->assertTrue($this->builder->getRefresh() === false);
        $this->assertTrue($this->builder->refresh(true) instanceof Builder);
        $this->assertTrue($this->builder->getRefresh() === true);
        $this->assertTrue($this->builder->refresh('wait_for') instanceof Builder);
        $this->assertTrue($this->builder->getRefresh() === 'wait_for');

        try {
            $this->builder->refresh('invalid');
            $this->fail('Exception is not happen');
        } catch (\Exception $e) {
            $this->assertEquals('refresh() supports only true(boolean), false(boolean) and wait_for(string)', $e->getMessage());
        }
    }

    public function testCreateIndex()
    {
        $this->waitReady('index');
        $actual = $this->builder->index('index')->createIndex();
        $this->builder->index('index')->refreshIndex();

        $this->assertTrue($actual->acknowledged);

        $this->assertEquals(
            'index',
            $actual->index
        );

        $this->builder->index('index')->dropIndex();
    }

    public function testDropIndex()
    {
        $this->waitReady('index');
        $this->builder->index('index')->createIndex();
        $this->builder->index('index')->refreshIndex();

        $actual = $this->builder->index('index')->dropIndex();
        $this->assertTrue($actual->acknowledged);
    }

    public function testExistsIndex()
    {
        $this->waitReady('index');
        $this->builder->index('index')->createIndex();
        $this->builder->index('index')->refreshIndex();

        $this->assertTrue($this->builder->index('index')->existsIndex());
        $this->assertFalse($this->builder->index('invalid')->existsIndex());

        $this->builder->index('index')->dropIndex();
    }

    public function testRefreshIndex()
    {
        $this->waitReady('index');
        $this->builder->index('index')->createIndex();

        $class = new \stdClass();
        $class->_shards = [
            'total' => 5,
            'successful' => 5,
            'failed' => 0,
        ];
        $this->assertEquals($class, $this->builder->index('index')->refreshIndex());

        $this->builder->index('index')->dropIndex();
    }

    public function testInsert()
    {
        $this->waitReady('index');
        $this->builder->index('index')->createIndex();
        $this->builder->index('index')->refreshIndex();
        $actual = $this->builder
                ->index('index')
                ->type('type')
                ->refresh('wait_for')
                ->insert(['field' => 'dummy']);

        $this->assertTrue(mb_strlen($actual->_id) > 0);
        $this->builder->index('index')->dropIndex();

        $this->waitReady('index');
        $this->builder->index('index')->createIndex();
        $this->builder->index('index')->refreshIndex();
        $actual = $this->builder
                ->index('index')
                ->type('type')
                ->refresh('wait_for')
                ->insert(['field' => 'dummy'], 'id');

        $this->assertTrue($actual->_id === 'id');
        $this->builder->index('index')->dropIndex();
    }

    public function testUpdate()
    {
        $this->waitReady('index');
        $this->builder->index('index')->createIndex();
        $this->builder->index('index')->refreshIndex();
        $res = $this->builder
             ->index('index')
             ->type('type')
             ->refresh('wait_for')
             ->insert([
                 'field1' => 'dummy',
                 'field2' => 'dummy',
             ]);
        $id = $res->_id;

        $actual = $this->builder
                ->index('index')
                ->type('type')
                ->refresh('wait_for')
                ->update([
                    'field1' => 'changed',
                ], $id);

        $this->assertEquals(
            2,
            $actual->_version
        );
        $this->builder->index('index')->dropIndex();
    }

    public function testDelete()
    {
        $this->waitReady('index');
        $this->builder->index('index')->createIndex();
        $this->builder->index('index')->refreshIndex();
        $res = $this->builder
             ->index('index')
             ->type('type')
             ->refresh('wait_for')
             ->insert(['field' => 'dummy']);
        $id = $res->_id;

        $actual = $this->builder
                ->index('index')
                ->type('type')
                ->refresh('wait_for')
                ->delete($id);

        $this->assertEquals(
            1,
            $actual->_shards['successful']
        );
        $this->builder->index('index')->dropIndex();
    }

    public function testDeleteByQuery()
    {
        $this->waitReady('index');
        $this->builder->index('index')->createIndex();
        $this->builder->index('index')->refreshIndex();
        for ($i = 1; $i <= 3; $i++) {
            $this->builder
                ->index('index')
                ->type('type')
                ->refresh('wait_for')
                ->insert(['field' => $i]);
        }

        $actual = $this->builder
                ->index('index')
                ->type('type')
                ->refresh(true)
                ->where('field', '>', 1)
                ->delete();

        $this->assertEquals(
            2,
            $actual->deleted
        );
        $this->builder->index('index')->dropIndex();
    }

    public function testBulk()
    {
        $this->waitReady('index1');
        $this->builder->index('index1')->createIndex();
        $this->builder->index('index1')->refreshIndex();

        $actual = $this->builder->index('index1')
                ->type('type')
                ->refresh('wait_for')
                ->bulk([
                    ['field' => 'dummy1'],
                    ['field' => 'dummy2']
                ]);

        $this->assertFalse($actual->errors);
        $this->builder->index('index1')->dropIndex();
    }

    public function testBulkWithCallback()
    {
        $this->waitReady('index1');
        $this->builder->index('index1')->createIndex();
        $this->builder->index('index1')->refreshIndex();
        $this->waitReady('index2');
        $this->builder->index('index2')->createIndex();
        $this->builder->index('index2')->refreshIndex();

        $actual = $this->builder->index('index1')
                ->type('type1')
                ->refresh('wait_for')
                ->bulk(function ($object) {
                    $object->id('id')->index('index2')->type('type2')->insert(['field' => 'dummy']);
                    $object->insert(['field' => 'dummy']);
                    $object->id('id')->index('index2')->type('type2')->update(['field' => 'new_dummy']);
                    $object->id('id')->index('index2')->type('type2')->delete();
                });

        $this->assertFalse($actual->errors);

        $this->builder->index('index1')->dropIndex();
        $this->builder->index('index2')->dropIndex();
    }

    public function testScrollAndGetScroll()
    {
        $this->assertTrue($this->builder->scroll('2m') instanceof Builder);
        $this->assertTrue($this->builder->getScroll() === '2m');
    }

    public function testTakeAndGetTake()
    {
        $this->assertTrue($this->builder->take(10) instanceof Builder);
        $this->assertTrue($this->builder->getTake() === 10);
    }

    public function testSkipAndGetSkip()
    {
        $this->assertTrue($this->builder->skip(10) instanceof Builder);
        $this->assertTrue($this->builder->getSkip() === 10);
    }

    public function testOrderByAndGetOrderBy()
    {
        $this->assertTrue($this->builder->orderBy('field1', 'desc') instanceof Builder);
        $this->assertTrue($this->builder->orderBy('field2') instanceof Builder);
        $this->assertTrue($this->builder->getOrderBy() === [
            ['field1' => 'desc'],
            ['field2' => 'asc']
        ]);

        $this->assertTrue($this->builder->orderBy('field3', 'desc', 'min') instanceof Builder);
        $this->assertTrue($this->builder->getOrderBy() === [
            ['field1' => 'desc'],
            ['field2' => 'asc'],
            [
                'field3' => [
                    'order' => 'desc',
                    'mode' => 'min',
                ],
            ],
        ]);
    }

    public function testOrderByNestAndGetOrderBy()
    {
        $builder = new Builder($this->client->build());
        $this->assertTrue($builder->orderByNest('objects.field1', 'desc', 'min', function ($nest) {
            $nest->path('objects')->where('objects.field2', '=', 1);
        }, 6.5) instanceof Builder);
        $this->assertEquals(
            [
                [
                    'objects.field1' => [
                        'order' => 'desc',
                        'mode' => 'min',
                        'nested' => [
                            'path' => 'objects',
                            'filter' => [
                                'term' => [
                                    'objects.field2' => 1
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $builder->getOrderBy()
        );

        $builder = new Builder($this->client->build());
        $this->assertTrue($builder->orderByNest('objects.field1', 'desc', 'min', function ($nest) {
            $nest->path('objects')->where('objects.field2', '=', 1);
        }, 5.6) instanceof Builder);
        $this->assertEquals(
            [
                [
                    'objects.field1' => [
                        'order' => 'desc',
                        'mode' => 'min',
                        'nested_path'=> 'objects',
                        'nested_filter'=> [
                            'term' => [
                                'objects.field2' => 1
                            ],
                        ],
                    ],
                ],
            ],
            $builder->getOrderBy()
        );
    }

    public function testSelectAndGetSelect()
    {
        $this->assertTrue($this->builder->select(['field1', 'field2']) instanceof Builder);
        $this->assertTrue($this->builder->getSelect() === ['field1', 'field2']);
        $this->assertTrue($this->builder->select('field3') instanceof Builder);
        $this->assertTrue($this->builder->getSelect() === ['field1', 'field2', 'field3']);
    }

    public function testGet()
    {
        $this->waitReady('index');
        $this->builder->index('index')->createIndex();
        $this->builder->index('index')->refreshIndex();

        $this->builder->index('index')
            ->type('type')
            ->refresh('wait_for')
            ->bulk([
                ['field' => 'dummy1', 'category' => 1],
                ['field' => 'dummy2', 'category' => 2],
                ['field' => 'dummy3', 'category' => 3],
                ['field' => 'dummy4', 'category' => 4],
                ['field' => 'dummy5', 'category' => 5],
            ]);

        $actual = $this->builder->get();
        $this->assertEquals(5, $actual->total);

        $this->builder->index('index')->dropIndex();
    }

    public function testGetWithConditions()
    {
        $this->waitReady('index');
        $this->builder->index('index')->createIndex();
        $this->builder->index('index')->refreshIndex();

        $this->builder->index('index')
            ->type('type')
            ->refresh('wait_for')
            ->bulk([
                ['field' => 'dummy1', 'category' => 1, 'group' => 1],
                ['field' => 'dummy2', 'category' => 2, 'group' => 2],
                ['field' => 'dummy3', 'category' => 3, 'group' => 1],
                ['field' => 'dummy4', 'category' => 4, 'group' => 2],
                ['field' => 'dummy5', 'category' => 5, 'group' => 1],
            ]);

        // where
        $actual = $this->builder->where('category', '=', 3)->get();
        $this->assertEquals(1, $actual->total);

        // whereIn
        $actual = $this->builder->whereIn('category', [1, 3, 5])->get();
        $this->assertEquals(3, $actual->total);

        // limit offset
        $actual = $this->builder->where('category', '>=', 3)->take(1)->skip(0)->get();
        $this->assertEquals(1, $actual->count());
        $actual = $this->builder->where('category', '>=', 3)->take(1)->skip(1)->get();
        $this->assertEquals(1, $actual->count());
        $actual = $this->builder->where('category', '>=', 3)->take(1)->skip(2)->get();
        $this->assertEquals(1, $actual->count());
        $actual = $this->builder->where('category', '>=', 3)->take(1)->skip(3)->get();
        $this->assertEquals(0, $actual->count());

        // sort
        $actual = $this->builder->orderBy('category', 'desc')->skip(4)->get()->first();
        $this->assertEquals(1, $actual->category);

        // select
        $actual = $this->builder->select('category')->take(1)->get()->first();
        $this->assertEquals([], array_except($actual->toArray(), ['category']));

        // scroll
        $actual = $this->builder->where('category', '>=', 3)->select('category')->take(1)->scroll('1m')->get();
        $this->assertTrue(!is_null($actual->scroll_id));
        $this->assertEquals(1, $actual->count());
        $actual = $this->builder->scroll('1m')->scrollId($actual->scroll_id)->get();
        $this->assertTrue(!is_null($actual->scroll_id));
        $this->assertEquals(1, $actual->count());
        $actual = $this->builder->scrollId($actual->scroll_id)->clearScroll();
        $this->assertTrue(isset($actual->succeeded));

        // aggs
        $actual = $this->builder->aggs('groups', function ($group) {
            $group->terms('group');
        })->get();
        $this->assertTrue(isset($actual->aggregations));
        $this->assertEquals(2, count($actual->aggregations->groups['buckets']));

        // collapse
        $actual = $this->builder->collapse('group')->get();
        $this->assertEquals(2, $actual->count());
        $actual = $this->builder->collapse('group', function ($innerHits) {
            $innerHits->name('categories')->orderBy('category', 'desc')->select('category', 'group')->take(1)->skip(1)->add();
        })->get();

        $first = $actual->first()->getInnerHit('categories')->first();
        if ($first->group === 2) {
            $this->assertEquals(2, $first->category);
        }
        if ($first->group === 1) {
            $this->assertEquals(3, $first->category);
        }

        $last = $actual->last()->getInnerHit('categories')->last();
        if ($last->group === 2) {
            $this->assertEquals(2, $last->category);
        }
        if ($last->group === 1) {
            $this->assertEquals(3, $last->category);
        }

        $this->builder->index('index')->dropIndex();
    }

    public function testCount()
    {
        $this->waitReady('index');
        $this->builder->index('index')->createIndex();
        $this->builder->index('index')->refreshIndex();

        $this->builder->index('index')
            ->type('type')
            ->refresh('wait_for')
            ->bulk([
                ['field' => 'dummy1', 'category' => 1],
                ['field' => 'dummy2', 'category' => 2],
                ['field' => 'dummy3', 'category' => 3],
                ['field' => 'dummy4', 'category' => 4],
                ['field' => 'dummy5', 'category' => 5],
            ]);

        $this->assertEquals(5, $this->builder->count());
        $this->assertEquals(3, $this->builder->where('category', '>=', 3)->count());

        $this->builder->index('index')->dropIndex();
    }

    public function testCollapseAndGetCollapse()
    {
        $this->assertTrue($this->builder->collapse('field') instanceof Builder);
        $this->assertEquals(
            [
                'field' => 'field',
            ],
            $this->builder->getCollapse()
        );

        $this->assertTrue($this->builder->collapse('field', function ($innerHits) {
            $innerHits->name('name')->add();
        }) instanceof Builder);
        $this->assertEquals(
            [
                'field' => 'field',
                'inner_hits' => [
                    [
                        'name' => 'name',
                        'size' => 3,
                        'from' => 0,
                        'sort' => [],
                        '_source' => [],
                    ],
                ],
            ],
            $this->builder->getCollapse()
        );
    }

    public function testAggsAndAggregationAndGetAggregation()
    {
        $builder = new Builder($this->client->build());
        $builder->aggs('products', function ($groups) {
            $groups->terms('product');
            $groups->aggs('price_min', function ($min) {
                $min->min('price');
            });
            $groups->aggs('price_max', function ($max) {
                $max->max('price');
            });
            $groups->aggs('product::categories', function ($subGroups) {
                $subGroups->terms('category');
            });
        });

        $this->assertEquals(
            [
                'products' => [
                    'terms' => [
                        'field' => 'product',
                        'size' => 10,
                    ],
                    'aggregations' => [
                        'price_min' => [
                            'min' => [
                                'field' => 'price',
                            ],
                        ],
                        'price_max' => [
                            'max' => [
                                'field' => 'price',
                            ],
                        ],
                        'product::categories' => [
                            'terms' => [
                                'field' => 'category',
                                'size' => 10,
                            ],
                        ],
                    ],
                ],
            ],
            $builder->getAggregation()
        );

        $builder = new Builder($this->client->build());
        $builder->aggregation('products', function ($groups) {
            $groups->terms('product');
        });

        $this->assertEquals(
            [
                'products' => [
                    'terms' => [
                        'field' => 'product',
                        'size' => 10,
                    ],
                ],
            ],
            $builder->getAggregation()
        );
    }

    public function testGetConditions_Where_WhereIn()
    {
        $this->assertTrue($this->builder->where('field', '=', 2) instanceof Builder);
        $this->assertTrue($this->builder->whereIn('field', [2]) instanceof Builder);

        // where
        $builder = new Builder($this->client->build());
        $builder->where('field', '=', 2);
        $this->assertEquals(
            [
                'query' => [
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
            ],
            $builder->getConditions()
        );

        $builder = new Builder($this->client->build());
        $builder->where('field', 2);
        $this->assertEquals(
            [
                'query' => [
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
            ],
            $builder->getConditions()
        );

        $builder = new Builder($this->client->build());
        $builder->where('field', '>', 2);
        $this->assertEquals(
            [
                'query' => [
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
            ],
            $builder->getConditions()
        );

        $builder = new Builder($this->client->build());
        $builder->where('field', '>=', 2);
        $this->assertEquals(
            [
                'query' => [
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
            ],
            $builder->getConditions()
        );

        $builder = new Builder($this->client->build());
        $builder->where('field', '<', 2);
        $this->assertEquals(
            [
                'query' => [
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
            ],
            $builder->getConditions()
        );

        $builder = new Builder($this->client->build());
        $builder->where('field', '<=', 2);
        $this->assertEquals(
            [
                'query' => [
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
            ],
            $builder->getConditions()
        );

        $builder = new Builder($this->client->build());
        $builder->where('field', 'like', 'test');
        $this->assertEquals(
            [
                'query' => [
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
            ],
            $builder->getConditions()
        );

        $builder = new Builder($this->client->build());
        $builder->where('objects', 'nested', function ($nested) {
            $nested->mode('avg')->where('objects.name', '=', 'name');
        });
        $this->assertEquals(
            [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'nested' => [
                                    'score_mode' => 'avg',
                                    'path' => 'objects',
                                    'query' => [
                                        'bool' => [
                                            'filter' => [
                                                [
                                                    'term' => [
                                                        'objects.name' => 'name',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $builder->getConditions()
        );

        // wherein
        $builder = new Builder($this->client->build());
        $builder->whereIn('field', [2]);
        $this->assertEquals(
            [
                'query' => [
                    'bool' => [
                        'filter' => [
                            [
                                'terms' => [
                                    'field' => [2],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $builder->getConditions()
        );

        // all
        $builder = new Builder($this->client->build());
        $builder->where('price', '>', 2000)
            ->where('category', 5)
            ->where('product', 'like', 'test')
            ->orderBy('price')
            ->collapse('author')
            ->select('field')
            ->aggs('categories', function ($groups) {
                $groups->terms('category');
            });
        $this->assertEquals(
            [
                '_source' => [
                    'field',
                ],
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'match' => [
                                    'product' => 'test',
                                ],
                            ],
                        ],
                        'filter' => [
                            [
                                'range' => [
                                    'price' => [
                                        'gt' => 2000,
                                    ],
                                ],
                            ],
                            [
                                'term' => [
                                    'category' => '5',
                                ],
                            ],
                        ],
                    ],
                ],
                'sort' => [
                    [
                        'price' => 'asc',
                    ],
                ],
                'collapse' => [
                    'field' => 'author',
                ],
                'aggregations' => [
                    'categories' => [
                        'terms' => [
                            'field' => 'category',
                            'size' => 10,
                        ],
                    ],
                ],
            ],
            $builder->getConditions()
        );
    }

    public function testIsOperator()
    {
        $mock = m::mock(new Builder($this->client->build()))
              ->makePartial()
              ->shouldAllowMockingProtectedMethods();

        $this->assertTrue($mock->isOperator('like'));
        $this->assertFalse($mock->isOperator('('));
    }

    public function testIsSortMode()
    {
        $mock = m::mock(new Builder($this->client->build()))
              ->makePartial()
              ->shouldAllowMockingProtectedMethods();

        $this->assertTrue($mock->isSortMode('min'));
        $this->assertFalse($mock->isSortMode('invalid'));
    }

    public function testExecute()
    {
        $mock = m::mock(new Builder($this->client->build()))
              ->makePartial()
              ->shouldAllowMockingProtectedMethods();

        $builderExample = new BuilderExample;
        $mock->index($builderExample->getIndex());
        $mock->type($builderExample->getType());
        $mock->setModel($builderExample);

        $create = new Create();
        $create->index($mock->getIndex())
            ->mappings($mock->getMappings());

        $actual = $mock->execute('index.create', $create);
        $this->builder->index('index')->refreshIndex();

        $this->assertTrue($this->builder->index('index')->existsIndex());

        $this->builder->index('index')->dropIndex();
    }

    public function testReset()
    {
        $this->builder->take(100);
        $this->assertEquals(100, $this->builder->getTake());

        $this->builder->reset();
        $this->assertEquals(10, $this->builder->getTake());
    }

    public function testRaw()
    {
        $this->assertTrue($this->builder->raw() instanceof Client);
    }

    private function waitReady(
        string $index
    ) {
        $tries = 1;
        // Wait, if index is not exists
        while ($this->builder->index($index)->existsIndex()) {
            sleep(1);
            $tries++;
            if ($tries === 3) {
                $this->builder->index($index)->dropIndex();
            }
        }
    }

    public function tearDown()
    {
        parent::tearDown();

        m::close();
    }
}
