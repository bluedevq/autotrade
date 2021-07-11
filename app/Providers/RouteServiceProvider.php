<?php

namespace App\Providers;

use App\Helper\Common;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

/**
 * Class RouteServiceProvider
 * @package App\Providers
 */
class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\Http\Controllers';

    public function boot()
    {
        parent::boot();
    }

    public function map()
    {
        $this->mapBackendRoutes();
        $this->mapFrontendRoutes();
        $this->mapApiRoutes();
    }

    protected function mapBackendRoutes()
    {
        Route::middleware('backend')
            ->prefix(Common::getBackendAlias())
            ->namespace($this->namespace . '\\Module\\' . Common::getBackendNamespace())
            ->domain(Common::getBackendDomain())
            ->group(base_path('routes/backend.php'));
    }

    protected function mapFrontendRoutes()
    {
        Route::middleware('frontend')
            ->prefix(Common::getFrontendAlias())
            ->namespace($this->namespace . '\\Module\\' . Common::getFrontendNamespace())
            ->domain(Common::getFrontendDomain())
            ->group(base_path('routes/frontend.php'));
    }

    protected function mapApiRoutes()
    {
        Route::middleware('api')
//            ->domain(Common::getApiDomain())
            ->prefix(Common::getApiAlias())
            ->namespace($this->namespace . Common::getApiNamespace())
            ->group(base_path('routes/api.php'));
    }
}
