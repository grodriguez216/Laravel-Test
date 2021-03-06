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
Route::get('/home', 'AppController@index')->name('home');
Route::get('/ajustes', 'AppController@settings')->name('app.settings');
Route::get('/reportes', 'AppController@reports')->name('app.reports');

Route::prefix('prestamos')->group(function ()
{
  /* Gets */
  Route::get('/', 'LoansController@index' )->name('loans.list');
  Route::get('agregar', 'LoansController@showCreateForm' )->name('loans.create');
  Route::get('ver/{id}', 'LoansController@show' )->name('loans.details');
  Route::get('hoy', 'LoansController@today' )->name('loans.today');
  Route::get('hoy/imprimir', 'LoansController@today_print' )->name('loans.today_print');
  
  /* Posts */
  Route::post('agregar', 'LoansController@createNewLoan' )->name('loans.store');
  Route::post('pagar', 'LoansController@update' )->name('loans.pay');
  Route::post('extender', 'LoansController@update' )->name('loans.update');
});

Route::prefix('debug')->group(function ()
{
  /* Gets */
  // Route::get('watch', 'LoansController@watch');
  // Route::get('addOrder/{id}', 'LoansController@addOrder');
  // Route::get('fix/all', 'LoansController@fixAll');
  // Route::get('fix/{id}', 'LoansController@fix');
  // Route::get('fake/all', 'LoansController@fake');
  // Route::get('fake/{id}', 'LoansController@fake');
  Route::get('fix', 'LoansController@fix');
});

Route::prefix('clientes')->group(function ()
{
  /* Gets */
  Route::get('/', 'ClientsController@index' )->name('clients.list');
  Route::get('perfil/{id}', 'ClientsController@show' )->name('clients.profile');
  Route::get('asignar/{cid}/{uid}/{exp}', 'ClientsController@assign' )->name('loans.assign');

  /* Posts */
  Route::post('editar', 'ClientsController@update' )->name('clients.update');
  Route::post('borrar', 'ClientsController@destroy' )->name('clients.delete');
});


Route::prefix('usuarios')->group(function ()
{
  Route::get('/', 'AppController@users' );
  Route::get('perfil/{id}', 'AppController@user_profile');
  Route::get('update/{id}/{action}/{zone}', 'AppController@update_user_zone' );
  Route::get('borrar/{id}', 'AppController@delete_user' );
  Route::get('bloquear/{id}', 'AppController@block_user' );
  Route::post('agregar', 'AppController@create_new_user' );
  Route::post('/pagar', 'AppController@payuser' )->name('users.pay');
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
