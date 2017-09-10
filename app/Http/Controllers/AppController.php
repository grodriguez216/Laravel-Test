<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Client;

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
}
