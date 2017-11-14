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
Route::get('/ajustes', 'AppController@settings')->name('app.settings');
Route::get('/reportes', 'AppController@reports')->name('app.reports');


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
  /* Posts */
  Route::post('editar', 'ClientsController@update' )->name('clients.update');
  Route::post('borrar', 'ClientsController@destroy' )->name('clients.delete');
});


Route::prefix('usuarios')->group(function ()
{
  Route::get('/', 'AppController@users' );
  Route::get('perfil/{id}', 'AppController@user_profile');
  Route::get('update/{id}/{action}/{zone}', 'AppController@update_user_zone' );
  
  Route::post('agregar', 'AppController@create_new_user' );
});


Route::prefix('zonas')->group(function ()
{
  /* Gets */
  Route::get('/', 'AppController@zones')->name('app.zones');
  Route::get('borrar/{id}', 'AppController@delete_zone' )->name('zones.delete');
  /* Posts */
  Route::post('agregar', 'AppController@create_zone' )->name('zones.create');
});


Route::prefix('mensajes')->group(function ()
{
  Route::post('cambiar', 'AppController@update' )->name('messages.update');
});
