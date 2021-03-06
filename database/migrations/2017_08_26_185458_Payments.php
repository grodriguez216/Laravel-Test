<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Payments extends Migration
{
  /**
  * Run the migrations.
  *
  * @return void
  */
  public function up()
  {
    Schema::create('payments', function (Blueprint $table)
    {
      $table->increments('id');
      $table->integer('load_id');
      $table->integer('client_id');
      $table->datetime('date');
      $table->char('type');
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
    Schema::dropIfExists('payments');
  }
}
