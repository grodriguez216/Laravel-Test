<?php

namespace App\Http\Controllers;

use Validator;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\Loan;
use App\Models\Payments;
use App\Models\PayOrder;
use App\Models\Assignments;
use App\User;

class LoansController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
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
      $loan->next_due = $fixdue;
    else
      $loan->next_due = $this->getNextPeriod( $loan->payplan, $loan->created_at );  
    /* [ END Next due date calculation ] */
    
    /* [START Commit changes] */
    $loan->save();
    /* [ END Commit changes] */

    /* [START Add peding orders] */
    for ($index=0; $index < $loan->delays; $index++)
    { 
      $order = new PayOrder;
      $order->loan_id = $loan->id;
      $order->amount = $loan->intval;
      $order->balance = $loan->intval;
      $order->date = $loan->created_at;
      $order->save();
    }
    /* [ END Add peding orders] */

    DB::commit();

    /* [START New loan notification] */
    $notifications = new NotificationController;
    $notifications->send( $client->phone, 'NL', $loan );
    /* [ END New loan notification] */

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

  private function getNextPeriod( $plan, $base = false )
  {
    /* set the relative base date-time */
    $base = $base ? strtotime($base) : time();

    /* End of month relative to base time */
    define('EOM', date('m', $base ) == 2 ? 28 : 30);

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
      $this_30 = strtotime( date('Y-m-'.EOM, $base) );

      if ( $today >= EOM )
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

    /* format the date for export */
    return date('Y-m-d', $next_due_ts );
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
      $due_date_ts = strtotime("+{$loan->duration} months", $base);
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

      /* [ON MINIMAL PAYMENTS] ALL MONEY PAYED OVER THE INTERESTS IS CREDITED TO THE BALANCE. */

      /* [START Check the credits] */
      /* Difference between the minimal payment and the interests */      
      $CREDITS = ( $MINIMUN - $loan->intval );
      /* Dismiss credits lower than 1K */
      $loan->credits = ( $CREDITS < 1000 ) ? 0 : $CREDITS;
      /* [ END Check the credits] */

      /* [START Format dates] */
      $loan->next_due_display = date('d/m/Y', strtotime( $loan->next_due ));
      $loan->date = date('d/m/Y', strtotime( $loan->created_at ));
      /* [ END Format dates] */

      /* [START Check pending bills] */
      $loan->pending = $loan->orders->where('status', 1)->sum('balance');
      /* [ END Check pending bills] */

    }
    return $loanlist;
  }

  private function calcPaymentFees()
  {

  }

  private function addPayments(Request $request, Loan $loan)
  {
    /* [START store user input] */
    $TYPE = $request->input('type','OT');
    $CREDITS = $request->input('credits', 0);
    /* [ END store user input] */

    /* [START Global vars ] */
    $DUE = $loan->firdue ? $loan->firdue : $loan->regdue;
    /* [ END Global vars ] */

    /* Instantiate the payment controller */
    $pc = new PaymentsController;

    /* Get all the peding orders this client has */
    $ORDERS = PayOrder::where('loan_id', $loan->id)->where('status', 1)->orderBy('date', 'id')->get();

    DB::beginTransaction();

    /* order currently being updated */
    $order_index = 0;

    /* Modifiable variable for the creditable amount */
    /* Making a Payment complete implicitly pays 1 peding order. 
    So we add its equivalent for the numbers to add up */
    $credits = ($TYPE === 'PC' && $loan->delays) ? $CREDITS + $this->nicecify($loan->intval) : $CREDITS;
    $payment = $CREDITS;

    /* Whether the client has any orders pending to cancel */
    $hasPendings = $loan->delays;

    /* Cancel the orders before touching the loan balance */
    while ( $hasPendings )
    {
      /* Most recent pending order */
      $order = $ORDERS->get( $order_index );

      /* If the order can be canceled right away | Usually the first run at this loop */ 
      if ( $order->balance <= $credits )
      {
        /* Amount to deduct from the balances */

        $credits -= $order->balance;
        $payment -= $order->balance;

        /* Register the Payment */
        $pc->addPayment( 'IN', $loan->id, $order->id, $order->balance, $loan->balance);

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

        /* Register the Payment */
        $pc->addPayment( 'AB', $loan->id, $order->id, $credits, $loan->balance);

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
      $loan->balance -= $credits; /* (includes 1 peding order if the payment is PC ) */
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
          /* paying more than it should on a regular due */
          if ( $credits > ($loan->regdue + $loan->duemod) )
          {
            $loan->duemod -= $credits - $loan->regdue;
          }
          else
          {
            /* reset the mod */
            $loan->duemod = 0;  
          }
        }
        else
        {
          $loan->duemod = $credits;    
        }

        /* Avoid zero-payments */
        if ( -1 * $loan->duemod >= $DUE)
        {
          $loan->duemod + $DUE;
        }
      }
      /* [ END Calc duemod] */

      /* Register the Payment */
      $pc->addPayment( $TYPE, $loan->id, 0, $payment, $loan->balance);
    }

    /* [START Update the loan ] */
    $loan->save();
    /* [ END Update the loan ] */

    DB::commit();

    /* [START Send an SMS messge ] */
    $notification = new NotificationController();
    $loan->credits = $CREDITS;
    $sms_type = ( $loan->status ) ? 'SP' : 'CL';
    $notification->send( $loan->client->phone, $sms_type, $loan );
    unset($loan->credits);
    /* [ END Send an SMS messge ] */
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
      $next_due = $request->input('next_due', false);
      if ($next_due)
      {
        $loan->next_due = $next_due;
        $loan->extentions++;
      }
    }

    /* Commit the changes */
    $loan->update();

    /* Redirect back to the profile */
    return back();
  }

  public function today()
  {
    $uzones = Assignments::where('user_id', Auth::user()->id )->get();

    $cli_arr = collect();
    $zones_arr = collect();

    foreach ($uzones as $z)
    {    
      if ($z->type == 'Z') $zones_arr->push( $z->target_id );
    }

    foreach ($uzones as $c)
      if ($c->type == 'C') $cli_arr->push( $c->target_id );

    $loans = collect();

    $t = Loan::where('delays', '>', 0)->orderBy('paytime')->get();


    foreach ($t as $l)
    {
      if ( in_array( $l->client->zone_id, $zones_arr->toArray() ) )
        $loans->push($l);

      if ( in_array( $l->client->id, $cli_arr->toArray() ) )
        $loans->push($l);
    }

    $data = array(
      'loans' => $loans,
      'aggregate' => Auth::user()->aggregate
    );

    return view('loans.today', $data);
  }

  private function nicecify( $amount )
  {
    return round( $amount / 1000, 0, PHP_ROUND_HALF_UP) * 1000;
  }

}
