<?php

namespace App;

use App\Models\Loan;
use App\Models\Client;
use App\Http\Controllers\NotificationController;

class Settings
{
  /* Send an sms to every person whose payment date is today */
  public static function remind_today( $lastnum = 9)
  {
    $notifier = new NotificationController;
    
    $loans_today = Loan::where('next_due', date('Y-m-d'))->get();

    if ($loans_today)
    {
      foreach ($loans_today as $loan)
      {
      //if (substr( $loan->client->phone, -1) == $lastnum)
        $notifier->notify( $loan->client->phone, 'PR', $loan );
      }
    }
  }
  print_r( $loans_today );
}

/* Send an sms to every person whose payment date is tomorrow */
public static function remind_tomorrow()
{


}


}
