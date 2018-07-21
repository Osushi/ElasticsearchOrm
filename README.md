## [WIP] ElasticsearchOrm
[![Packagist](https://img.shields.io/packagist/v/osushi/elasticsearch-orm.svg)](https://packagist.org/packages/osushi/elasticsearch-orm)

## Requirements

- `php` >= 7.0.0
- `laravel/laravel` >= 5.*
- `elasticsearch` >= 6.*

## Installation

### Laravel Installation

##### 1) Install package using composer.

```bash
$ composer require osushi/elasticsearch-orm
```

##### 2) Add package service provider.

```php
[config/app.php]
Osushi\ElasticsearchOrm\Providers\ElasticsearchOrmServiceProvider::class
```
	
##### 3) Publishing.

```bash
$ php artisan vendor:publish --provider="Osushi\ElasticsearchOrm\Providers\ElasticsearchOrmServiceProvider"
``` 

## Elasticsearch data model

##### Basic usage
```php
<?php

namespace App;

use Osushi\ElasticsearchOrm\Model;

class Sample extends Model
{
    protected $index = 'index';
    protected $type = 'type';

    // protected $mappings = [];
    // protected $connection = 'default';
}

$sample = new Sample();
```

## Elasticsearch client

##### Basic usage
```php
<?php

require "vendor/autoload.php";

use Osushi\ElasticsearchOrm\Connection;

$es = Connection::build([
    'servers' => [
        [
            "host" => '127.0.0.1',
            "port" => 9200,
            'user' => '',
            'pass' => '',
            'scheme' => 'http',
        ],
    ],
    
    'index' => '',
    
    'logging' => [
        'enabled'   => env('ES_LOGGING_ENABLED',false),
        'level'     => env('ES_LOGGING_LEVEL','all'),
        'location'  => env('ES_LOGGING_LOCATION',base_path('storage/logs/elasticsearch.log'))
    ],  
]);
```

## Usage as a query builder

### Index
---

#### Creating a new index

```php
[model]
$sample->create();

[client]
$es->index("index")->create();
```
    
##### Creating index with custom options (optional)
   
```php
[model]
$sample->create(function($index){
        
    $index->shards(5)->replicas(1)->mapping([
        'my_type' => [
            'properties' => [
                'first_name' => [
                    'type' => 'string',
                ],
                'age' => [
                    'type' => 'integer'
                ]
            ]
        ]
    ]);

};

[client]
$es->index("index")->create(function($index){
        
    $index->shards(5)->replicas(1)->mapping([
        'my_type' => [
            'properties' => [
                'first_name' => [
                    'type' => 'string',
                ],
                'age' => [
                    'type' => 'integer'
                ]
            ]
        ]
    ]);

});
```

#### Dropping index

```php
[model]
$sample->drop();
    
[client]
$es->index("index")->drop();
```

#### Check index

```php
[model]
$sample->exists();
    
[client]
$es->index("index")->exists();
```

### Document
---

#### Insert a new document
    
```php
[model]
$sample->_id = 'id'; # optional
$sample->field = 'test';
$sample->save();
    
[client]
$es->index('index')->type('type')->insert(['field' => 'test'], 'id');
# or
$es->index('index')->type('type')->id('id')->insert(['field' => 'test']);
```

### Options
---

#### Get elasticsearch raw client
```php
[model]
$sample->raw(); # Elasticsearch\Client
    
[client]
$es->raw(); # Elasticsearch\Client
```

## Inspire packages
- https://github.com/basemkhirat/elasticsearch 

## License
MIT

