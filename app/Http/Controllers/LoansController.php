<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;

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
    return view('loans.create');
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
    
    $loan->loaned = $request->input('loan');
    $loan->balance = $request->input('total');
    $loan->payable = $request->input('total');
    $loan->dues = $request->input('dues');
    $loan->interest = $request->input('partial');
    $loan->rate = $request->input('interest');
    $loan->duration = $request->input('duration');
    $loan->payplan = $request->input('payplan');
    
    $due_date = time();
    $next_due = time();
    
    switch ( $loan->payplan )
    {
      case 'we':
      $due_date = strtotime("+{$loan->duration} weeks");
      $next_due = strtotime("+1 week");
      break;
      
      case 'bw':
      
      $in_weeks = $loan->duration * 2;
      $due_date = strtotime("+{$in_weeks} weeks");
      $eom = ( date('m') == 2 ) ? 28 : 30;
      
      if ( date('d') >= $eom )
      {
        $next_ft = strtotime( date('Y-m-15') );
        $next_due = date('Y-m-d', strtotime( '+1 month', $next_ft ) );
      }
      else if ( date('d') < 15)
      $next_due = strtotime( date('Y-m-15') );
      
      else // between the 15 and the EOM
      $next_due = strtotime( date('Y-m-30') );
      break;
      
      case 'mo':
      $due_date = strtotime("+{$loan->duration} months");
      $this_month = date( "Y-m-{$loan->details}" );
      $next_due = strtotime('+1 month', $this_month );
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
        $loan->duration .= ' Sem';
        break;
        case 'bw':
        $loan->duration .= ' Qns';
        break;
        case 'mo':
        $loan->duration .= ' Meses';
        break;
      }
      
      /* Calculate the discout from previous partial deposits */
      $loan->nice_due = $loan->dues - $loan->discount;
      $loan->diff_due = $loan->dues - $loan->nice_due; // Difference w/ regular due
      $loan->diff_due = $loan->diff_due >= 0 ? '- ₡'.$loan->diff_due : '+ ₡'.$loan->diff_due*-1;
      
      /* Round nterests to the closest 1000 */
      $loan->nice_int = $this->nicecify($loan->interest);
      $loan->diff_int = $loan->nice_int - $loan->interest;
      $loan->diff_int = $loan->diff_int >= 0 ? '+ ₡'.$loan->diff_int : '- ₡'.$loan->diff_int*-1;
      
      /* Format the dates nicely */
      $loan->next_due_display = date('d/M/Y', strtotime( $loan->next_due ));
      $loan->date = date('d-M-Y', strtotime( $loan->created_at ));
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
    
    $loan = Loan::findOrFail( $request->input('id') );
    
    // TODO: Manage Exception //
    
    $PaymentsController = new PaymentsController;
    
    if ( $request->path() == 'prestamos/pagar')
    {
      /* Payment Type | Full or Minimun */
      $type = $request->input('type');
      
      /* Amount of Interests */
      $interests = $this->nicecify( $loan->interest );
      
      /* Extra Deposit */
      $deposit = $request->input('extra', 0);
      
      /* Amount to substract */
      $substract = ( $type == 'PC') ? ( $loan->dues - $loan->discount ) + $deposit : $deposit;
      
      /* Reset the discount after a PC payment */
      if ( $type == 'PC') $loan->discount = 0;
      
      /* Substract the deposit from the next PC payment  */
      if ( $deposit ) $loan->discount += $deposit;
      
      /* IF the discount is greater that the actual PC payment */
      if ( $loan->discount >= $loan->dues ) $loan->discount = 0;
      
      /* Increase the counter for minimal payments */
      if ( $type == 'PM') $loan->extentions++;
      
      /* Update the balance */
      $loan->balance -= $substract;
      
      /* Close the loan if balance is zero */
      if ( $loan->balance <= 0) $loan->balance = $loan->status = 0;
      
      /* Register the Due Payment */
      $amount = ( $type == 'PC' ) ? $substract - $deposit: $interests;
      $PaymentsController->store( $type, $loan->id, $amount, $loan->balance + $deposit);
      
      /* Register the Optional extra deposit */
      if ( $deposit ) $PaymentsController->store( 'AB', $loan->id, $deposit, $loan->balance);
      
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
    //
  }
  
  
  public function today()
  {
    $today = date('Y-m-d');
    $loans = Loan::where('next_due', $today )->where('status', 1)->get();
    
    $data = array( 'loans' => $loans );
    
    return view('loans.today', $data);
    
  }
  
  private function nicecify( $amount )
  {
    return round( $amount / 1000, 0, PHP_ROUND_HALF_UP) * 1000;
  }
  
}
