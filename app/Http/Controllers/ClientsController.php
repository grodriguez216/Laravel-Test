<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\Client;

class ClientsController extends Controller
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
  * Store a newly created resource in storage.
  *
  * @param  \Illuminate\Http\Request  $request
  * @return \Illuminate\Http\Response
  */
  public function store(Request $request)
  {

    $attributes = array(
      'first_name' => $request->input('first_name'),
      'last_name' => $request->input('last_name'),
      'ssn' => $request->input('ssn'),
      'phone_home' => $request->input('phone_home'),
      'phone_work' => $request->input('phone_work'),
      'address_home' => $request->input('address_home'),
      'address_work' => $request->input('address_work'),
      'zone_id' => $request->input('zone_id')
    );
    
    return Client::firstOrCreate( ['phone' => $request->input('phone') ], $attributes );
  }

  public function update(Request $request)
  {
    $client = Client::find( $request->input('id') );
    $client->ssn = $request->input('ssn');
    $client->first_name = $request->input('first_name');
    $client->last_name = $request->input('last_name');
    $client->phone = $request->input('phone');
    $client->phone_home = $request->input('phone_home');
    $client->phone_work = $request->input('phone_work');
    $client->address_home = $request->input('address_home');
    $client->address_work = $request->input('address_work');

    $client->zone_id = $request->input('zone_id');

    $client->update();

    return back();
  }
  
  /**
  * Display the specified resource.
  *
  * @param  int  $id
  * @return \Illuminate\Http\Response
  */
  public function show($id)
  {
    try
    {
      $client = Client::findOrFail( $id );
    }
    catch(ModelNotFoundException $e)
    {
      return redirect('/');
    }
    
    $LoansController = new LoansController;
    $loans = $LoansController->show( $id );

    $zones = \App\Models\Zones::all();
    
    $data = array(
      'client' => $client,
      'loans' => $loans,
      'zones' => $zones
    );
    
    /* Handle Exception */
    
    return view('clients/profile', $data);
  }
}
