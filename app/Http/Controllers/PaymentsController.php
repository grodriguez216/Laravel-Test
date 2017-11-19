<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Payment;

class PaymentsController extends Controller
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
    //
  }
  
  /**
  * Show the form for creating a new resource.
  *
  * @return \Illuminate\Http\Response
  */
  public function create()
  {
    return view('loans.pay');
  }
  
  /**
  * Store a newly created resource in storage.
  *
  * @param  StdClass  $request
  * @return \Illuminate\Http\Response
  */
  public function store(  $type, $loan, $amount, $balance )
  {
    $payment = new Payment;
    
    $payment->type = $type;
    $payment->loan_id = $loan;
    $payment->amount = $amount;
    $payment->balance = $balance;
    $payment->save();
    
    return $payment;
  }
  
  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show($id)
  {
    $payments_list = Payment::where('loan_id', $id)->get();
    
    foreach ($payments_list as $payment)
    {
      switch ( $payment->type )
      {
        case 'F': $payment->type = ' Completo'; break;
        case 'M': $payment->type = ' Minimo'; break;
        case 'E': $payment->type = ' Abono'; break;
      }
      
      $payment->date = date('d-M-Y', strtotime( $payment->created_at ));
    }
    
    return $payments_list;
  }
  
  /**
  * Show the form for editing the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function edit($id)
  {
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
}
