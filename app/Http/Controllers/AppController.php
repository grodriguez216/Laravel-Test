<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Validator;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Payment;
use App\Models\Zones;
use App\Models\Assignments;
use App\User;
use App\Notification;
use App\Helper;

class AppController extends Controller
{

  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
    $clients = Client::all();

    $nice_list = array();

    foreach ($clients as $cli)
    {
      $item = array();
      $item['value'] = $cli->id;
      $fullname = $cli->first_name . ' ' . $cli->last_name . " ( {$cli->phone} )";

      $fullname = str_replace('á', 'a', $fullname);
      $fullname = str_replace('é', '2', $fullname);
      $fullname = str_replace('í', 'i', $fullname);
      $fullname = str_replace('ó', 'o', $fullname);
      $fullname = str_replace('ú', 'u', $fullname);

      $item['label'] = $fullname;

      $nice_list[] = $item;
    }

    $json_list = json_encode( $nice_list );
    return view('home')->with( 'client_list', $json_list );
  }

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

    $loans = Loan::all();

    $this->create_csv( $loans );

    $data['loans'] = $loans;

    /* All Active Loan Objects */
    return view('reports', $data);
  }

  public function settings()
  {
    return view('settings')->with('notifications', Notification::all());
  }


  /* ==================================== Zones ==========================================*/
  public function zones()
  {
    $data['zones'] = Zones::all();
    return view('loans.zones', $data );
  }

  public function create_zone( Request $request )
  {
    $zone = new Zones();
    $zone->name = ucwords( trim( $request->input('name') ) );
    $zone->save();
    return redirect('/zonas');
  }

  public function delete_zone( $id )
  {
    Assignments::where('type', 'Z')->where('target_id', $id)->delete();
    Zones::destroy( $id );
    return redirect('/zonas');
  }



  /* ==================================== Users ==========================================*/

  public function users( )
  {
    $users = User::where('id', '>', 0)->get();
    return view('users', [ 'users' => $users ] );
  }

  public function payuser( Request $request )
  {
    $u = User::find( $request->input('id') );
    $u->aggregate = 0;
    $u->save();
    return back();
  }

  public function user_profile( $id = 1 )
  {
    $data['user'] = User::find( $id );

    $azones = Zones::all();
    $uzones = Assignments::where('user_id', $id)->where('type', 'Z')->get();

    /* zones the user is related to */
    $data['user_zones'] = [];

    /* all the other zones available */
    $data['all_zones'] = [];

    foreach ( $azones as $z )
    {
      $is_uzone = false;
      foreach ($uzones as $uz)
      {
        if ( $uz->target_id == $z->id)
        {
          $data['user_zones'][] = $z;
          $is_uzone = true;
        }
      }
      if ( !$is_uzone)
      {
        $data['all_zones'][] = $z;
      }
    }
    return view('loans.user_zones', $data );
  }

  public function update_user_zone( $id, $action, $zone )
  {

    if ( $action === 'A' )
    {
      $zone_asig = new Assignments;
      $zone_asig->type = 'Z';
      $zone_asig->user_id = $id;
      $zone_asig->target_id = $zone;

      $zone_asig->save();
    }
    else
    {
      Assignments::where('user_id', $id)
      ->where('target_id', $zone)
      ->where('type', 'Z')
      ->delete();
    }

    return redirect("/usuarios/perfil/$id");
  }

  public function create_new_user( Request $request )
  {
    $rules =
    [
      'name' => 'required',
      'email' => 'required|unique:users',
    ];

    $validator = Validator::make( $request->all(), $rules);

    if ( $validator->fails() )
      return redirect('/usuarios?ref=error');

    $user = new User();
    $user->name = ucwords( trim( $request->input('name') ) );
    $user->email = trim( $request->input('email') );
    $user->password = password_hash( trim( $request->input('email') ), PASSWORD_BCRYPT );
    $user->zones = '{"zones":[]}';
    $user->save();
    return redirect('/usuarios/perfil/' . $user->id );
  }

  public function delete_user( $id )
  {
    User::destroy( $id );
    return redirect('/usuarios');
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
      $data[] = $Loan->intrate . ' %';
      $data[] = number_format($Loan->regdue, 0);
      $data[] = number_format($Loan->mindue, 0);
      $data[] = $Loan->extentions;
      
      
      $payed = $Loan->payable - $Loan->balance;
      $data[] = number_format( $payed, 0);
      
      if( $Loan->payable )
      {
           $data[] =  ( $payed / $Loan->payable ) * 100 . ' %';
      }	
      else
      {
      	$data[] = "-";
      }


      fputcsv($file, $data);
    }

    fclose($file);
  }

}
