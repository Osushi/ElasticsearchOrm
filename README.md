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
                'my_field' => [
                    'type' => 'string',
                ],
            ]
        ]
    ]);

});

[client]
$es->index("index")->create(function($index){
        
    $index->shards(5)->replicas(1)->mapping([
        'my_type' => [
            'properties' => [
                'my_field' => [
                    'type' => 'string',
                ],
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
# or
$sample->fill(['_id' => 'id', 'field' => 'test']);
$sample->save();
    
[client]
$es->index('index')->type('type')->insert(['field' => 'test'], 'id');
# or
$es->index('index')->type('type')->id('id')->insert(['field' => 'test']);
```

#### Bulk insert options

```php
[model]
$this->sample->bulk([
    ['field' => 'test1', '_id' => 'id'],
    ['field' => 'test2'], # Attach automatic ids
]);

[client]
$es->index('index')->type('type')->bulk(function ($bulk) {

    $bulk->id('id')->insert(["field" => "test1"]);
    $bulk->insert(["field" => "test2"]); # Attach automatic ids

});
```

#### Delete a document

```php
[model]
$this->sample->id('id')->delete();
# or
$samples = $this->sample->get();
foreach ($samples as $sample) {
    $sample->delete();
}

[client]
$es->index('index')->type('type')->id('id')->delete();
```

#### Delete documents by queries
```
[model]
$this->sample->bulk([
    ['field1' => 'test', 'field2' => 1],
    ['field1' => 'test', 'field2' => 2],
    ['field1' => 'test', 'field2' => 3],
    ['field1' => 'test', 'field2' => 4],
    ['field1' => 'test', 'field2' => 5],
]);
$this->sample->where('field2', '>', 1)->deleteByQuery();
/*
Results: 
$this->sample->get()->toArray();
-> ['field1' => 'test', 'field2' => 1]
*/
[client]
// ...
```


#### Get documents

```php
[model]
$this->sample->get();

[client]
$es->index('index')->type('type')->get();
```

#### Select only fields

```php
[model]
$this->sample->select('field')->get();

[client]
$es->index('index')->type('type')->select('field')->get();
```

#### Where clause

```php
[model]
$this->sample->where('field', '=', 'test1')->get();

[client]
$es->index('index')->type('type')->where('field', '=', 'test1')->get();
```

Here are supported operators:
```php
[
  '=', '!=', '>', '>=', '<', '<=',
  'like',
]
# Default operator is '='.
# ex. where('field', 'value');
```

#### Where in clause

```php
[model]
$this->sample->whereIn('field', ['test1'])->get();

[client]
$es->index('index')->type('type')->whereIn('field', ['test1'])->get();
```

#### Sorting

```php
[model]
$this->sample->orderBy('created_at', 'desc')->get();

[client]
$es->index('index')->type('type')->->orderBy('created_at')->get(); # order is `asc`
```

#### Limit and offset

```php
[model]
$this->sample->take(1)->skip(1)->get();

[client]
$es->index('index')->type('type')->take(1)->skip(1)->get();
```

#### Field Collapsing queries

https://www.elastic.co/guide/en/elasticsearch/reference/6.2/search-request-collapse.html#search-request-collapse

```php
$this->sample->bulk([
    ['field1' => 'test', 'field2' => 1],
    ['field1' => 'test', 'field2' => 2],
    ['field1' => 'test', 'field2' => 3],
    ['field1' => 'test', 'field2' => 4],
    ['field1' => 'test', 'field2' => 5],
]);

[model]
$res = $this->sample->collapse('field1.keyword', function ($innerHits) {
    $innerHits->name('name')->take(10)->skip(0)->orderBy('field2')->add();
})->get();

foreach ($res as $v) {
    dd($v->getInnerHit('name')->toArray());
    /*
    array:5 [▼
      0 => array:2 [▼
        "field1" => "test"
        "field2" => 1
      ]
      1 => array:2 [▼
        "field1" => "test"
        "field2" => 2
       ]
      2 => array:2 [▼
        "field1" => "test"
        "field2" => 3
      ]
      3 => array:2 [▼
        "field1" => "test"
        "field2" => 4
      ]
      4 => array:2 [▼
        "field1" => "test"
        "field2" => 5
      ]
    ]  
    */
    dd($v->getInnerHits());
    /*
    array:1 [▼
      "name" => Collection {#208 ▶}
    ]
    */
    dd($v->getFields());
    /*
    array:1 [▼
      "field1.keyword" => array:1 [▼
        0 => "test"
      ]
    ]
    */
}

[client]
// ...
```

#### Scroll queries
```php
[model]
$res = $this->sample->scroll('1m')->take(1)->get();
/*
dd($res);
Collection {#206 ▼
  #items: array:3 [▶]
  +"total": 3
  +"max_score": 1.0
  +"took": 2
  +"timed_out": false
  +"scroll_id": "DnF1ZXJ5VGhlbkZldGNoBQ.... ▶"
  +"shards": {#210 ▶}
}
*/

# Run scrolling
$scrollId = $res->scroll_id;
$this->sample->scroll('1m')->scrollId($scrollId)->get();

# Clear scrollId
$this->sample->scrollId($scrollId)->clear();

[client]
$res = $es->index('index')->type('type')->scroll('1m')->take(1)->get();
// ...
```

### aggregations
---

```php
[model]
$this->sample->bulk([
  ['product' => 1, 'price' => 200, 'category' => 1],
  ['product' => 1, 'price' => 100, 'category' => 1],
  ['product' => 1, 'price' => 50, 'category' => 2],
  ['product' => 2, 'price' => 200, 'category' => 1],
  ['product' => 2, 'price' => 100, 'category' => 1],
  ['product' => 2, 'price' => 50, 'category' => 2],
  ['product' => 3, 'price' => 200, 'category' => 1],
  ['product' => 3, 'price' => 100, 'category' => 1],
  ['product' => 3, 'price' => 50, 'category' => 2],
  ['product' => 4, 'price' => 200, 'category' => 1],
  ['product' => 4, 'price' => 100, 'category' => 1],
  ['product' => 4, 'price' => 50, 'category' => 2],
  ['product' => 5, 'price' => 200, 'category' => 1],
  ['product' => 5, 'price' => 100, 'category' => 1],
  ['product' => 5, 'price' => 50, 'category' => 2],
]);

$res = $this->sample->take(0)->aggs('products', function ($groups) {
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
})->get();

var_dump($res->aggregations->products);
/*
array (size=3)
  'doc_count_error_upper_bound' => int 0
  'sum_other_doc_count' => int 0
  'buckets' => 
    array (size=5)
      0 => 
        array (size=5)
          'key' => int 1
          'doc_count' => int 3
          'price_min' => 
            array (size=1)
              'value' => float 50
          'product::categories' => 
            array (size=3)
              'doc_count_error_upper_bound' => int 0
              'sum_other_doc_count' => int 0
              'buckets' => 
                array (size=2)
                  0 => 
                    array (size=2)
                      'key' => int 1
                      'doc_count' => int 2
                  1 => 
                    array (size=2)
                      'key' => int 2
                      'doc_count' => int 1
          'price_max' => 
            array (size=1)
              'value' => float 200
      1 => // ...
      2 => // ...
      3 => // ...
      4 => // ...
*/

[client]
// ...
```

Here are supportted aggregations.
+ Terms Aggregation - `terms(string $field, int $size = 10)`
+ Min Aggregation - `min(string $field)`
+ Max Aggregation - `max(string $field)`
+ Top Hits Aggregation - `topHits(array $sorts, array $columns = ['*'], int $size = 10, int $from = 0)`

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

