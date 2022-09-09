<?php

namespace App\Providers;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\ServiceProvider;

class DynamoDbServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
//        DynamoDbModel::setDynamoDbClientService($this->app->make(DynamoDbClientInterface::class));

        $this->publishes([
            __DIR__.'/../config/dynamodb.php' => app()->basePath('config/dynamodb.php'),
        ]);
    }

    /**
     * Register the service provider.
     */
    public function register()
    {


        $this->app->singleton(DynamoDbClient::class, function () {
            $conf = config('dynamodb.connections.local');
            $conf['version'] = '2012-08-10';
            $conf['debug'] = false;
            return new DynamoDbClient($conf);
//            return new DynamoDbClientService();
        });

    }
}
