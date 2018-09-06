<?php

namespace Osushi\ElasticsearchOrm;

use Elasticsearch\ClientBuilder;

class Connection
{
    private $config = [];

    private $connection;

    private $connections = [];

    public function __construct()
    {
        $this->config = config('elasticsearch');
    }

    public function isLoaded(
        string $name
    ) {
        if (array_key_exists($name, $this->connections)) {
            return true;
        }
        return false;
    }

    protected function newQuery(
        string $name
    ) {
        return new Builder($this->connections[$name]);
    }

    public static function create(
        array $config
    ) {
        $client = ClientBuilder::create();
        $client->setHosts($config['servers']);
        if (array_get($config, 'logging.enabled')) {
            $logger = ClientBuilder::defaultLogger(
                array_get($config, 'logging.location'),
                array_get($config, 'logging.level', 'all')
            );
            $client->setLogger($logger);
        }
        return new Builder($client->build());
    }

    public function connect(
        string $name
    ) {
        if ($this->isLoaded($name)) {
            $this->connection = $this->connections[$name];
            return $this->newQuery($name);
        }

        if (array_key_exists($name, $this->config['connections'])) {
            $config = $this->config['connections'][$name];

            $client = ClientBuilder::create();
            $client->setHosts($config['servers']);
            if (array_get($config, 'logging.enabled')) {
                $logger = ClientBuilder::defaultLogger(
                    array_get($config, 'logging.location'),
                    array_get($config, 'logging.level', 'all')
                );
                $client->setLogger($logger);
            }
            $connection = $client->build();

            $this->connection = $connection;
            $this->connections[$name] = $connection;

            return $this->newQuery($name);
        }

        throw new \Exception('Invalid elasticsearch connection driver `' . $name . '`');
    }
}
