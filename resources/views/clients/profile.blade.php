@extends('layouts.app')

@php
use App\Helper;
@endphp

@section('styles')
  <link rel="stylesheet" type="text/css" href="{{ asset('css/default.date.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('css/datepicker.css') }}">
@endsection

@section('content')
  
  <div class="container-fluid">
    
    <div class="row">
      <div class="col-12 col-md-4">
        <div class="card">
          <div class="card-body">
            <h4 class="card-title">Perfil del Cliente</h4>
            <table class="table table-sm">
              <tr>
                <td>Nombre:</td>
                <td>{{ $client->first_name }}&nbsp;{{ $client->last_name }}</td>
              </tr>
              <tr>
                <td>Telefono:</td>
                <td><a href="tel:{{ $client->phone }}">{{ $client->phone }}</a></td>
              </tr>
              <tr>
                <td>Direccion:</td>
                <td>{{ $client->address }}</td>
              </tr>
            </table>
          </div>
        </div>
        
        <div class="text-center">
          <a class="btn btn-outline-danger w-100 my-3" href="/prestamos/agregar?auto=1&amp;key={{ $client->phone }}">Agregar Prestamo</a>
        </div>
        
        
        
      </div>
      <div class="col-md-8 scroll">
        @foreach ($loans->sortByDesc('created_at') as $loan)
          <div class="card mb-5">
            <div class="card-body">
              <h4 class="card-title">
                
                <div class="row">
                  <div class="col mr-auto">Detalle de Prestamo</div>
                  <div class="col text-right"><small class="text-muted">{{ $loan->date}}</small></div>
                </div>
              </h4>
              
              <div class="row">
                <div class="col-md-6">
                  <table class="table table- mb-0">
                    <tr>
                      <td>Monto Inicial:</td>
                      <td class="text-right">₡<span class="money">{{ $loan->loaned }}</span> </td>
                    </tr>
                    <tr>
                      <td>Total a pagar:</td>
                      <td class="text-right">₡<span class="money">{{ $loan->payable }}</span></td>
                    </tr>
                    <tr>
                      <td><strong>Pendiente:</strong></td>
                      <td class="text-right">₡<span class="money"><strong>{{ $loan->balance }}</strong></span> </td>
                    </tr>
                    <tr>
                      <td>Modalidad:</td>
                      <td class="text-right">{{ $loan->duration }} / {{ $loan->rate }}%</td>
                    </tr>
                  </table>
                </div>
                
                <div class="col-md-6">
                  
                  @if ( $loan->status )
                    <form action="{{ route('loans.pay') }}" method="post">
                      <div class="row">
                        <div class="col-12 text-center">
                          <h2 class="w-100 mb-4">₡
                            <span id="dues_label" class="money">{{ $loan->dues }}</span>
                            <span id="interest_label" class="money d-none">{{ $loan->interest }}</span>
                          </h2>
                        </div>
                        <div class="col-12 text-center">
                          <div class="form-check form-check-inline">
                            <label class="custom-control custom-radio">
                              <input class="custom-control-input" type="radio" name="type" onchange="onTypeChange('PC')" value="PC" checked>
                              <span class="custom-control-indicator"></span>
                              <span class="custom-control-description">Completo</span>
                            </label>
                          </div>
                          <div class="form-check form-check-inline">
                            <label class="custom-control custom-radio">
                              <input class="custom-control-input" type="radio" name="type" onchange="onTypeChange('PM')" value="PM">
                              <span class="custom-control-indicator"></span>
                              <span class="custom-control-description">Minimo</span>
                            </label>
                          </div>
                        </div>
                        
                        <div class="col-12 text-center">
                          {{ csrf_field() }}
                          <input type="hidden" name="id" value="{{ $loan->id }}">
                          <button type="submit" class="btn btn-outline-danger mt-2 w-50 ">Pagar</button>
                        </div>
                      </div>{{-- row --}}
                    </form>
                  @endif
                  <div class="col-12 text-center mt-3">
                    <a data-toggle="collapse" href="#paylist-{{ $loan->id }}">Ver Historial de Pagos</a>
                  </div>
                </div>
              </div>
              
              <div class="collapse" id="paylist-{{ $loan->id }}">
                <hr>
                <div class="card card-body">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>Fecha de Pago</th>
                        <th>Fecha Acordada</th>
                        <th>Tipo de Pago</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($loan->payments->sortByDesc('created_at') as $payment)
                        <tr>
                          <td>{{ Helper::date( $payment->created_at, 'd-M-Y | h:i A' ) }}</td>
                          <td>{{ Helper::date( $payment->due_date ) }}</td>
                          <td>{{ $payment->type }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                  <div class="row">
                    <div class="col-4 mr-auto text-left">
                      <a class="btn btn-outline-danger btn-sm" href="prestamos/reembolso/{{$loan->id}}">Deshacer último pago</a>
                    </div>
                    
                    <div class="col ml-auto text-right">
                      <small class="text-muted"><b>PC</b>: Pago Completo   <b>PM</b>: Pago Minimo   <b>RM</b>: Reembolso</small>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            @if ( $loan->status)
              <div class="card-footer">
                <table class="table table-sm mb-0">
                  <tr>
                    <td><strong>Proximo Pago:</strong></td>
                    <td><a href="#" onclick="onNextDueClick('lf-{{$loan->id}}')">{{ $loan->next_due_display }}</a></td>
                    <td><strong>Extenciones:</strong></td>
                    <td>{{ $loan->extentions }}</td>
                  </tr>
                </table>
                <form id="lf-{{$loan->id}}" action="{{ route('loans.update') }}" method="post">
                  {{ csrf_field() }}
                  <input type="hidden" name="id" value="{{ $loan->id }}">
                  <input class="datepicker d-none" type="date" id="next_due" name="next_due" data-value="{{ $loan->next_due }}">
                </form>
              </div>
            </div>
          @endif
        @endforeach
      </div>
      
    </div>
    
  </div>
  
  
  
@endsection

@section('scripts')
  <script src="{{ asset('js/picker.js') }}"></script>
  <script src="{{ asset('js/picker.date.js') }}"></script>
  <script src="{{ asset('js/picker.es_ES.js') }}"></script>
  <script type="text/javascript">
  
  // Global Scope
  var $datepicker = null;
  var $active_form = null;
  
  window.onload = function()
  {
    window.datepicker_init();
    window.nicecify_money();
    toggle('loader', false );
  };
  
  
  // FUNCTION DEFINITION BEGINS //
  
  function onNextDueClick( form )
  {
    event.stopPropagation();
    window.$active_form = form;
    var picker = $datepicker.pickadate('picker');
    picker.open();
  }
  
  function onNextDueSet( context )
  {
    get( $active_form ).submit();
  }
  
  function onTypeChange(val)
  {
    var is_full = ( val == 'PC' );
    toggle('dues_label', is_full);
    toggle('interest_label', !is_full);
  }
  
  function datepicker_init()
  {
    // initialize the DatePicker
    var options =
    {
      min: 'true',
      today: false,
      clear: false,
      close: false,
      hiddenName: true,
      onClose: onNextDueSet,
      formatSubmit: 'yyyy-mm-dd'
    };
    $datepicker = $('.datepicker').pickadate( options );
  }
  
  function nicecify_money()
  {
    var items = document.getElementsByClassName('money');
    
    for (var i = 0; i < items.length; i++) {
      items[i].innerHTML = nicecify( items[i].innerHTML );
    }
    
  }
  
  </script>
@endsection
