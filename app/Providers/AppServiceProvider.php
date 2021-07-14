<?php

namespace App\Providers;

use App\Database\Migration\CustomBlueprint;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('db.custom.schema', function ($app) {
            $schema = $app['db']->connection()->getSchemaBuilder();
            $schema->blueprintResolver(function ($table, $callback) {
                return new CustomBlueprint($table, $callback);
            });
            return $schema;
        });
    }
}
