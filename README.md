## ElasticsearchOrm
[![Packagist](https://img.shields.io/packagist/v/osushi/elasticsearch-orm.svg)](https://packagist.org/packages/osushi/elasticsearch-orm)

## Requirements

- `php` >= 7.0.0

## Installation

### Laravel Installation

##### 1) Install package using composer.

```bash
$ composer require osushi/elasticsearch-orm:~{version}
```

### Version Metrics

||Elasticsearch 5.6.x|Elasticsearch 6.2.x|Elasticsearch 6.3.x|Elasticsearch 6.4.x|Elasticsearch 6.5.x|
|:---:|:---:|:---:|:---:|:---:|:---:|
|Laravel 5.5.x|~5.6.0|~6.2.0|~6.3.0|~6.4.0|~6.5.0|
|Laravel 5.6.x|~5.6.0|~6.2.0|~6.3.0|~6.4.0|~6.5.0|
|Laravel 5.7.x|~5.6.0|~6.2.0|~6.3.0|~6.4.0|~6.5.0|

e.g.
```bash
$ composer require osushi/elasticsearch-orm:~6.2.0
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

## Documents
- https://github.com/Osushi/ElasticsearchOrm/wiki

## Test

This package's coverage is **100%**

```bash
# Laravel55
$ COMPOSER=composer-laravel55.json php composer.phar update
$ vendor/bin/phpunit

# Laravel56
$ COMPOSER=composer-laravel56.json php composer.phar update
$ vendor/bin/phpunit

# Laravel57
$ COMPOSER=composer-laravel57.json php composer.phar update
$ vendor/bin/phpunit
```

## Inspire packages
- https://github.com/basemkhirat/elasticsearch 

## License
MIT

