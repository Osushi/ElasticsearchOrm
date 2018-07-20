<?php

namespace Osushi\ElasticsearchOrm;

use Elasticsearch\ClientBuilder;

class Connection
{
    private $connection;

    private $connections = [];

    private $config;

    public function __construct()
    {
        $this->config = config('elasticsearch');
    }

    public static function build(array $config)
    {
        $client = ClientBuilder::create();
        $client->setHosts($config['servers']);
        if (array_get($config, 'logging.enabled')) {
            $logger = ClientBuilder::defaultLogger(array_get($config, 'logging.location'), array_get($config, 'logging.level', 'all'));
            $client->setLogger($logger);
        }
        return new Query($client->build());
    }

    public function connection(string $name)
    {
        if ($this->isLoaded($name)) {
            $this->connection = $this->connections[$name];
            return $this->newQuery($name);
        }

        if (array_key_exists($name, $this->config['connections'])) {
            $config = $this->config['connections'][$name];

            $client = ClientBuilder::create();
            $client->setHosts($config['servers']);
            if (array_get($config, 'logging.enabled')) {
                $logger = ClientBuilder::defaultLogger(array_get($config, 'logging.location'), array_get($config, 'logging.level', 'all'));
                $client->setLogger($logger);
            }
            $connection = $client->build();

            $this->connection = $connection;
            $this->connections[$name] = $connection;

            return $this->newQuery($name);
        }

        throw new \Exception("Invalid elasticsearch connection driver `" . $name . "`");
    }

    public function isLoaded(string $name)
    {
        if (array_key_exists($name, $this->connections)) {
            return true;
        }
        return false;
    }

    public function newQuery(string $name)
    {
        $config = $this->config["connections"][$name];

        $query = new Query($this->connections[$name]);
        return $query;
    }

    public function __call(string $name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $arguments);
        } else {
            $query = $this->connection($this->config['default']);
            return call_user_func_array([$query, $name], $arguments);
        }
    }
}
