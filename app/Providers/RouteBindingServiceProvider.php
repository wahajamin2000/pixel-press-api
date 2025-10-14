<?php

namespace App\Providers;

use App\Http\Middleware\TrimStrings;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteBindingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Route::bind('userTrashed', function ($value) {
            return User::onlyTrashed()->where((new User())->getRouteKeyName(), $value)->firstOrFail();
        });

        Route::bind('productCategoryTrashed', function ($value) {
            return ProductCategory::onlyTrashed()->where((new ProductCategory())->getRouteKeyName(), $value)->firstOrFail();
        });

        Route::bind('productTrashed', function ($value) {
            return Product::onlyTrashed()->where((new Product())->getRouteKeyName(), $value)->firstOrFail();
        });

        if (request()->wantsJson())
        {
            //
        }
    }
}
