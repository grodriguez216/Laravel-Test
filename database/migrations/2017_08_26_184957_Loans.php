<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Loans extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('loans', function (Blueprint $table)
    {
      $table->increments('id');
      $table->integer('client_id');
      $table->integer('category_id');
      
      $table->float('loaned_amount', 8, 2);
      $table->float('payable_amount', 8, 2);
      $table->float('current_balance', 8, 2);
      $table->float('dues_amount', 8, 2);
      $table->float('interest_rate', 2, 2); // 10, 20, 30
      
      $table->char('payments_weekday'); // l,k,m,j,v,s,d
      
      $table->date('due_date');
      $table->date('next_payment');
      $table->integer('extentions')->default(0);
      $table->timestamps();
    });
  }
  
  /**
  * Reverse the migrations.
  *
  * @return void
  */
  public function down()
  {
    Schema::dropIfExists('loans');
  }
}
