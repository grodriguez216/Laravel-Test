<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
  
  protected $table = 'payments';
  
  public function loan()
  {
    return $this->belongsTo('App\Loan');
  }
}
