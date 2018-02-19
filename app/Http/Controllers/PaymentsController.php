<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Payment;

class PaymentsController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }
  
  public function addPayment( $type, $loan, $order, $amount, $balance )
  {
    $payment = new Payment;
    
    $payment->type = $type;
    $payment->loan_id = $loan;
    $payment->payorder_id = $order;
    $payment->amount = $amount;
    $payment->balance = $balance;
    $payment->save();
    
    return $payment;
  }
  
  public function getLoanPayments($loan_id)
  {
    $payments_list = Payment::where('loan_id', $loan_id)->get();
    
    foreach ($payments_list as $payment)
    {
      switch ( $payment->type )
      {
        case 'F': $payment->details = ' Completo'; break;
        case 'M': $payment->details = ' Minimo'; break;
        case 'E': $payment->details = ' Abono'; break;
      }
      $payment->date = date('d-M-Y', strtotime( $payment->created_at ));
    }
    return $payments_list;
  }

  public function addPayOrder( $loan_id, $amount )
  {
    
  }
  
}
