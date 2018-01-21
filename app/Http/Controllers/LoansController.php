<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\Loan;
use App\Models\Payments;

class LoansController extends Controller
{
  /**
  * Create a new controller instance.
  *
  * @return void
  */
  public function __construct()
  {
    $this->middleware('auth');
  }
  
  /**
  * Display a listing of the resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {
    return view('loans.list');
  }
  
  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
    $zones = \App\Models\Zones::all();
    return view('loans.create', [ 'zones' => $zones ] );
  }
  
  /**
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
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
    if ($validator->fails()) return redirect('prestamos/agregar#newLoanForm')->withErrors($validator)->withInput();
    
    /* Build the Loan Object */
    $loan = new Loan;
    
    /* Add the client throug the client controller */
    $clientsController = new ClientsController();
    $client = $clientsController->store( $request );
    $loan->client_id = $client->id;
    
    /* -------------------------- begin: Loan calculations -------------------------- */
    
    $loan->loaned = $request->input('loaned');
    $loan->balance = $request->input('balance');
    $loan->payable = $request->input('balance');

    $loan->firdue = $request->input('firdue');
    $loan->regdue = $request->input('regdue');
    $loan->mindue = $request->input('mindue');

    $loan->intval = $request->input('intval');
    $loan->intrate = $request->input('intrate');

    /* Due modifier: discounts or extras */
    $loan->duemod = 0;

    $loan->duration = $request->input('duration');
    $loan->payplan = $request->input('payplan');
    $loan->paytime = $request->input('paytime');
    
    $fixdue = $request->input('next_due', 0);
    $fix_date = ( $fixdue ) ? strtotime( $fixdue ) : time();
    $next_due = $due_date = time();
    
    switch ( $loan->payplan )
    {
      case 'we':
      $due_date = strtotime("+{$loan->duration} weeks", $fix_date);
      $next_due = ( $fix_date ) ? $fix_date : strtotime("+1 week");
      break;
      
      case 'bw':
      $in_weeks = $loan->duration * 2;
      $due_date = strtotime("+{$in_weeks} weeks", $fix_date);
      $eom = ( date('m') == 2 ) ? 28 : 30;
      
      if ( date('d') >= $eom )
      {
        $next_ft = strtotime( date('Y-m-15') );
        $next_due = ( $fix_date ) ? $fix_date : date('Y-m-d', strtotime( '+1 month', $next_ft ) );
      }
      else if ( date('d') < 15)
        $next_due = ( $fix_date ) ? $fix_date : strtotime( date('Y-m-15') );
      
      else // between the 15 and the EOM
      $next_due = ( $fix_date ) ? $fix_date : strtotime( date('Y-m-30') );
      break;
      
      case 'mo':
      $due_date = strtotime("+{$loan->duration} months", $fix_date);
      $next_due = ( $fix_date ) ? $fix_date : strtotime('+1 month');
      break;
    }
    
    $loan->due_date = date('Y-m-d', $due_date);
    $loan->next_due = date('Y-m-d', $next_due);
    
    /* -------------------------- end: Loan calculations -------------------------- */
    
    /* Send an SMS messge */
    $notification = new NotificationController();
    $notification->notify( $client->phone, 'NL', $loan );
    
    $loan->save();
    
