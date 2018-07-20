<?php

namespace Osushi\ElasticsearchOrm\Providers;

use Illuminate\Support\ServiceProvider;
use Osushi\ElasticsearchOrm\Connection;

class ElasticsearchOrmServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(
            dirname(__FILE__) . '/../../config/elasticsearch.php',
            'elasticsearch'
        );
        $this->publishes([
            __DIR__.'/../../config/elasticsearch.php' => config_path('elasticsearch.php'),
        ]);
    }

    public function register()
    {
        $this->app->bind('elasticsearch', function () {
            return new Connection();
        });
    }
}
