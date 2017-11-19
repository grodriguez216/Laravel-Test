<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use App\Models\Client;
use App\Models\Loan;
use App\Models\Payment;
use App\User;


class PublicController extends Controller
{
  public function collect( Request $request )
  {
    if ( !$request->session()->get('auth2', false) )
      return view( 'auth/login', [ 'c' => 1 ] );

    /* zones the user is related to */
    $user = User::find( session('user') );
    $uzones = json_decode( $user->zones )->zones;

    $loans = DB::table('loans')
    ->select('*')
    ->join('clients', 'clients.id', '=', 'loans.client_id')
    ->whereIn("clients.zone_id", $uzones)
    ->where('loans.next_due', date('Y-m-d'))
    ->get();

    return view('loans.collect', [ 'loans' => json_decode($loans) ]);
  } /* End: collect */
  

  function login2( Request $request )
  {
    if ( $request->input('email') && $request->input('password') )
    {
      $user = User::where('email', $request->input('email') )->first();
      $auth = password_verify( $request->input('password'), $user->password );

      if ( $auth && $user->id > 1 )
      {
        $request->session()->put('auth2', true);
        $request->session()->put('user', $user->id);
        $request->session()->put('name', $user->name );
        return redirect('/cobrar');
      }
    }

    // else:
    return view( 'auth/login', [ 'c' => 1 ] );
  }

}