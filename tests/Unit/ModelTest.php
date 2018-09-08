<?php

namespace Tests\Unit;

use Tests\TestCase;
use Osushi\ElasticsearchOrm\Model;
use Osushi\ElasticsearchOrm\Builder;
use Osushi\ElasticsearchOrm\Collection;

class ModelExample extends Model
{
    protected $index = 'index';
    protected $type = 'type';
}

class ModelTest extends TestCase
{
    private $model;

    public function setUp()
    {
        parent::setUp();

        $this->model = new ModelExample();
    }

    public function testFill()
    {
        $this->assertTrue(
            $this->model->fill([]) instanceof Model
        );

        $this->model->fill(['field' => 'dummy']);
        $this->assertEquals(
            ['field' => 'dummy'],
            $this->model->toArray()
        );

        $this->model->fill(['_id' => 'id']);
        $this->assertEquals(
            [],
            $this->model->toArray()
        );
        $this->assertEquals(
            'id',
            $this->model->getId()
        );
    }

    public function testGetConnection()
    {
        $this->assertTrue($this->model->getConnection() === 'default');
    }

    public function testSetConnection()
    {
        $this->model->setConnection('sample');
        $this->assertTrue($this->model->getConnection() === 'sample');
    }

    public function testSetIndexAndGetIndex()
    {
        $this->model->setIndex('index');
        $this->assertTrue($this->model->getIndex() === 'index');
    }

    public function testSetTypeAndGetType()
    {
        $this->model->setType('type');
        $this->assertTrue($this->model->getType() === 'type');
    }

    public function testSetMappingAndGetMappings()
    {
        $this->model->setMappings(['mappings']);
        $this->assertTrue($this->model->getMappings() === ['mappings']);
    }

    public function testSetIdAndGetId()
    {
        // string
        $this->model->setId('id');
        $this->assertTrue($this->model->getId() === 'id');

        // integer
        $this->model->setId(1);
        $this->assertTrue($this->model->getId() === '1');
    }

    public function testSetFieldsAndGetFields()
    {
        $this->model->setFields(['fields']);
        $this->assertTrue($this->model->getFields() === ['fields']);
    }

    public function testSetInnerHitsAndGetInnerHitsAndGetInnerHit()
    {
        $collection = new Collection([1, 2, 3]);
        $this->model->setInnerHits('test', $collection);
        $this->assertTrue($this->model->getInnerHits() === ['test' => $collection]);
        $this->assertTrue($this->model->getInnerHit('test') === $collection);
        $this->assertFalse($this->model->getInnerHit('invalid'));
    }

    public function testSetIsExistsAndGetIsExists()
    {
        $this->model->setIsExists(true);
        $this->assertTrue($this->model->getIsExists() === true);
    }

    public function testToArray()
    {
        $this->model->fill([
            '_id' => 'id',
            'field' => 'dummy',
        ]);
        $this->assertEquals(
            [
                'field' => 'dummy',
            ],
            $this->model->toArray()
        );
    }

    public function test_set()
    {
        $this->model->key = 'value';
        $this->assertEquals(
            [
                'key' => 'value',
            ],
            $this->model->toArray()
        );

        $this->model->_id = 'id';
        $this->assertEquals(
            [
                'key' => 'value',
            ],
            $this->model->toArray()
        );
    }

    public function test_get()
    {
        $this->model->key = 'value';
        $this->assertEquals(
            'value',
            $this->model->key
        );

        $this->assertNull($this->model->dummy);
    }

    public function test_isset()
    {
        $this->model->key = 'value';
        $this->assertTrue(isset($this->model->key));
        $this->assertFalse(isset($this->model->dummy));
    }

    public function test_call()
    {
        try {
            $this->assertTrue($this->model->index('index') instanceof Builder);
        } catch (\Exception $e) {
            $this->fail('Unbale to get builder class');
        }
    }

    public function testNewQuery()
    {
        $this->model->setIndex('index');
        $this->model->setType('type');
        $this->model->setMappings(['dummy']);
        $this->assertTrue($this->model->newQuery() instanceof Builder);
    }

    public function testSave()
    {
        $this->waitReady();
        $this->model->createIndex();
        $this->model->refreshIndex();
        $this->model->fill([
            '_id' => 'id',
            'field' => 'dummy1',
        ]);

        // insert
        $this->assertTrue($this->model->save('wait_for') instanceof ModelExample);
        $this->assertEquals([
            'field' => 'dummy1',
        ], $this->model->toArray());
        $this->assertEquals('id', $this->model->getId());
        $this->assertEquals('dummy1', $this->model->field);

        // update
        $this->model->field = 'dummy2';
        $this->assertTrue($this->model->save('wait_for') instanceof ModelExample);
        $this->assertEquals([
            'field' => 'dummy2',
        ], $this->model->toArray());
        $this->assertEquals('id', $this->model->getId());
        $this->assertEquals('dummy2', $this->model->field);

        $this->model->dropIndex();
    }

    public function testDelete()
    {
        $this->waitReady();
        $this->model->createIndex();
        $this->model->refreshIndex();
        $this->model->fill([
            '_id' => 'id',
            'field' => 'dummy',
        ]);

        // insert
        $this->assertTrue($this->model->save('wait_for') instanceof ModelExample);
        $this->assertEquals(1, $this->model->count());

        // delete
        $this->assertTrue($this->model->delete('wait_for') instanceof ModelExample);
        $this->assertEquals(0, $this->model->count());

        // delete
        $this->assertFalse($this->model->delete('wait_for'));

        $this->model->dropIndex();
    }

    private function waitReady()
    {
        // Wait, if index is not exists
        while ($this->model->existsIndex()) {
            sleep(1);
        }
    }
}
