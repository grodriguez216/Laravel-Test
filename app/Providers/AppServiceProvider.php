<?php

namespace App\Providers;

date_default_timezone_set('America/Costa_Rica');

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
  /**
  * Bootstrap any application services.
  *
  * @return void
  */
  public function boot()
  {
    Schema::defaultStringLength(191);
    View::share('appname', 'PrestaControl' );
  }
  
  /**
  * Register any application services.
  *
  * @return void
  */
  public function register()
  {
    //
  }
}
