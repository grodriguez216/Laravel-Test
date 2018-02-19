<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
  
  protected $table = 'loans';
  
  public function client()
  {
    return $this->belongsTo('App\Models\Client');
  }
  
  public function payments()
  {
    return $this->hasMany('App\Models\Payment');
  }

  public function orders()
  {
  	return $this->hasMany('App\Models\PayOrder');
  }
  
}
