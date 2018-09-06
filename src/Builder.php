<?php

namespace Osushi\ElasticsearchOrm;

use Elasticsearch\Client;
use Osushi\ElasticsearchOrm\Model;
use Osushi\ElasticsearchOrm\Requests\Request;
use Osushi\ElasticsearchOrm\Responses\Response;
use Osushi\ElasticsearchOrm\Queries\Query;
use Osushi\ElasticsearchOrm\Queries\Index\Create;
use Osushi\ElasticsearchOrm\Queries\Index\Drop;
use Osushi\ElasticsearchOrm\Queries\Index\Exist;
use Osushi\ElasticsearchOrm\Queries\Index\Refresh;
use Osushi\ElasticsearchOrm\Queries\Document\Insert;
use Osushi\ElasticsearchOrm\Queries\Document\Update;
use Osushi\ElasticsearchOrm\Queries\Document\Delete;
use Osushi\ElasticsearchOrm\Queries\Document\Bulk;
use Osushi\ElasticsearchOrm\Queries\Search\Get;
use Osushi\ElasticsearchOrm\Queries\Search\ClearScroll;
use Osushi\ElasticsearchOrm\Queries\Search\Count;
use Osushi\ElasticsearchOrm\Queries\Search\InnerHits;
use Osushi\ElasticsearchOrm\Queries\Aggregation\Aggregation;

class Builder
{
    const OPERATORS = [
        '=', '!=', '>', '>=', '<', '<=',
        'like',
    ];

    private $connection;

    private $index;

    private $type;

    private $mappings = [];

    private $model;

    private $refresh = false;

    private $conditions = [];

    private $filter = [];

    private $must = [];

    private $scroll;

    private $scrollId;

    private $take = 10;

    private $skip = 0;

    private $sort = [];

    private $select = [];

    private $collapse = [];

    private $aggregation = [];

    public function __construct(
        Client $connection
    ) {
        $this->connection = $connection;
    }

