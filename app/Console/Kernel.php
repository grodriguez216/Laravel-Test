<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\Loan;
use App\Models\Client;
use App\Models\PayOrder;
use App\Http\Controllers\LoansController;
use App\Http\Controllers\NotificationController;

class Kernel extends ConsoleKernel
{
  /**
  * The Artisan commands provided by your application.
  *
  * @var array
  */
  protected $commands = [];
  
  /**
  * Define the application's command schedule.
  *
  * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
  * @return void
  */
  protected function schedule(Schedule $schedule)
  {

    $schedule->call(function ()
    {
      $controller = new LoansController();
      $controller->updateDaily();
    })->dailyAt('07:00');
    
    $schedule->call(function ()
    {
      $controller = new LoansController();
      $controller->rememberDaily();
    })->dailyAt('13:00');
  }
  
  /**
  * Register the Closure based commands for the application.
  *
  * @return void
  */
  protected function commands()
  {
    require base_path('routes/console.php');
  }
}
