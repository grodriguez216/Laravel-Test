<?php

namespace App\Http\Controllers;

use Validator;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\Loan;
use App\Models\Zones;
use App\Models\Payment;
use App\Models\PayOrder;
use App\Models\PayRoll;
use App\Models\Assignments;
use App\User;

use App\Http\Controllers\NotificationController;

class LoansController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');

    date_default_timezone_set('America/Costa_Rica');
  }

  public function showCreateForm()
  {
    $zones = \App\Models\Zones::all();
    return view('new', [ 'zones' => $zones ] );
  }

  public function createNewLoan(Request $request)
  {
    /* [START Check the user-input] */
    $this->validateRequest($request);
    /* [ END Check the user-input] */

    /* [START Instantiante the new loan] */
    $loan = new Loan;
    /* [ END Instantiante the new loan] */

    DB::beginTransaction();

    /* [START Add or Find the client] */
    $contoller = new ClientsController();
    $client = $contoller->newClient( $request );
    $loan->client_id = $client->id; 
    /* [ END Add or Find the client] */

    /* [START Store the user-input values] */
    $loan->loaned = $request->input('loaned');
    $loan->balance = $request->input('balance');
    $loan->payable = $request->input('balance');
    $loan->firdue = $request->input('firdue');
    $loan->regdue = $request->input('regdue');
    $loan->mindue = $request->input('mindue');
    $loan->intrate = $request->input('intrate');
    $loan->intval = $request->input('intval');
    $loan->delays = $request->input('delays');
    $loan->duration = $request->input('duration');
    $loan->payplan = $request->input('payplan');
    $loan->paytime = $request->input('paytime');
    $loan->created_at = $request->input('date');
    /* [ END Store the user-input values] */

    /* [START Set default values ] */
    $loan->duemod = 0;
    /* [ END Set default values ] */

    /* [START Nicefy interests ] */
    $loan->intval = $this->nicecify( $loan->intval );
    /* [ END Nicefy interests ] */

    /* [START Estimated due date calculation] */
    $loan->due_date = $this->getDueDate( $loan->payplan, $loan->duration, $loan->delays, $loan->created_at );
    /* [ END Estimated due date calculation] */

    /* [START Next due date calculation ] */
    $fixdue = $request->input('next_due', 0);
    if ( $fixdue )
    {
      $loan->next_due = $fixdue;
      $loan->collect_date = $fixdue;
    }
    else
    {
      $loan->next_due = $this->getNextPeriod( $loan->payplan, $loan->created_at );  
      $loan->collect_date = $loan->next_due;
    }
    /* [ END Next due date calculation ] */

    /* [START Commit changes] */
    $loan->save();
    /* [ END Commit changes] */

    /* [START Add peding orders] */
    for ($index=0; $index < $loan->delays; $index++)
    { 
      $order = new PayOrder;
      $order->loan_id = $loan->id;
      $order->amount = $loan->mindue;
      $order->balance = $loan->mindue;
      $order->date = $loan->created_at;
      $order->save();
    }
    /* [ END Add peding orders] */

    DB::commit();

    if ( getenv('APP_ENV') !== 'local' )
    {
      /* [START New loan notification] */
      $notifications = new NotificationController;
      $notifications->send( $client->phone, 'NL', $loan );
      /* [ END New loan notification] */
    }

    return redirect("clientes/perfil/{$client->id}");
  }

  private function validateRequest(Request $request)
  {
    $rules =
    [
      'first_name' => 'required',
      'last_name' => 'required',
      'phone' => 'required',
    ];

    $messages =
    [
      'first_name.required' => 'El nombre no puede ser vacio',
      'last_name.required' => 'El apellido no puede ser vacio',
      'phone.required' => 'El telefono no puede ser vacio',
    ];

    $validator = Validator::make( $request->all(), $rules, $messages);
    if ( $validator->fails() )
    {
      return redirect('prestamos/agregar')->withErrors($validator)->withInput();
    }
  }

  private function getNextPeriod( $plan, $base = false, $num = 1 )
  {
    /* set the relative base date-time */
    $base = $base ? strtotime($base) : time();

    /* End of month relative to base time */
    $EOM = date('m', $base ) == 2 ? 28 : 30;

    $next_due_ts;

    switch ( $plan )
    {
      case 'we':
      /* Simply add 1 week from today on */
      $next_due_ts = strtotime( "+1 week", $base );
      break;

      case 'bw':
      $today = date('d', $base );
      $this_15 = strtotime( date('Y-m-15', $base) );
      $this_30 = strtotime( date('Y-m-'.$EOM, $base) );

      if ( $today >= $EOM )
      {
        /* Is past the end of the month, so next bill is the 15th of next month */
        $next_15 = strtotime( '+1 month', $this_15 );
        $next_due_ts = $next_15;
      }
      else if ( $today < 15)
      {
        /* Before the 15th of this month */
        $next_due_ts = $this_15;
      }
      else
      {
        /* Is past the 15th of the month, but before the EOM */
        $next_due_ts = $this_30;
      }
      break;

      case 'mo':
      /* Simply add 1 month from today on */
      $next_due_ts = strtotime('+1 month', $base);
      break;
    }

    if( $num == 1 )
    {
      /* format the date for export */
      return date('Y-m-d', $next_due_ts );
    }
    else
    {
      return $this->getNextPeriod( $plan, date('Y-m-d', $next_due_ts ), $num -1);
    }
  }

  private function getDueDate( $plan, $duration, $delays, $base = false )
  {
    /* set the relative base date-time */
    $base = $base ? strtotime($base) : time();

    /* increas the estimated location by the # of delayed payments */
    $duration += $delays;

    $due_date_ts;

    switch ( $plan )
    {
      case 'we':
      $due_date_ts = strtotime("+{$duration} weeks", $base);
      break;

      case 'bw':
      $duration *= 2;
      $due_date_ts = strtotime("+{$duration} weeks", $base);
      break;

      case 'mo':
      $due_date_ts = strtotime("+{$duration} months", $base);
      break;
    }

    /* format the date for export */
    return date('Y-m-d', $due_date_ts );
  }

  public function displayLoans($id)
  {
    $loanlist = Loan::where('client_id', $id)->where('status', 1)->get();

    foreach ($loanlist as $loan)
    {
      switch ( $loan->payplan )
      {
        case 'we':
        $loan->duration .= ' Semanas';
        break;
        case 'bw':
        $loan->duration .= ' Quincenas';
        break;
        case 'mo':
        $loan->duration .= ' Meses';
        break;
      }

      /* Round interests to the closest 1000 */
      $MINIMUN = $this->nicecify( $loan->mindue );
      $loan->mindue = $MINIMUN;

      /* Amount due */
      $DUE = $loan->firdue ? $loan->firdue : $loan->regdue;

      /* [START Apply discounts] */
      $loan->due = $DUE + $loan->duemod;
      $loan->mod = $DUE - $loan->due;
      /* [ END Apply discounts] */

      /* [START Format dates] */
      $collect_ts = strtotime( $loan->collect_date );
      $loan->next_due_display = date('d/m/Y', $collect_ts);
      $loan->next_due_display = $this->translateDay($collect_ts) . " " . $loan->next_due_display;

      $create_ts = strtotime( $loan->created_at );
      $loan->date = date('d/m/Y', $create_ts);
      $loan->date = $this->translateDay($create_ts) . " " . $loan->date;
      /* [ END Format dates] */

      /* [START Check pending bills] */
      $loan->pending = $loan->orders->where('status', 1)->sum('balance');
      /* [ END Check pending bills] */
    }
    return $loanlist;
  }

  public function updateDaily()
  {
    $today = date('Y-m-d');

    $loans_today = Loan::where('status', 1)
    ->where('next_due', '<=', $today)
    ->get();

    if ( $loans_today->isEmpty() )
      return true;

    foreach ($loans_today as $loan)
    {
      $next_due = $loan->next_due;

      if ( $loan->delays > 0 )
      {
        $next_due = $this->getNextPeriod( $loan->payplan, $loan->next_due, $loan->delays );
      }

      if ( $next_due === $today )
      {
        $po = new PayOrder;
        $po->loan_id = $loan->id;
        $po->date = $today;
        $po->amount = $loan->mindue;
        $po->balance = $po->amount;
        $po->save();

        /* Update the loan */
        $loan->delays++;
        $loan->save();
      }
    }
  }

  public function rememberDaily()
  {
    $notifier = new NotificationController;

    $loans_tomorrow = Loan::where('status', 1)
    ->where('next_due', '<=', date('Y-m-d', strtotime('+1 day')))
    ->get();

    if ( $loans_tomorrow->isEmpty() )
      return true;

    foreach ($loans_tomorrow as $loan)
    {
      $next_due = $loan->next_due;

      if ( $loan->delays )
        $next_due = $this->getNextPeriod( $loan->payplan, $loan->next_due, $loan->delays );

      if ( $next_due == date('Y-m-d') )
      {
        $notifier->send( $loan->client->phone, 'PR', $loan );  
      }

    }
  }

  private function calcPaymentFees()
  {
  }

  private function addPayments(Request $request, Loan $loan)
  {
    /* [store user input] */
    $TYPE = $request->input('type','OT');
    $CREDITS = $request->input('credits', 0);
    $MULTI = $request->input('multi', 0);

    $DUE = $loan->firdue ? $loan->firdue : $loan->regdue;

    $COLLECTOR = Auth::user();

    if( $COLLECTOR->id )
    {
      $COLLECTOR->payroll = Payroll::where('user_id', $COLLECTOR->id)->first(); 

      if( $loan->intrate > 0 )
      {
        $COLLECTOR->rate = $COLLECTOR->payroll->pay_rate;
      }
      else
      {
        $COLLECTOR->rate = 0.2;
      }
    }

    /* Instantiate the payment controller */
    $pc = new PaymentsController;

    /* Get all the peding orders this client has */
    $ORDERS = PayOrder::where('loan_id', $loan->id)->where('status', 1)->orderBy('date', 'id')->get();

    DB::beginTransaction();

    /* order currently being updated */
    $order_index = 0;

    /* Modifiable variable for the creditable amount */
    /* Making a complete payment (PC) implicitly pays 1 peding order. 
    So we add its equivalent for the numbers to add up */
    $credits = $CREDITS;

    $EXTRA = 0;

    if ( $TYPE === 'PC'
      && $loan->delays
      && $loan->intrate
      && $credits >= $DUE
    )
    {
      $EXTRA = $loan->mindue * $MULTI;
      $credits += $EXTRA;
    }

    $payment = $CREDITS;

    /* Whether the client has any orders pending to cancel */
    $hasPendings = $loan->delays;

    $collector_fee = 0;

    /* Cancel the orders before touching the loan balance */
    while ( $hasPendings && $ORDERS->isNotEmpty() )
    {
      /* Most recent pending order */
      $order = $ORDERS->get( $order_index );

      if ( !isset($order->balance) )
        continue;

      /* If the order can be canceled right away | Usually the first run at this loop */ 
      if ( $order->balance <= $credits )
      {
        /* Amount to deduct from the balances */
        $credits -= $order->balance;
        $payment -= $order->balance;

        $fee = $order->balance * $COLLECTOR->rate;

        /* Register the Payment */
        $pc->addPayment( 'IN', $loan->id, $order->id, $order->balance, $loan->balance, $fee);

        /* Register the collector's fee */
        $collector_fee += $fee;

        /* Cancel this order */
        $order->balance = 0;
        $order->status = 0;

        /* Reduce the number of pending orders  */
        $loan->delays--;

        /* Check if there are more peding orders */
        if ( $loan->delays )
        {
          /* The remaining credits must be used to pay the next peding order */
          $hasPendings = true;
          $order_index++;
        }
        else
        {
          /* Allow the remaining credits to be substracted from the balance */  
          $hasPendings = false;
        }

        /* If the credits are exactly 0, there is no point in continuing */
        $hasPendings = (bool) $credits ? $hasPendings : false;
      }
      else
      {
        /* If the balance is greater than the available credits | Usually remainings of the first run */

        $fee = $credits * $COLLECTOR->rate;

        /* Register the Payment */
        $pc->addPayment( 'AB', $loan->id, $order->id, $credits, $loan->balance, $fee);

        /* Register the collector's fee */
        $collector_fee += $fee;

        /* Substract the credits from the pending balance */
        $order->balance -= $credits;

        /* All credits have been spent */
        $credits = 0;

        /* Check if order must be canceled */
        if ( $order->balance === 0)
        {
          $order->status = 0;
          $loan->delays--;
        }

        /* No more credits for this payment */
        $hasPendings = false;
      }

      if ( $TYPE === 'PM')
      {
        $loan->extentions++;
      }

      /* [START Update the order] */
      $order->save();
      /* [ END Update the order] */
    } /* end while */

    /* If after paying the orders there's still credits. They affect the loan's balance directly */
    if ( $credits )
    {
      /* [START Close the loan if balance reaches 0] */
      $loan->balance -= $credits; /* (includes *multi* peding orders if the payment is PC ) */
      $loan->balance = $loan->balance > 0 ? $loan->balance : 0;
      $loan->status = (bool) $loan->balance;
      /* [ END Close the loan if balance reaches 0] */

      /* [START Calc duemod] */

      /* update the firdue substracting the payed amount */
      if ( $loan->firdue )
      {
        $loan->firdue -= $credits;

        if ( $loan->firdue < 0)
        {
          $loan->duemod = $loan->firdue;
          $loan->firdue = 0; 
        }
      }
      else
      {
        if ( $TYPE === 'PC')
        {
          /* reset the mod */
          $loan->duemod = 0;  
        }
        else
        {
          /* PM substract the remaining */
          if ( $loan->intrate > 0 )
          {
            $loan->duemod -= $credits;      
          }
        }

        /* Avoid zero-payments */
        if ( -1 * $loan->duemod >= ($DUE * $MULTI))
        {
          $loan->duemod += ($DUE * $MULTI);
        }
      }
      /* [ END Calc duemod] */

      /* Register the Payment */
      $pc->addPayment( $TYPE, $loan->id, 0, $payment, $loan->balance);

      /* Register the collector's fee */
      // $collector_fee += $payment * $COLLECTOR->rate;
    }

    /* [ Update the loan ] */
    $loan->save();

    if( $COLLECTOR->id )
    {
      $COLLECTOR->payroll->balance += $collector_fee;
      $COLLECTOR->payroll->save();
    }
    
    /* [START Update the date] */
    $loan->next_due = $this->getNextPeriod( $loan->payplan, $loan->next_due );
    /* [ END Update the date] */

    DB::commit();

    if ( getenv('APP_ENV') !== 'local' )
    {
      /* [START Send an SMS messge ] */
      $notification = new NotificationController();
      $loan->credits = $CREDITS;
      $sms_type = ( $loan->status ) ? 'SP' : 'CL';
      $notification->send( $loan->client->phone, $sms_type, $loan );
      unset($loan->credits);
      /* [ END Send an SMS messge ] */
    }
  }

  public function update(Request $request)
  {
    try
    {
      $loan = Loan::findOrFail( $request->input('id') );
    }
    catch(ModelNotFoundException $e)
    {
      return redirect('/');
    }

    if ( $request->path() == 'prestamos/pagar')
    {
      $this->addPayments( $request, $loan );
    }
    else
    {
      /* Update the next payment date */      
      $collect_date = $request->input('next_due', false);
      if ($collect_date)
      {
        $loan->collect_date = $collect_date;
        $loan->extentions++;
      }
    }

    /* Commit the changes */
    $loan->update();

    /* Redirect back to the profile */
    return back();
  }

  private function _today()
  {
    /* Total payments from today */
    $payed_in = Payment::where('created_at', '>', date('Y-m-d'))
    ->where('type', 'IN')
    ->get()->sum('amount');

    $payed_ab = Payment::where('created_at', '>', date('Y-m-d'))
    ->where('type', 'AB')
    ->get()->sum('amount');

    $pending = 0;

    $cli_arr = collect();
    $zones_arr = collect();
    $zones = collect();
    $other = collect();

    $USER = Auth::user()->id;

    if ( $USER === 0 )
    {
      $zones = Zones::all();
    }
    else
    {
      $assignments_z = Assignments::where('user_id', $USER )->get();

      /* [START Get User Assigments] */
      foreach ($assignments_z as $z)
      {
        if ($z->type == 'Z')
        {
          $zones->push( Zones::find($z->target_id) );
        }
      }
      /* [ END Get User Assigments] */
    }

    /* [START Get All delayed Loans] */
    $loans = Loan::where('delays', '>', 0)
    ->where('next_due', '<=', date('Y-m-d'))
    ->where('status', 1)
    ->get();
    /* [ END Get All delayed Loans] */

    /* [START Group loans by zone] */
    foreach ($loans as $lid => $loan)
    {
      $mapped = false;

      foreach ($zones as $zone)
      {
        if (!$zone->loans)
        {
          $zone->loans = collect();  
        }

        if( $loan->client->zone_id == $zone->id )
        {
          $zone->loans->push( $loan );
          $mapped = true;
        }
      }

      /* Handle unmapped loans */
      if ( !$mapped )
      {
        $other->push( $loan );
      }
      /* Get the pending amount */
      $pending += PayOrder::where('loan_id', $loan->id)
      ->where('status', 1)
      ->where('date', '<=', date('Y-m-d'))->get()->sum('amount');
    }
    /* [ END Group loans by zone] */

    $data = array(
      'payed' => $payed_ab + $payed_in,
      'total' => $pending + $payed_in,
      'progress' => 0,
      'loans' => $loans,
      'zones' => $zones,
      'other' => $other,
    );
    return $data;
  }

  public function today()
  {
    $data = $this->_today();
    return view('today', $data);
  }

  public function today_print()
  {
    $data = $this->_today();
    return view('today_print', $data);
  }


  private function nicecify( $amount )
  {
    return round( $amount / 1000, 0, PHP_ROUND_HALF_UP) * 1000;
  }

  private function calcDelays(Loan $loan)
  {
    $delays = 0; 
    $today = date('Y-m-d');

    /* Get the date of the last payment made to this loan */
    $lp = $loan->payments->sortByDesc('id')->first();

    /* Set the date to either the last payment or the loan date */
    $ref_date = ($lp) ? (string) $lp->created_at : (string) $loan->created_at;

    if( $loan->payplan === 'we' )
    {
      $day_loan = date('l', strtotime($loan->created_at));
      $day_lp_ts = strtotime("last ${day_loan}", strtotime($ref_date));
      $day_lp = date('Y-m-d', $day_lp_ts);
      $ref_date = $day_lp;
    }

    /* If loan should have pending orders */
    if( $ref_date <= $today )
    {
      $hasPendings = true;
      while ( $hasPendings )
      { 
        /* Calculate 1 period more after the current set date */
        $nextPeriod = $this->getNextPeriod($loan->payplan, $ref_date);

        /* If the date alreday happened, add a delay and check again */
        if ( $nextPeriod < $today )
        {
          $delays++;
          $ref_date = $nextPeriod;
        }
        else
        {
          /* Means the date for next period has not happened yet */
          $hasPendings = false;
        }
      }
    }

    /* Update the loan */
    $loan->delays = $delays;
    $loan->save();

    return $ref_date;
  }

  private function addPendingOrders( Loan $loan)
  {
    /* Delete previous active orders */
    PayOrder::where('loan_id', $loan->id)->where('status', 1)->delete();
    
    for ($i=0; $i < $loan->delays; $i++)
    {
      $po = new PayOrder;
      $po->loan_id = $loan->id;
      $po->date = date('Y-m-d');
      $po->amount = $loan->mindue;
      $po->balance = $po->amount;
      $po->save();
    }
  }

  private function translateDay($timestamp)
  {
    $eng = date('D', $timestamp);
    switch ($eng)
    {
      case 'Sat': return 'Sab';
      case 'Mon': return 'Lun';
      case 'Tue': return 'Mar';
      case 'Wed': return 'Mie';
      case 'Thu': return 'Jue';
      case 'Fri': return 'Vie';
      case 'Sun': return 'Dom';
    }
  }

  public function act()
  {
    // $controller = new NotificationController();
    // $controller->test();

    $today = date('Y-m-d');

    DB::beginTransaction();
    
    $toUpdate = Loan::where('status', 1)
    ->where('next_due', '<=', $today)
    ->get();



    foreach ($toUpdate as $loan )
    {
      // $day_lp = $this->calcDelays($loan);

      // $this->addPendingOrders( $loan );

      // if( $loan->payplan === 'we')
      // {
      //   $loan->next_due = $this->getNextPeriod('we', $day_lp);
      // }
      // else
      // {
      // }

      // $loan->next_due = $this->getNextPeriod($loan->payplan, $day_lp);

      echo $loan->client_id, " | ", $loan->payplan, " | ",$loan->next_due, " | ", $loan->delays;
      $this->hr();

      // $loan->save();
    }

    DB::rollback();
    // DB::commit();


    /* ---------------------------------- TO UPDATE ---------------------------------- */

    // $ID = 409;

    /* ______________________________ END: TO UPDATE ______________________________ */

    // $loan = Loan::find($ID);

    // $this->calcDelays($loan);
    // $this->addPendingOrders( $loan );




    // foreach ($loans_today as $loan)
    // {
    //   echo $loan , "<hr>";

    //   $NEXT_DUE = $loan->next_due;

    //   if ( $loan->delays > 0 )
    //   {
    //     $NEXT_DUE = $this->getNextPeriod( $loan->payplan, $loan->next_due, $loan->delays );
    // }

    //   echo $loan;
    //   echo $this->br();
    //   echo $NEXT_DUE;
    //   echo $this->hr();

      // if ( $NEXT_DUE === $today )
      // {
      //   $po = new PayOrder;
      //   $po->loan_id = $loan->id;
      //   $po->date = $today;
      //   $po->amount = $loan->mindue;
      //   $po->balance = $po->amount;
      //   $po->save();

      //   /* Update the loan */
      //   $loan->delays++;
      //   $loan->save();
      // }
    // }

    // $toUpdate = Loan::where('status', 1)
    // ->where('intrate', 0)
    // ->get();
    // echo '<pre>';
    // DB::commit();
    // $this->updateDaily();
    // $controller = new NotificationController();
    // $controller->test();
  }

  private function br($num = 1){ for ($i=0; $i < $num; $i++) echo "<br>"; }
  private function hr($num = 1){ for ($i=0; $i < $num; $i++) echo "<hr>"; }
}