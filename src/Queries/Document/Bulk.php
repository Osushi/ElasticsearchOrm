<?php

namespace Osushi\ElasticsearchOrm\Queries\Document;

use Osushi\ElasticsearchOrm\Queries\Query;

class Bulk implements Query
{
    private $data;

    private $buffers = [];

    private $id;

    private $baseIndex;

    private $baseType;

    private $index;

    private $type;

    private $refresh = false;

    public function __construct(
        $data,
        $refresh = false
    ) {
        $this->data = $data;
        $this->refresh = $refresh;
    }

    public function baseIndex(
        string $baseIndex
    ) {
        $this->baseIndex = $baseIndex;
        return $this;
    }

    public function id(
        string $id
    ) {
        $this->id = $id;
        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBaseIndex()
    {
        return $this->baseIndex;
    }

    public function index(
        string $index
    ) {
        $this->index = $index;
        return $this;
    }

    public function getIndex()
    {
        return $this->index ? $this->index : $this->getBaseIndex();
    }

    public function baseType(
        string $baseType
    ) {
        $this->baseType = $baseType;
        return $this;
    }

    public function getBaseType()
    {
        return $this->baseType;
    }

    public function type(
        string $type
    ) {
        $this->type = $type;
        return $this;
    }

    public function getType()
    {
        return $this->type ? $this->type : $this->getBaseType();
    }

    public function getRefresh()
    {
        return $this->refresh;
    }

    public function insert(
        array $data
    ) {
        $this->action('index', $data);
    }

    public function delete()
    {
        $this->action('delete', []);
    }

    public function update(
        array $data
    ) {
        $this->action('update', $data);
    }

    protected function action(
        string $action,
        array $data
    ) {
        $header = [
            $action => [
                '_index' => $this->getIndex(),
                '_type' => $this->getType(),
            ]
        ];
        if (array_key_exists('_id', $data)) {
            $header[$action]['_id'] = $data['_id'];
            unset($data['_id']);
        } elseif ($id = $this->getId()) {
            $header[$action]['_id'] = $id;
        }

        $this->buffers[] = $header;

        if (!empty($data)) {
            if ($action === 'update') {
                $this->buffers[] = ['doc' => $data];
            } else {
                $this->buffers[] = $data;
            }
        }

        $this->reset();
    }

    protected function reset()
    {
        $this->index = null;
        $this->type = null;
        $this->id = null;
    }

    protected function getBuffers()
    {
        return $this->buffers;
    }

    public function build(): array
    {
        if (is_callback_function($this->data)) {
            $data = $this->data;
            $data($this);
        } else {
            foreach ($this->data as $data) {
                $this->insert($data);
            }
        }

        $params = [
            'body' => $this->getBuffers(),
        ];

        if ($refresh = $this->getRefresh()) {
            $params['refresh'] = $refresh;
        }

        return $params;
    }
}
