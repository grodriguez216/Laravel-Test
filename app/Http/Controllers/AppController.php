<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Client;
use App\Models\Loan;
use App\Models\Payment;

class AppController extends Controller
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
  * Show the application dashboard.
  *
  * @return \Illuminate\Http\Response
  */
  public function index()
  {  
    $clients = Client::all();
    
    $nice_list = array();
    
    foreach ($clients as $cli)
    {
      $item = array();
      $item['value'] = $cli->id;
      $item['label'] = $cli->first_name . ' ' . $cli->last_name . " ( {$cli->phone} )";
      $nice_list[] = $item;
    }
    
    $json_list = json_encode( $nice_list );
    
    return view('home')->with( 'client_list', $json_list );
  }
  
  
  /**
  * Show the Montly Reports Page.
  *
  * @return \Illuminate\Http\Response
  */
  public function reports(Request $request)
  {
    /* Object passed to the view */
    $data = array();
    
    /* Defalt values when undefined */
    $fallback_s = date('Y-m-d 00:00:00', strtotime('first day of this month'));
    $fallback_e = date('Y-m-d 23:59:59', strtotime('today'));
    
    /* Get the limits of the report */
    $rep_start = $request->input('desde', $fallback_s);
    $rep_end = $request->input('hasta', $fallback_e);
    
    /* Format dates for display */
    $data['rep_start'] = date('d/m/Y', strtotime($rep_start));
    $data['rep_end'] =  date('d/m/Y', strtotime($rep_end));
    
    /* All Active Loans */
    $loans = Loan::where('status', 1)->get();
    
    $data['total_loaned'] = $loans->sum('loaned');
    $data['total_pending'] = $loans->sum('balance');
    $data['active_loans'] = $loans->count();
    
    /* Payments Received this month */
    $payments = Payment::orderBy('id')
    ->where('created_at', '>', $fallback_s)
    ->where('created_at', '<', $fallback_e)
    ->get();
    
    $byType = $payments->groupBy('type');
    
    /* Total from payments */
    $data['total_received_pc'] = $byType->get('PC') ? $byType->get('PC')->sum('amount') : 0;
    $data['total_received_pm'] = $byType->get('PM') ? $byType->get('PM')->sum('amount') : 0;
    $data['total_received_ab'] = $byType->get('AB') ? $byType->get('AB')->sum('amount') : 0;
    $data['total_received'] = $payments->sum('amount');
    
    /* Earnigns from Payments */
    $data['total_earnings'] = 0;
    $data['total_earnings_pc'] = 0;
    $data['total_earnings_pm'] = 0;
    
    foreach ($payments as $payment)
    {
      switch ($payment->type) {
        case 'PC':
        $data['total_earnings'] += $payment->loan->interest;
        $data['total_earnings_pc'] += $payment->loan->interest;
        break;
        
        case 'PM':
        $data['total_earnings'] += $payment->amount;
        $data['total_earnings_pm'] += $payment->amount;
        break;
      }
    }
    
    
    /* All Active Loan Objects */
    $data['loans'] = $loans;
    
    return view('loans.reports', $data);
  }
  
  
  private function nicecify( $amount )
  {
    return round( $amount / 1000, 0, PHP_ROUND_HALF_UP) * 1000;
  }
  
  
}