    public function index(
        string $index
    ) {
        $this->index = $index;
        return $this;
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function type(
        string $type
    ) {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function mappings(
        array $mappings
    ) {
        $this->mappings = $mappings;
        return $this;
    }

    public function getMappings()
    {
        return $this->mappings;
    }

    public function setModel(
        Model $model
    ) {
        $this->model = $model;
        return $this;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function refresh(
        $action = false
    ) {
        if (!in_array($action, [true, false, 'wait_for'], true)) {
            throw new \Exception('refresh() supports only true(boolean), false(boolean) and wait_for(string)');
        }
        $this->refresh = $action;
        return $this;
    }

    public function getRefresh()
    {
        return $this->refresh;
    }

    public function getConditions()
    {
        if (count($this->select)) {
            $this->conditions['_source'] = array_unique($this->getSelect());
        }

        if (count($this->must)) {
            $this->conditions['query']['bool']['must'] = $this->must;
        }

        if (count($this->filter)) {
            $this->conditions['query']['bool']['filter'] = $this->filter;
        }

        if (count($this->sort)) {
            $this->conditions['sort'] = array_unique($this->getOrderBy());
        }

        if (count($this->collapse)) {
            $this->conditions['collapse'] = $this->getCollapse();
        }

        if (count($this->aggregation)) {
            $this->conditions['aggregations'] = $this->getAggregation();
        }

        return $this->conditions;
    }

    // Index
    public function createIndex(
        $callback = false
    ) {
        $create = new Create($callback);
        $create->index($this->getIndex())
            ->mappings($this->getMappings());

        return $this->execute('index.create', $create);
    }

    public function dropIndex()
    {
        $drop = new Drop();
        $drop->index($this->getIndex());

        return $this->execute('index.drop', $drop);
    }

    public function existsIndex()
    {
        $exist = new Exist();
        $exist->index($this->getIndex());

        return $this->execute('index.exist', $exist);
    }

    public function refreshIndex()
    {
        $refresh = new Refresh();
        $refresh->index($this->getIndex());

        return $this->execute('index.refresh', $refresh);
    }

    // Document
    public function insert(
        array $attributes,
        ?string $id = null
    ) {
        $insert = new Insert();
        $insert->refresh($this->getRefresh())
            ->id($id)
            ->index($this->getIndex())
            ->type($this->getType())
            ->attributes($attributes);

        return $this->execute('document.insert', $insert);
    }

    public function update(
        array $attributes,
        string $id
    ) {
        $update = new Update();
        $update->refresh($this->getRefresh())
            ->id($id)
            ->index($this->getIndex())
            ->type($this->getType())
            ->attributes($attributes);

        return $this->execute('document.update', $update);
    }

    public function delete(
        ?string $id = null
    ) {
        $delete = new Delete();
        $delete->refresh($this->getRefresh())
            ->id($id)
            ->index($this->getIndex())
            ->type($this->getType())
            ->conditions($this->getConditions());

        return $this->execute('document.delete', $delete);
    }

    public function bulk(
        $data
    ) {
        $bulk = new Bulk($data, $this->getRefresh());
        $bulk->baseIndex($this->getIndex())
            ->baseType($this->getType());

        return $this->execute('document.bulk', $bulk);
    }

    // Search
    public function get()
    {
        $get = new Get();
        $get->index($this->getIndex())
            ->type($this->getType())
            ->take($this->getTake())
            ->skip($this->getSkip())
            ->scroll($this->getScroll())
            ->scrollId($this->getScrollId())
            ->conditions($this->getConditions());

        return $this->execute('search.get', $get);
    }

    public function clearScroll()
    {
        $clearScroll = new ClearScroll();
        $clearScroll->scrollId($this->getScrollId());

        return $this->execute('search.clear.scroll', $clearScroll);
    }

    public function count()
    {
        $count = new Count();
        $count->index($this->getIndex())
            ->type($this->getType())
            ->conditions($this->getConditions());

        return $this->execute('search.count', $count);
    }

    // Conditions
    public function where(
        string $name,
        string $operator = '=',
        $value = null
    ) {
        if (!$this->isOperator($operator)) {
            $value = $operator;
            $operator = '=';
        }

        if ($operator === '=') {
            $this->filter[] = [
                'term' => [
                    $name => $value
                ],
            ];
        }

        if ($operator === '>') {
            $this->filter[] = [
                'range' => [
                    $name => ['gt' => $value],
                ],
            ];
        }

        if ($operator === '>=') {
            $this->filter[] = [
                'range' => [
                    $name => ['gte' => $value],
                ],
            ];
        }

        if ($operator === '<') {
            $this->filter[] = [
                'range' => [
                    $name => ['lt' => $value],
                ],
            ];
        }

        if ($operator === '<=') {
            $this->filter[] = [
                'range' => [
                    $name => ['lte' => $value],
                ],
            ];
        }

        if ($operator === 'like') {
            $this->must[] = [
                'match' => [
                    $name => $value,
                ],
            ];
        }

        return $this;
    }

    public function whereIn(
        string $name,
        array $value
    ) {
        $this->filter[] = [
            'terms' => [
                $name => $value,
            ],
        ];
        return $this;
    }

    protected function isOperator(
        string $operator
    ) {
        if (in_array($operator, self::OPERATORS)) {
            return true;
        }
        return false;
    }

    public function scroll(
        string $scroll
    ) {
        $this->scroll = $scroll;
        return $this;
    }

    public function getScroll()
    {
        return $this->scroll;
    }

    public function take(
        int $take
    ) {
        $this->take = $take;
        return $this;
    }

    public function getTake()
    {
        return $this->take;
    }

    public function skip(
        int $skip
    ) {
        $this->skip = $skip;
        return $this;
    }

    public function getSkip()
    {
        return $this->skip;
    }

    public function scrollId(
        string $scrollId
    ) {
        $this->scrollId = $scrollId;
        return $this;
    }

    public function getScrollId()
    {
        return $this->scrollId;
    }

    public function orderBy(
        string $field,
        string $order = 'asc'
    ) {
        $this->sort[] = [
            $field => $order
        ];
        return $this;
    }

    public function getOrderBy()
    {
        return $this->sort;
    }

    public function select()
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            if (is_array($arg)) {
                $this->select = array_merge(
                    $this->select,
                    $arg
                );
            } else {
                $this->select[] = $arg;
            }
        }

        return $this;
    }

    public function getSelect()
    {
        return $this->select;
    }

    public function collapse(
        string $field,
        ?\Closure $callback = null
    ) {
        $this->collapse = [
            'field' => $field,
        ];

        if ($callback && is_callback_function($callback)) {
            $innerHits = new InnerHits();
            $callback($innerHits);
            $this->collapse['inner_hits'] = $innerHits->build();
        }

        return $this;
    }

    public function getCollapse()
    {
        return $this->collapse;
    }

    public function aggs(
        string $name,
        \Closure $callback
    ) {
        return $this->aggregation($name, $callback);
    }

    public function aggregation(
        string $name,
        \Closure $callback
    ) {
        $aggregation = new Aggregation($name);
        $callback($aggregation);

        $this->aggregation = array_merge(
            $this->aggregation,
            $aggregation->build()
        );

        return $this;
    }

    public function getAggregation()
    {
        return $this->aggregation;
    }

    // Extra
    public function raw()
    {
        return $this->connection;
    }

    protected function execute(
        string $requestType,
        Query $query
    ) {
        $requestTypes = explode('.', $requestType);
        $request = new Request($this->connection);
        $results = $request
                 ->setParams($query->build())
                 ->setRequestTypes($requestTypes)
                 ->request();

        $response = new Response($results);
        $response->setRequestTypes($requestTypes);
        if ($model = $this->getModel()) {
            $response->setModel($this->getModel());
        }

        $this->reset();
        return $response->make();
    }

    public function reset()
    {
        $this->refresh = false;
        $this->conditions = [];
        $this->filter = [];
        $this->must = [];
        $this->scroll = null;
        $this->scrollId = null;
        $this->take = 10;
        $this->skip = 0;
        $this->sort = [];
        $this->select = [];
        $this->collapse = [];
        $this->aggregation = [];
    }
}
