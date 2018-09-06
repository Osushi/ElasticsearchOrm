<?php

namespace Tests\Unit\Queries\Search;

use Osushi\ElasticsearchOrm\Queries\Search\InnerHits;
use Tests\TestCase;

class InnerHitsTest extends TestCase
{
    private $innerHits;

    public function setUp()
    {
        parent::setUp();

        $this->innerHits = new InnerHits();
    }

    public function testNameAndGetName()
    {
        $this->assertTrue($this->innerHits->name('name') instanceof InnerHits);
        $this->assertTrue($this->innerHits->getName() === 'name');
    }

    public function testTakeAndGetTake()
    {
        $this->assertTrue($this->innerHits->take(10) instanceof InnerHits);
        $this->assertTrue($this->innerHits->getTake() === 10);
    }

    public function testSkipAndGetSkip()
    {
        $this->assertTrue($this->innerHits->skip(10) instanceof InnerHits);
        $this->assertTrue($this->innerHits->getSkip() === 10);
    }

    public function testOrderByAndGetOrderBy()
    {
        $this->assertTrue($this->innerHits->orderBy('field1', 'desc') instanceof InnerHits);
        $this->assertTrue($this->innerHits->orderBy('field2') instanceof InnerHits);
        $this->assertTrue($this->innerHits->getOrderBy() === [
            ['field1' => 'desc'],
            ['field2' => 'asc']
        ]);
    }

    public function testSelectAndGetSelect()
    {
        $this->assertTrue($this->innerHits->select(['field1', 'field2']) instanceof InnerHits);
        $this->assertTrue($this->innerHits->getSelect() === ['field1', 'field2']);
        $this->assertTrue($this->innerHits->select('field3') instanceof InnerHits);
        $this->assertTrue($this->innerHits->getSelect() === ['field1', 'field2', 'field3']);
    }

    public function testAddAndBuildAndReset()
    {
        $this->innerHits = new InnerHits();

        $this->innerHits->name('name')
            ->take(10)
            ->skip(10)
            ->orderBy('field')
            ->select('field')
            ->add();

        // build
        $this->assertEquals([
            [
                'name' => 'name',
                'size' => 10,
                'from' => 10,
                'sort' => [
                    [
                        'field' => 'asc'
                    ],
                ],
                '_source' => ['field'],
            ],
        ], $this->innerHits->build());

        // reset
        $this->assertNull($this->innerHits->getName());
        $this->assertEquals(3, $this->innerHits->getTake());
        $this->assertEquals(0, $this->innerHits->getSkip());
        $this->assertEquals([], $this->innerHits->getSelect());
    }
}
