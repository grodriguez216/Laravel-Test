<?php
namespace App\Http\Controllers;

use Validator;
use Auth;
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


      /* [START New loan notification] */
      $notifications = new NotificationController;
      $notifications->send( $client->phone, 'NL', $loan );
      /* [ END New loan notification] */

      /* [START Commit changes] */
      $loan->save();
      /* [ END Commit changes] */

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

# [ON MINIMAL PAYMENTS] ALL MONEY PAYED OVER THE INTERESTS IS CREDITED TO THE BALANCE.

         /* [START Check the credits] */
         /* Difference between the minimal payment and the interests */      
         $CREDITS = ( $MINIMUN - $loan->intval );
         /* Dismiss credits lower than 1K */
         $loan->credits = ( $CREDITS < 1000 ) ? 0 : $CREDITS;
         /* [ END Check the credits] */

         /* [START Signal labels ] */
         $loan->mod = $loan->mod > 0 ? '-'.$loan->mod : '+'.$loan->mod * -1;
         $loan->credits = $loan->credits > 0 ? '+'.$loan->credits : '-'.$loan->credits * -1;
         /* [ END Signal labels ] */

         /* [START Format dates] */
         $loan->next_due_display = date('d/m/Y', strtotime( $loan->next_due ));
         $loan->date = date('d/m/Y', strtotime( $loan->created_at ));
         /* [ END Format dates] */

         /* [START Check pending bills] */
         $loan->pending = $loan->orders->sum('balance');
         /* [ END Check pending bills] */

      }
      return $loanlist;
   }


   private function calcPaymentFees()
   {

   }


   private function addPayments(Request $request, Loan $loan)
   {

      $PaymentsController = new PaymentsController;

      /* [START store user input] */
      $TYPE = $request->input('type');
      $custompay = $request->input('custompay', 0);
      $multiplier = $request->input('duemulti', 1);
      $extra = (int) $request->input('extra', 0);
      /* [ END store user input] */

      /* [START Global vars ] */
      $DUE = 0;
      $CREDITS = 0;
      /* [ END Global vars ] */

      switch ( $type )
      {
         case 'PC':
         /* [START Check First Due] */
         if( $loan->firdue )
         {
            $DUE = $loan->firdue;
            $loan->firdue = 0;
         }
         /* [ END Check First Due] */

         /* [START Check for Custom Regular Payments] */

         /* [ END Check for Custom Regular Payments] */

         /* Check if the user selected a custom 'regular due' */
         if ( !$custompay ) $substract += $due; 

         /* Amount to substract (+) the mods */
         $substract += ( $loan->duemod );
         /* Reset the discount after a PC payment */
         $loan->duemod = 0;

         break;

         case 'PM':

         /* Check if is paying different than the minimum */
         $credit = ( $loan->mindue ) - ( $this->nicecify( $loan->intval ) );

         /* Add the credit to the duemod. This will decrease the next regular due. */
         if ( $credit > 1000 )
         {
            $loan->duemod += ($credit * $multiplier) * -1;
            $CREDITS += $credit;
         }

         /* Increase the counter for minimal payments */
         $loan->extentions++;
         break;
      }

      $CREDITS += $extra;

      /* Get all the peding orders this client has */
      $ORDERS = PayOrder::where('loan_id', $loan->id)->where('status', 1)->orderBy('date')->get();

      $try = 0;

      if ( $loan->delays && $ORDERS)
      {
         for ($index=0; $index < $multiplier; $index++)
         {

            $hasPendings = true;  

            while ( $hasPendings )
            {
               $current $ORDERS->get( $try );

               if ( $current->balance >= $CREDITS )
               {
                  /* Decrease the Order balance */
                  $current->balance -= $CREDITS;

                  /* Register the Payment */
                  $PaymentsController->addPayment( $TYPE, $loan->id, $current->id, $CREDITS, $loan->balance);

                  /* Exit the loop */
                  $hasPendings = false;
               }
               else
               {
                  /* Register the Payment */
                  $PaymentsController->addPayment( 'AB', $loan->id, $current->id, $current->balance, $loan->balance);

                  /* Decrease the credits by the amount registered */
                  $CREDITS -= $current->balance;

                  /* Decrease the Order balance to 0 */
                  $current->balance = 0;

                  /* Tell the app to use the next order in line */
                  $try++;
               }

               /* Validate before ending */
               if ( $current->balance === 0 )
               {
                  /* Close the Order when balance reaches 0 */
                  $current->status = 0;

                  /* Decrease the loan balance by the order amount  */
                  $loan->balance-= $current->amount;

                  /* Decrease the delayed payment counter */
                  $loan->delays--;

                  /* Close the Loan when the balance reaches 0 */
                  $loan->status = (int) $loan->balance > 0;
               }

               /* Upadte the Models */
               $current->save();
               $loan->save();
            }
         }
      }













// echo '<pre>';
// print_r( $DUE );
// echo 'Line: ', __LINE__;
      die;


      /* ---------------------------------- CALC FEES ---------------------------------- */

// /* Calc the fee */
// $u = Auth::user();

// if ( $loan->intrate )
// {
//   $u->aggregate += $this->nicecify( $loan->intval ) * $u->fee;
// }
// else
// {
//   /* Fixed 20% on loans at 0% interest rate */  
//   $u->aggregate += (($substract + $extra) * $multiplier) * 0.20;
// }
// $u->save();
      /* ______________________________ END: CALC FEES ______________________________ */


      /* Substract the extra from the next PC payment */
      if ( !$custompay )
      {
         $loan->duemod += ($extra * $multiplier) * -1;
      }

      /* Check if the discount is greater than a regdue, to avoid zero-payments */
      if ( $loan->duemod*-1 >= $loan->regdue ) { $loan->duemod -= $loan->regdue; }

      /* Register the Payments */
      for ($i=0; $i < $multiplier; $i++)
      { 
         /* Update the balance */
         $loan->balance -= $substract;
         $PaymentsController->store( $type, $loan->id, $substract, $loan->balance);

         /* Update the delays */
         $loan->delays -= $loan->delays > 0 ? 1 : 0;
      }

      /* Register the Optional extra deposit */
      if ( $extra )
      {
         for ($i=0; $i < $multiplier; $i++)
         { 
            /* Update the balance */
            $loan->balance -= $extra;
            $PaymentsController->store( 'AB', $loan->id, $extra, $loan->balance);
         }
      }

      /* Close the loan if balance is zero */
      if ( $loan->balance <= 0) $loan->balance = $loan->status = 0;

      /* Next Due Timestamp */
      $ndt = strtotime( $loan->next_due );

      /* Created at Timestamp */
      $cat = strtotime( $loan->created_at );

      switch ($loan->payplan)
      {
         case 'we':
         $original_day = date('l', $cat);
         $loan->next_due = date('Y-m-d', strtotime( "next $original_day", $ndt ));
         break;

         case 'bw':
         $eom = ( date('m', $ndt) == 2 ) ? 28 : 30;
         if ( date('d', $ndt) >= $eom )
         {
            $next_ft = strtotime( date('Y-m-15', $ndt) );
            $loan->next_due = date('Y-m-d', strtotime( '+1 month', $next_ft ) );
         }
         else if ( date('d') < 15)
         {
            $loan->next_due = date('Y-m-15', $ndt);
         }
         else
         {
            /* Between the 15 and the EOM */
            $loan->next_due = date('Y-m-d', $ndt);
         }
         break;

         case 'mo':
         $original_date = date('d', $cat);
         $loan->next_due = date("Y-m-$original_date", strtotime( "+1 month", $ndt ));
         break;
      }

      /* Send an SMS messge */
      $notification = new NotificationController();
      $loan->notifiable_due = $substract;
      $sms_type = ( $loan->status == 1 ) ? 'SP' : 'CL';
      $notification->notify( $loan->client->phone, $sms_type, $loan );
      unset( $loan->notifiable_due );
   }

   private function changeDueDate(Request $request)
   {

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
         return $this->addPayments( $request, $loan );
      }



      die('tr');

      /* Extentions */
// else if ( $request->path() == 'prestamos/extender')
// {
//   $loan->next_due =  $request->input('next_due');
// }

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
