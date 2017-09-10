<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Auth::routes();

Route::get('/', 'AppController@index')->name('home');

Route::prefix('prestamos')->group(function ()
{
  /* Gets */
  Route::get('/', 'LoansController@index' )->name('loans.list');
  Route::get('agregar', 'LoansController@create' )->name('loans.create');
  Route::get('ver/{id}', 'LoansController@show' )->name('loans.details');
  Route::get('hoy', 'LoansController@today' )->name('loans.today');
  
  /* Posts */
  Route::post('agregar', 'LoansController@store' )->name('loans.store');
  Route::post('pagar', 'LoansController@update' )->name('loans.pay');
  Route::post('extender', 'LoansController@update' )->name('loans.update');
});


Route::prefix('clientes')->group(function ()
{
  /* Gets */
  Route::get('/', 'ClientsController@index' )->name('clients.list');
  Route::get('perfil/{id}', 'ClientsController@show' )->name('clients.profile');
  //Route::get('agregar', 'ClientsController@create' )->name('clients.create');
  //Route::get('editar/{id}', 'ClientsController@edit' )->name('clients.edit');
  
  /* Posts */
  //Route::post('agregar', 'ClientsController@store' )->name('clients.store');
  //Route::post('editar', 'ClientsController@update' )->name('clients.update');
  Route::post('borrar', 'ClientsController@destroy' )->name('clients.delete');
});
