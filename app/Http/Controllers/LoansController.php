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
use App\Models\Client;
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
      $loan->last_order = $fixdue;
      $loan->collect_date = $fixdue;
    }
    else
    {
      $loan->last_order = $this->getNextPeriod( $loan->payplan, $loan->created_at );  
      $loan->collect_date = $loan->last_order;
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
    $loanlist = Loan::where('client_id', $id)->get();

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
      $last_order_ts = strtotime( $loan->last_order );
      $collect_ts = strtotime( $loan->collect_date );

      $loan->last_order_display = date('d/m/Y', $last_order_ts);
      $loan->collect_display = date('d/m/Y', $collect_ts);

      $loan->last_order_display = $this->translateDay($last_order_ts  ) . " " . $loan->last_order_display;
      $loan->collect_display = $this->translateDay($collect_ts) . " " . $loan->collect_display;

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
    DB::beginTransaction();

    $today = date('Y-m-d');
    $todayTs = strtotime( $today );

    $activeLoans = Loan::where('status', 1)->get();

    foreach ( $activeLoans as $loan )
    {
      $lastOrderDate = $this->getLastOrderDate( $loan );
      $lastOrderDateTs = strtotime( $lastOrderDate );

      $nextOrderDate = $this->getNextPeriod(  $loan->payplan, $lastOrderDate );
      $nextOrderDateTs = strtotime( $nextOrderDate );

      $needsOrder = ( $nextOrderDateTs === $todayTs );

      if ( $needsOrder )
      {
        $this->createOrder( $loan, $today);
        $loan->last_order = $today;
        $loan->update();
      }
    }

    DB::commit();
  }

  public function rememberDaily()
  {

    $notifier = new NotificationController;

    $today = date('Y-m-d');
    $todayTs = strtotime( $today );

    $activeLoans = Loan::where('status', 1)->get();

    foreach ($activeLoans as $loan)
    {
      $nextOrderDate = $this->getNextPeriod( $loan->payplan, $loan->last_order );
      $nextOrderDateTs = strtotime( $nextOrderDate );

      $dayBeforeTheOrderTs = strtotime( "-1 day", $nextOrderDateTs );

      if( $dayBeforeTheOrderTs ==  $todayTs )
      {
        /* Send the payment reminder message */      
        $notifier->send( $loan->client->phone, 'PR', $loan );
      }
    }
  }

  /* ------------------------------- Payments ------------------------------- */
  
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
    * So we add its equivalent for the numbers to add up */
    $credits = $CREDITS;

    $EXTRA = 0;

    if ( $TYPE === 'PC' && $loan->intrate && $credits >= $DUE )
    {
      $EXTRA = $loan->mindue * $MULTI;
      $credits += $EXTRA;
    }

    $payment = $CREDITS;

    $collector_fee = 0;

    /* Whether the client has any orders pending to cancel */
    /* Cancel the orders before touching the loan balance */
    while ( $loan->orders->where("status" , 1)->isNotEmpty() && $credits > 0)
    {

      /* Most the most recent pending order */
      $order = $loan->orders->where("status", 1)->sortByDesc("date")->first();

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

        $hasPendings = $loan->orders->where("status" , 1)->isNotEmpty();

        /* Check if there are more peding orders */
        if ( $hasPendings )
        {
          /* The remaining credits must be used to pay the next peding order */
          $order_index++;
        }
        else
        {
          /* Allow the remaining credits to be substracted from the balance */  
        }
        /* If the credits are exactly 0, there is no point in continuing */
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
        }
        /* No more credits for this payment */
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
    if ( $credits > 0 )
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
      /* $collector_fee += $payment * $COLLECTOR->rate; */
    }

    /* [ Update the loan ] */
    $loan->save();

    if( $COLLECTOR->id )
    {
      $COLLECTOR->payroll->balance += $collector_fee;
      $COLLECTOR->payroll->save();
    }


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
    $assignedClientsIds = collect();
    $assignedZonesIds = collect();
    $assignedZones = collect();
    
    /* Get all the entities assigned to this user */
    $AllUserAssignments = assignments::where('user_id', Auth::user()->id )->get();

    /* Get the list of zones assigned to this user */
    if ( Auth::user()->is_admin )
    {
      $assignedZones = Zones::all();
    }
    else
    {
      $zoneAssigmentsCollect = $AllUserAssignments->where('type', 'Z');
      foreach( $zoneAssigmentsCollect as $zma )
      {
        $zone = Zones::find($zma->target_id);
        $zone->loans = collect();
        $assignedZones->push( $zone );
      }
    }

    /* Flatten the zone list into a 1-dimentional array of ids */
    foreach ($assignedZones as $az) 
    {
      $az->loans = collect();
      $assignedZonesIds->push( $az->id );
    }

    /* Get the list of clients assigned to this user */
    $manualClientAssigmentsCollect = $AllUserAssignments->where('type', 'C');
    foreach ( $manualClientAssigmentsCollect as $mca ) 
    {
      $assignedClientsIds->push( $mca->target_id );
    }

    /* ----------------- filter the loans ----------------- */
    $activeLoansForCollection = collect();

    $today = date('Y-m-d');
    $todayTs = strtotime( $today );

    $activeLoans = Loan::where('status', 1)->get();

    foreach ( $activeLoans as $loan )
    {
      $collectDateTs = strtotime( $loan->collect_date );
      $lastOrderTs = strtotime( $loan->last_order );

      /* if the loan has a "promise of payment" */
      if( $collectDateTs > $lastOrderTs )
      {
        if ( $collectDateTs === $todayTs )
        {
          $activeLoansForCollection->push( $loan );
        }
      }
      else
      {
        /* check for the normal allocation - by next_order() */
        if ( $lastOrderTs === $todayTs )
        {
          $activeLoansForCollection->push( $loan );
        }
      }
    }

    $assignedLoans = collect();

    foreach ($activeLoansForCollection as $loan)
    {
      /* When the loan is within a zone linked to this user */
      if( $assignedZonesIds->contains( $loan->client->zone_id ) )
      {
        foreach ($assignedZones as $zone)
        {
          if ($loan->client->zone_id == $zone->id)
          {
            $zone->loans->push( $loan );
          }
        }
      } /* When the loan belongs to a client assigned to this user */
      else if( $assignedClientsIds->contains($loan->client_id) )
      {
        $assignedLoans->push($loan);
      }
    }

    $data = array(
      'payed' => 0,
      'total' => 0,
      'progress' => 0,
      'loans' => $activeLoansForCollection,
      'zones' => $assignedZones,
      'other' => $assignedLoans,
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

  /* ---------------------------------- DEBUG FUNCTIONS ---------------------------------- */
  
  /* ---------------------------------- DEBUG FUNCTIONS ---------------------------------- */
  
  public function fix()
  {
    $this->fix_PaymentsDate();
  }

  private function fix_MissingOrders()
  {
    return;

    DB::beginTransaction();

    $today = date('Y-m-d');
    $todayTs = strtotime( $today );

    $activeLoans = Loan::where('status', 1)->get();

    echo '<pre>';
    foreach ( $activeLoans as $loan )
    {
      $lastOrderDate = $this->getLastOrderDate( $loan );
      $lastOrderDateTs = strtotime( $lastOrderDate );

      $nextOrderDate = $this->getNextPeriod(  $loan->payplan, $lastOrderDate );
      $nextOrderDateTs = strtotime( $nextOrderDate );

      $needsOrders = $nextOrderDateTs < $todayTs;

      if ( $needsOrders )
      {
        // echo $nextOrderDateTs . " " . $todayTs . "<br>";
        $this->fix_CreateOrderRecursive( $loan, $nextOrderDate );
      }
    }

    DB::commit();
  }

  private function fix_CreateOrderRecursive( Loan $loan, $currentOrderDate )
  {
    return;
    $today = date('Y-m-d');
    $todayTs = strtotime( $today );
    
    $currentOrderDateTs = strtotime( $currentOrderDate );

    $this->createOrder( $loan, $currentOrderDate );

    echo "created order for client: " . $loan->client_id . " date: " . $currentOrderDate . "<br>";
    
    $loan->collect_date = $currentOrderDate;
    $loan->update();

    /* evaluate recursivity */
    $nextOrderDate = $this->getNextPeriod(  $loan->payplan, $currentOrderDate );
    $nextOrderDateTs = strtotime( $nextOrderDate );

    $needsOrders = $nextOrderDateTs < $todayTs;

    if( $needsOrders )
    {
      return $this->fix_CreateOrderRecursive( $loan, $nextOrderDate );
    }

    return $needsOrders;
  }

  private function fix_DuplicateOrders()
  {
    $allOrders = PayOrder::where("status", 1)->get();
    // $allOrders = PayOrder::where("loan_id", 397)->where("status", 1)->get();

    $uniqueOrders = $allOrders->unique("date");

    echo "<pre>";
    echo $allOrders->count() . $this->br();
    echo $uniqueOrders->count() . $this->br();

    $uniqueOrderIds = collect();
    foreach ($uniqueOrders as $order)
    {
      $uniqueOrderIds->push( $order->id );
    }

    foreach ($allOrders as $order)
    {
      if( !$uniqueOrderIds->contains( $order->id ) )
      {
        echo "deleted order: " . $order->id . $this->br();
        // PayOrder::where("id", $order->id)->delete();
        $order->delete();
      }
    }
  }
  
  private function fix_PaymentsDate()
  {
    $allPayments = Payment::all();

    foreach ($allPayments as $payment)
    {
      $date = date('Y-m-d', strtotime( $payment->created_at ));
      $payment->date = $date;
      $payment->update();
    }
  }

  private function createOrder(Loan $loan, $date)
  {
    $po = new PayOrder;
    $po->loan_id = $loan->id;
    $po->date = $date;
    $po->amount = $loan->mindue;
    $po->balance = $po->amount;
    $po->save();
  }

  private function getLastOrderDate( Loan $loan )
  {
    $lastOrder = PayOrder::where('loan_id', $loan->id)
    ->orderBy("date", "desc")
    ->first();
    
    if( $lastOrder )
    {
      return $lastOrder->date;
    }
    echo "no orders: " . $loan->client_id . "<hr>";
    return $this->getLastPaymentDate();
  }

  private function getLastPaymentDate( Loan $loan)
  {
    $lastPayment = Payments::where('loan_id', $loan->id)
    ->orderBy("date", "desc")
    ->first();

    if( $lastPayment )
    {
      return $lastPayment->date;
    }
    return $loan->created_at;
  }

  function datediff($interval, $datefrom, $dateto, $using_timestamps = false)
  {
    /*
    $interval can be:
    yyyy - Number of full years
    q    - Number of full quarters
    m    - Number of full months
    y    - Difference between day numbers
    (eg 1st Jan 2004 is "1", the first day. 2nd Feb 2003 is "33". The datediff is "-32".)
    d    - Number of full days
    w    - Number of full weekdays
    ww   - Number of full weeks
    h    - Number of full hours
    n    - Number of full minutes
    s    - Number of full seconds (default)
    */

    if (!$using_timestamps) 
    {
      $datefrom = strtotime($datefrom, 0);
      $dateto   = strtotime($dateto, 0);
    }

    $difference        = $dateto - $datefrom; /*  Difference in seconds */

    $months_difference = 0;

    switch ($interval)
    {
      case 'yyyy':
      $years_difference = floor($difference / 31536000);
      if (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom), date("j", $datefrom), date("Y", $datefrom)+$years_difference) > $dateto) {
        $years_difference--;
      }

      if (mktime(date("H", $dateto), date("i", $dateto), date("s", $dateto), date("n", $dateto), date("j", $dateto), date("Y", $dateto)-($years_difference+1)) > $datefrom) {
        $years_difference++;
      }

      $datediff = $years_difference;
      break;

      case "q":
      $quarters_difference = floor($difference / 8035200);

      while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($quarters_difference*3), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
        $months_difference++;
      }

      $quarters_difference--;
      $datediff = $quarters_difference;
      break;

      case "m":
      $months_difference = floor($difference / 2678400);

      while (mktime(date("H", $datefrom), date("i", $datefrom), date("s", $datefrom), date("n", $datefrom)+($months_difference), date("j", $dateto), date("Y", $datefrom)) < $dateto) {
        $months_difference++;
      }

      $months_difference--;

      $datediff = $months_difference;
      break;

      /* Difference between day numbers */
      case 'y': 
      $datediff = date("z", $dateto) - date("z", $datefrom);
      break;

      /* Number of full days */
      case "d": 
      $datediff = floor($difference / 86400);
      break;

      /* Number of full weekdays */
      case "w": 
      $days_difference  = floor($difference / 86400);
      $weeks_difference = floor($days_difference / 7);
      $first_day        = date("w", $datefrom);
      $days_remainder   = floor($days_difference % 7);
      /* Do we have a Saturday or Sunday in the remainder? */
      $odd_days         = $first_day + $days_remainder;

      /* Sunday */
      if ($odd_days > 7) {
        $days_remainder--;
      }

      /* Saturday */
      if ($odd_days > 6) {
        $days_remainder--;
      }

      $datediff = ($weeks_difference * 5) + $days_remainder;
      break;

      /* Number of full weeks */
      case "ww": 
      $datediff = floor($difference / 604800);
      break;

      /* Number of full hours */
      case "h":
      $datediff = floor($difference / 3600);
      break;

      /* Number of full minutes */
      case "n": 
      $datediff = floor($difference / 60);
      break;

      /* Number of full seconds (default) */
      default:
      $datediff = $difference;
      break;
    }

    return $datediff;
  }

  /* ______________________________ END: DEBUG FUNCTIONS ______________________________ */

  private function br($num = 1){ for ($i=0; $i < $num; $i++) echo "<br>"; }
  private function hr($num = 1){ for ($i=0; $i < $num; $i++) echo "<hr>"; }
}