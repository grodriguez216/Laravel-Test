<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayOrder extends Model
{
	protected $table = 'payorder';

	public function loan()
	{
		return $this->belongsTo('App\Models\Loan');
	}
}