    return redirect("clientes/perfil/{$client->id}");
  }
  
  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show($id)
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

      /* Calculate the discout from previous partial deposits */
      $due = ( $loan->firdue ) ? $loan->firdue : $loan->regdue;

      $loan->nice_due = $due + $loan->duemod;
      $loan->diff_due = $due - $loan->nice_due;

      /* Round interests to the closest 1000 */
      $loan->nice_int = $this->nicecify( $loan->mindue );

      /* Check if the mindue is higher than the default */

      /* If is positive and higher than 1000, the diff will be substacted from the balance.
       * Otherwise the diff will be added to the next regular due.
       */
      $loan->diff_int = ( $loan->nice_int - $loan->intval ); // -333

      if ( $loan->diff_int < 1000 ) $loan->diff_int = 0;

      /* Add the diff from the rounding to the diff_int */
      // $loan->diff_int += ( $loan->nice_int - $loan->intval );

      /* Add the signs to the labels */
      $loan->diff_due = $loan->diff_due >= 0 ? '- '.$loan->diff_due : '+ '.$loan->diff_due*-1;
      $loan->diff_int = $loan->diff_int >= 0 ? '+ '.$loan->diff_int : '- '.$loan->diff_int*-1;
      
      /* Format the dates nicely */
      $loan->next_due_display = date('d/m/Y', strtotime( $loan->next_due ));
      $loan->date = date('d-m-Y', strtotime( $loan->created_at ));
    }
    
    return $loanlist;
  }
  
  /**
  * Show the form for editing the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function edit($id)
  {
    //
  }
  
  /**
  * Update the specified resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
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
    
    $PaymentsController = new PaymentsController;
    
    if ( $request->path() == 'prestamos/pagar')
    {
      /* Payment Type | Full or Minimun */
      $type = $request->input('type');

      /* Amount extra to substract from the balance */  
      $extra = (int) $request->input('extra');
      if ( $extra <= 0 ) $extra = 0;

      $custompay = $request->input('custompay', 0);
      $multiplier = $request->input('duemulti', 1);

      /* Regular or first due */
      $due = $loan->regdue;

      /* How much to reduce the balance */
      $substract = 0;

      if ( $type == 'PC')
      {
        /* Check if the first due is different */
        if( $loan->firdue )
        {
          $due = $loan->firdue;
          /* remove the firdue */
          $loan->firdue = 0;
        }

        /* Check if the user selected a custom 'regular due' */
        if ( !$custompay ) $substract += $due; 

        /* Amount to substract (+) the mods */
        $substract += ( $loan->duemod );

        /* Reset the discount after a PC payment */
        $loan->duemod = 0;
      }
      else
      {
        /* Check if is paying different than the minimum */
        $diff = ( $loan->mindue ) - ( $this->nicecify( $loan->intval ) );

        /* Add the diff to the duemod. This will decrease the next regular due. */
        if ( $diff > 1000 )
        {
          $loan->duemod += ($diff * $multiplier) * -1;
          $substract += $diff;
        }

        /* Increase the counter for minimal payments */
        $loan->extentions++;
      }

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
        # ----------------------------------------------------------------------------
        $original_day = date('l', $cat);
        $loan->next_due = date('Y-m-d', strtotime( "next $original_day", $ndt ));
        break;

        case 'bw':
        # ----------------------------------------------------------------------------
        $eom = ( date('m', $ndt) == 2 ) ? 28 : 30;
        if ( date('d', $ndt) >= $eom )
        {
          $next_ft = strtotime( date('Y-m-15', $ndt) );
          $loan->next_due = date('Y-m-d', strtotime( '+1 month', $next_ft ) );
        }
        else if ( date('d') < 15) $loan->next_due = date('Y-m-15', $ndt);
        else $loan->next_due = date('Y-m-30', $ndt); // between the 15 and the EOM
        break;
        
        case 'mo':
        # ----------------------------------------------------------------------------
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

    /* Extentions */
    else if ( $request->path() == 'prestamos/extender')
    {
      $loan->next_due =  $request->input('next_due');
    }
    
    /* Commit the changes */
    $loan->update();
    
    /* Redirect back to the profile */
    return back();
  }
  
  /**
  * Remove the specified resource from storage.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function destroy($id)
  {
  }
  
  
  public function today()
  {
    $today = date('Y-m-d');

    $loans = Loan::where('delays', '>', 0)->orderBy('paytime')->get();
    
    $data = array( 'loans' => $loans );
    
    return view('loans.today', $data);
    
  }
  
  private function nicecify( $amount )
  {
    return round( $amount / 1000, 0, PHP_ROUND_HALF_UP) * 1000;
  }
  
}
