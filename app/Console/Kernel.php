<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\Loan;
use App\Models\Client;
use App\Http\Controllers\NotificationController;

class Kernel extends ConsoleKernel
{
  /**
  * The Artisan commands provided by your application.
  *
  * @var array
  */
  protected $commands = [
    //
  ];
  
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
      $notifier = new NotificationController;
      
      $loans_today = Loan::where('next_due', date('Y-m-d'))->get();
      
      if (!$loans_today) return true;
      
      foreach ($loans_today as $loan)
      $notifier->notify( $loan->client->phone, 'PR', $loan );
      
    })->dailyAt('09:00');
    
    
    $schedule->call(function ()
    {
      $notifier = new NotificationController;
      
      $loans_today = Loan::where('next_due', date('Y-m-d', strtotime('+1 day')))->get();
      
      if (!$loans_today) return true;
      
      foreach ($loans_today as $loan)
      $notifier->notify( $loan->client->phone, 'PR', $loan );
      
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
