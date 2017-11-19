<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Payment;
use App\User;
use App\Notification;
use App\Helper;

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
    
    $this->create_csv( $loans );
    
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
    
    $data['loans'] = $loans;
    
    
    /* All Active Loan Objects */
    return view('loans.reports', $data);
  }
  
  /**
  * Show the Montly Reports Page.
  *
  * @return \Illuminate\Http\Response
  */
  public function settings()
  {
    return view('loans.settings')->with('notifications', Notification::all());
  }
  
  
  /* ==================================== Zones ==========================================*/
  
  public function zones()
  {
    $data['zones'] = \App\Models\Zones::all();
    return view('loans.zones', $data );
  }
  
  
  public function create_zone( Request $request )
  {
    $zone = new \App\Models\Zones();
    
    $zone->name = ucwords( trim( $request->input('name') ) );
    
    $zone->save();
    
    return redirect('/zonas');
  }
  
  
  public function delete_zone( $id )
  {
    \App\Models\Zones::destroy( $id );
    return redirect('/zonas');
  }
  
  
  public function users( )
  {
    $users = User::where('id', '>', 1)->get();
    return view('loans.users', [ 'users' => $users ] );
  }
  
  
  public function user_profile( $id = 1 )
  {
    $data['user'] = User::find( $id );
    
    /* zones the user is related to */
    $uzones = json_decode( $data['user']->zones );
    
    /* all the other zones available */
    $allzones = \App\Models\Zones::all();
    
    $data['user_zones'] = [];
    $data['all_zones'] = [];
    
    foreach ($allzones as $zone )
    {
      if ( in_array( $zone->id, (array) $uzones->zones ) )
      {
        $data['user_zones'][] = $zone;
      }
      else
      {
        $data['all_zones'][] = $zone;
      }
    }
    return view('loans.user_zones', $data );
  }
  
  public function update_user_zone( $id, $action, $zone )
  {
    $user = User::find( $id );
    $uzones = json_decode( $user->zones );
    $array_zones = $uzones->zones;
    
    if ( $action == 'D')
    {
      $key = array_search( $zone, $array_zones );
      /* remove the zone from the array */
      if  ( $key !== false ) unset( $array_zones[ $key ] );
    }
    else if ( $action = 'A' ) $array_zones[] = $zone;
    
    $uzones->zones = $array_zones;
    $user->zones = json_encode( $uzones );
    $user->update();
    
    return redirect('/usuarios/perfil/' . $user->id );
  }
  
  
  public function create_new_user( Request $request )
  {
    $user = new User();
    $user->name = ucwords( trim( $request->input('name') ) );
    $user->email = trim( $request->input('phone') );
    $user->password = password_hash( trim( $request->input('phone') ), PASSWORD_BCRYPT );
    $user->zones = "{'zones':[]}";
    $user->save();
    return redirect('/usuarios/perfil/' . $user->id );
  }
  
  
  
  public function update(Request $request)
  {
    $id = $request->input('notification');
    $text = $request->input('message');
    
    $notif = Notification::findOrFail( $id );
    
    $notif->message = trim( $text );
    
    $notif->update();
    
    return redirect('/ajustes');
    
  }
  
  
  private function nicecify( $amount )
  {
    return round( $amount / 1000, 0, PHP_ROUND_HALF_UP) * 1000;
  }
  
  
  private function create_csv( $loans )
  {
    $file  = fopen("files/reporte_completo.csv", 'w');
    
    /* Add the header fields */
    fputcsv($file, ['Prestamo', 'Nombre',  'Fecha', 'Monto', 'Pagable', 'Saldo', 'Interes', 'Monto Cuotas', 'Monto Intereses', 'Extenciones', 'Pagado', 'Progreso']);
    
    foreach ($loans as $Loan)
    {
      $data = array();
      
      $data[] = $Loan->id;
      $data[] = $Loan->client->first_name . ' ' . $Loan->client->last_name;
      $data[] = Helper::date( $Loan->created_at, 'd/m/Y');
      $data[] = number_format($Loan->loaned, 0);
      $data[] = number_format($Loan->payable, 0);
      $data[] = number_format($Loan->balance, 0);
      $data[] = $Loan->rate . ' %';
      $data[] = number_format($Loan->dues, 0);
      $data[] = number_format($Loan->interest, 0);
      $data[] = $Loan->extentions;
      
      $payed = $Loan->payable - $Loan->balance;
      $data[] = number_format( $payed, 0);
      $data[] =  ( $payed / $Loan->payable ) * 100 . ' %';
      
      fputcsv($file, $data);
    }
    
    fclose($file);
  }
  
}
