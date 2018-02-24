<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Models\Loan;
use App\Models\Client;
use App\Models\PayOrder;
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
      $notifier = new NotificationController;

      $loans_today = Loan::where('status', 1)
      ->where('next_due', date('Y-m-d'))
      ->get();
      
      if (!$loans_today) return true;
      foreach ($loans_today as $loan)
      {
        $po = new PayOrder;
        $po->loan_id = $loan->id;
        $po->date = $loan->next_due;
        $po->amount = $loan->intval;
        $po->balance = $po->amount;
        $po->save();
        
        /* Update the loan */
        $loan->delays++;
        $loan->save();

        $notifier->send( $loan->client->phone, 'PR', $loan );
      }
    })->dailyAt('08:00');
    
    $schedule->call(function ()
    {
      $notifier = new NotificationController;

      $loans_tomorrow = Loan::where('status', 1)
      ->where('next_due', date('Y-m-d', strtotime('+1 day')))
      ->get();

      if (!$loans_tomorrow) return true;

      foreach ($loans_tomorrow as $loan)
      {
        $notifier->send( $loan->client->phone, 'PR', $loan );
      }
    })->dailyAt('13:00');

    // $schedule->call(function ()
    // {      
    //   $loans_today = Loan::all();
    //   if (!$loans_today) return true;
    //   foreach ($loans_today as $loan)
    //   {
    //     $po = new PayOrder;
    //     $po->loan_id = $loan->id;
    //     $po->date = $loan->next_due;
    //     $po->amount = $loan->intval;
    //     $po->balance = $po->amount;
    //     $po->save();
    //     /* Update the loan */
    //     $loan->delays++;
    //     $loan->save();
    //   }
    // })->everyMinute();
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
