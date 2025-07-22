<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use Illuminate\Support\Facades\Schema;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    $verticalMenuJson = file_get_contents(base_path('resources/menu/verticalMenu.json'));
    $verticalMenuData = json_decode($verticalMenuJson);
    $count = Product::whereColumn('stock_cm', '<', 'minimum_stock_cm')->count();
    \Log::info($count);

    // Share all menuData to all the views
    $this->app->make('view')->share('menuData', [$verticalMenuData]);
    $this->app->make('view')->share('need_to_kulak_products', $count);
  }
}
