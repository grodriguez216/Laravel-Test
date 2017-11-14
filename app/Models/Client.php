<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
  
  protected $table = 'clients';
  
  protected $guarded = [];
  
  public function loans()
  {
    return $this->hasMany('App\Models\Loan');
  }

  public function zone()
  {
  	return $this->belongsTo('App\Models\Zones');
  }
  
}
