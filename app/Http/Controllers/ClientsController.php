<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\Models\Client;
use App\Models\Models;
use App\Models\Zones;
use App\Models\Assignments;

class ClientsController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }
  
  public function newClient(Request $request)
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
    $loans = $LoansController->displayLoans( $id );

    $zones = Zones::all();

    $has_asg = Assignments::where('target_id', $client->id)->where('type', 'C')->get();
    
    $data = array(
      'client' => $client,
      'loans' => $loans,
      'zones' => $zones,
      'is_asg' => !$has_asg->isEmpty()
    );
    
    /* Handle Exception */
    return view('profile', $data);
  }

  public function assign($id)
  {
    $asg = new Assignments;
    $asg->type = 'C';
    $asg->user_id = 2;
    $asg->target_id = $id;
    $asg->expiration = date('Y-m-d', strtotime('+1 day') );
    $asg->save();

    return back();
  }
}



