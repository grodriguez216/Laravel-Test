@extends('layouts.app')

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
      </div>
      <div class="col-md-8 scroll">
        @foreach ($loans as $loan)
          <div class="card mb-5">
            <div class="card-body">
              <h4 class="card-title">
                
                <div class="row">
                  <div class="col mr-auto">Detalle de Prestamo</div>
                  <div class="col text-right"><small>{{ $loan->date}}</small></div>
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
                            <input class="custom-control-input" type="radio" name="type" onchange="onTypeChange('F')" value="F" checked>
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Completo</span>
                          </label>
                        </div>
                        <div class="form-check form-check-inline">
                          <label class="custom-control custom-radio">
                            <input class="custom-control-input" type="radio" name="type" onchange="onTypeChange('M')" value="M">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description">Minimo</span>
                          </label>
                        </div>
                      </div>
                      
                      <div class="col-12 text-center">
                        {{ csrf_field() }}
                        <input type="hidden" name="id" value="{{ $loan->id }}">
                        <button type="submit" class="btn btn-outline-danger mt-4 w-50 ">Pagar</button>
                      </div>
                      
                    </div>{{-- row --}}
                  </form>
                  
                </div>
              </div>
              
            </div>
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
                <input class="datepicker d-none" type="date" id="next_due" name="next_due" value="{{ $loan->next_due }}">
              </form>
            </div>
          </div>
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
    var is_full = ( val == 'F' );
    toggle('dues_label', is_full);
    toggle('interest_label', !is_full);
  }
  
  function datepicker_init()
  {
    // initialize the DatePicker
    var options =
    {
      min: true,
      today: false,
      clear: false,
      close: false,
      hiddenName: true,
      onSet: onNextDueSet,
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
