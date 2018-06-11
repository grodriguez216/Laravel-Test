<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayRoll extends Model
{
  
  protected $table = 'payroll';
  
  public function user()
  {
    return $this->belongsTo('Auth\User');
  }
  
}
