<?php

namespace Osushi\ElasticsearchOrm\Requests;

use Elasticsearch\Client;

class Request
{
    private $connection;

    private $params = [];

    private $requestTypes = [];

    public function __construct(
        Client $connection
    ) {
        $this->connection = $connection;
    }

    public function setParams(
        array $params = []
    ) {
        $this->params = $params;
        return $this;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setRequestTypes(
        array $requestTypes
    ) {
        $this->requestTypes = $requestTypes;
        return $this;
    }

    public function getRequestTypes()
    {
        return $this->requestTypes;
    }

    public function request()
    {
        switch ($this->requestTypes[0]) {
        case 'index':
            if ($this->requestTypes[1] === 'create') {
                return $this->connection
                    ->indices()
                    ->create($this->getParams());
            }
            if ($this->requestTypes[1] === 'drop') {
                return $this->connection
                    ->indices()
                    ->delete($this->getParams());
            }
            if ($this->requestTypes[1] === 'exist') {
                return $this->connection
                    ->indices()
                    ->exists($this->getParams());
            }
            if ($this->requestTypes[1] === 'refresh') {
                return $this->connection
                    ->indices()
                    ->refresh($this->getParams());
            }
            break;
        case 'document':
            if ($this->requestTypes[1] === 'insert') {
                return $this->connection
                    ->index($this->getParams());
            }
            if ($this->requestTypes[1] === 'update') {
                return $this->connection
                    ->update($this->getParams());
            }
            if ($this->requestTypes[1] === 'delete') {
                $params = $this->getParams();
                if (isset($params['body'])) {
                    return $this->connection->deleteByQuery($params);
                }

                return $this->connection
                    ->delete($params);
            }
            if ($this->requestTypes[1] === 'bulk') {
                return $this->connection
                    ->bulk($this->getParams());
            }
            break;
        case 'search':
            if ($this->requestTypes[1] === 'get') {
                $params = $this->getParams();
                if (isset($params['scroll_id'])) {
                    return $this->connection->scroll($params);
                }

                return $this->connection
                    ->search($params);
            }
            if ($this->requestTypes[1] === 'clear') {
                if (isset($this->requestTypes[2]) && $this->requestTypes[2] === 'scroll') {
                    return $this->connection->clearScroll($this->getParams());
                }
            }
            if ($this->requestTypes[1] === 'count') {
                return $this->connection->count($this->getParams());
            }
            break;
        }

        throw new \Exception('Invalid request type');
    }
}
