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
      'phone_home' => $request->input('phone_home', 'No especificado'),
      'phone_work' => $request->input('phone_work', 'No especificado'),
      'address_home' => $request->input('address_home', 'No especificado'),
      'address_work' => $request->input('address_work', 'No especificado')
    );
    
    return Client::firstOrCreate( ['phone' => $request->input('phone') ], $attributes );
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
    
    $data = array(
      'client' => $client,
      'loans' => $loans
    );
    
    /* Handle Exception */
    
    return view('clients/profile', $data);
  }
}
