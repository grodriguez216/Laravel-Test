<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;

use App\Models\Loan;

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
    $loan->payable = $request->input('total');
    $loan->balance = $request->input('total');
    
    $loan->dues = $request->input('dues');
    $loan->interest = $request->input('partial');
    $loan->rate = $request->input('interest');
    
    $loan->duration = $request->input('duration');
    $loan->payplan = $request->input('payplan');
    $loan->details = $request->input('details');
    
    $due_date = time();
    $next_due = time();
    
    $day_to_string = array(
      1 => 'monday',
      2 => 'tuesday',
      3 => 'wednesday',
      4 => 'thursday',
      5 => 'friday',
      6 => 'saturday',
      7 => 'sunday'
    );
    
    switch ( $loan->payplan )
    {
      case 'we':
      $pay_day = $day_to_string[ $loan->details ];
      $due_date = strtotime("+{$loan->duration} weeks");
      $next_due = strtotime("next $pay_day");
      //$next_week = strtotime("+1 week");
      break;
      
      case 'bw':
      $pay_day = $day_to_string[ $loan->details ];
      $in_weeks = $loan->duration * 2;
      $due_date = strtotime("+{$in_weeks} weeks");
      $next_week = strtotime("+1 week");
      $next_due = strtotime("next $pay_day", $next_week);
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
    
    /* -------------------------- begin: Debug Only -------------------------- */
    
    //echo "<pre>";
    //print_r( json_decode( $loan ) );
    //die("Line: ".__LINE__);
    
    /* -------------------------- end: Debug Only -------------------------- */
    
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
    $loan = Loan::find( $id );
    
    return view('loans.pay', [ 'info' => $loan ]);
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
  public function update(Request $request, $id)
  {
    //
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
    # code...
  }
  
  
}
