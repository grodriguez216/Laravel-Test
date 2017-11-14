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
        <div class="card-header d-flex justify-content-between" style="background:#fff">
          <h5>Perfil del Cliente</h5>
          <a class href="#" data-toggle="modal" data-target="#cliUpdateModal">editar</a>
        </div>

        <div class="card-body">
          <div class="row mb-4">
            <div class="col-12 mb-1"><small class="bold">Nombre:</small></div>
            <div class="col-12">{{ $client->first_name }}&nbsp;{{ $client->last_name }} ({{ $client->ssn }})</div>
          </div>
          <hr>
          <div class="row mb-4">
            <div class="col-12 mb-1"><small class="bold">Direccion (Casa)</small></div>
            <div class="col-12">{{ $client->address_home }}</div>
          </div>
          <div class="row mb-4">
            <div class="col-12 mb-1"><small class="bold">Direccion (Trabajo)</small></div>
            <div class="col-12">{{ $client->address_work }}</div>
          </div>
          <hr>
          <div class="row mb-4">
            <div class="col-12 mb-1"><small class="bold">Telefonos:</small></div>
            
            <div class="col-md-4">Principal</div>
            <div class="col-md-8"><a href="tel:{{ $client->phone }}">{{ $client->phone }}</a></div>

            <span class="col-md-4">Trabajo</span>
            <div class="col-md-8">
              <a href="tel:{{ $client->phone_work }}">{{ $client->phone_work }}</a>
            </div>
            <span class="col-md-4">Casa</span>
            <div class="col-md-8">
              <a href="tel:{{ $client->phone_home }}">{{ $client->phone_home }}</a>
            </div>
          </div>
        </div>
        <div class="card-footer text-center" style="background:#fff">
          <a class="btn btn-outline-danger w-100 my-3" href="/prestamos/agregar?auto=1&amp;key={{ $client->phone }}">Agregar Prestamo</a>
        </div>
      </div> {{-- card --}}
    </div> {{-- col-12/4 Left InfoPanel --}}


    <div class="col-12 col-md-8 scroll">
      @foreach ($loans->sortByDesc('created_at')->sortByDesc('status') as $loan)

      <div class="card loan mb-4">
        <div class="card-body">

          <h4 class="card-title">
            <div class="row">
              <div class="col text-left">Detalle de Prestamo</div>
              <div class="col text-right"><small class="text-muted">{{ $loan->date}}</small></div>
            </div>
          </h4>

          <hr>

          <div class="row">

            <div class="col-12 col-lg-5">
              <table class="table mb-0">
                <tr>
                  <td>Prestado:</td>
                  <td class="text-right">₡<span class="money">{{ $loan->loaned }}</span> </td>
                </tr>
                <tr>
                  <td>Total (+ {{ $loan->rate }}%):</td>
                  <td class="text-right">₡<span class="money">{{ $loan->payable }}</span></td>
                </tr>
                <tr>
                  <td><strong>Pendiente:</strong></td>
                  <td class="text-right">₡<span class="money"><strong>{{ $loan->balance }}</strong></span> </td>
                </tr>
                <tr>
                  <td>Duración:</td>
                  <td class="text-right">{{ $loan->duration }} +{{ $loan->extentions }}</td>
                </tr>
              </table>
            </div> {{-- col-5 --}}

            <div class="col-12 my-3 d-lg-none">
              <hr>
            </div>

            <div class="col-12 col-lg-7">
              @if ( $loan->status )
              <form action="{{ route('loans.pay') }}" method="post">

                <div class="row mb-0">
                  <div class="col text-right">
                    <h2>
                      <small>₡</small>
                      <span id="dues_label-{{ $loan->id }}" class="money">{{ $loan->nice_due }}</span>
                      <span id="interest_label-{{ $loan->id }}" class="money d-none">{{ $loan->nice_int }}</span>
                    </h2>
                  </div>
                  <div class="col pl-0 text-left">
                    <div class="input-group minimal">
                      <span class="input-group-addon">+</span>
                      <input id="input_amount" type="text" name="extra" pattern="\d*" class="form-control" placeholder="Extra">
                    </div>
                  </div>
                </div>{{--row --}}

                <div class="row mb-3">
                  <div class="col-6 text-right">
                    <span style="color:#999" id="round-d-{{$loan->id}}" class="">{{ $loan->diff_due }}</span>
                    <span style="color:#999" id="round-i-{{$loan->id}}" class="d-none">{{ $loan->diff_int }}</span>
                  </div>
                </div>{{--row --}}

                <div class="row mb-4">
                  <div class="col text-center">
                    <div class="form-check form-check-inline">
                      <label class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" name="type" onchange="onTypeChange('PC','{{ $loan->id }}')" value="PC" checked>
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">Completo</span>
                      </label>
                    </div>
                    <div class="form-check form-check-inline">
                      <label class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" name="type" onchange="onTypeChange('PM','{{ $loan->id }}')" value="PM">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">Minimo</span>
                      </label>
                    </div>
                  </div>
                </div> {{-- row --}}

                <div class="row">
                  <div class="col text-center">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" value="{{ $loan->id }}">
                    <input type="hidden" name="due" value="{{ $loan->nice_due }}">
                    <input type="hidden" name="int" value="{{ $loan->nice_int }}">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-50 ">Pagar</button>
                  </div>
                </div> {{-- row --}}

              </form>
              @else
              <div class="col text-center mt-3">
                <a data-toggle="collapse" href="#paylist-{{ $loan->id }}">Historial&nbsp;
                  <i class="fa fa-chevron-down"></i>
                </a>
              </div>
              @endif
            </div>{{-- row col-7 --}}

          </div> {{-- row --}}


          <div class="collapse p-2 mt-2" id="paylist-{{ $loan->id }}" style="">
            <hr>
            <div class="row">
              <div class="col py-3">
                <table class="table table-sm table-hover">
                  <thead class="thead-inverse">
                    <tr>
                      <th><small class="bold">Fecha</small></th>
                      <th><small class="bold">Tipo</small></th>
                      <th><small class="bold">Monto</small></th>
                      <th><small class="bold">Balance</small></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($loan->payments->sortBy('id') as $payment)
                    <tr>
                      <td>{{ Helper::date( $payment->created_at, 'd-M-Y' ) }}</td>
                      @php
                      switch ( $payment->type )
                      {
                        case 'PC': $payment->type = 'Completo'; break;
                        case 'PM': $payment->type = 'Minimo'; break;
                        case 'AB': $payment->type = 'Abono'; break;
                        case 'RB': $payment->type = 'Reembolso'; break;
                      }
                      @endphp
                      <td>{{ $payment->type }}</td>
                      <td>₡<span class="money">{{ $payment->amount }}</span></td>
                      <td>₡<span class="money">{{ $payment->balance }}</span></td>
                    </tr>
                    @endforeach
                  </tbody>
                  <tfoot>
                    <tr>
                      <td></td>
                      <td style="border-top:solid 1px #999">Total Pagado:</td>
                      <td style="border-top:solid 1px #999">₡<span class="money"><b>{{ $loan->payments->sum('amount') }}</b></span></td>
                      <td></td>
                    </tr>
                  </tfoot>
                </table>
              </div> {{-- col --}}
            </div> {{-- row --}}

            <div class="row d-none">
              <div class="col-4 mr-auto text-left">
                <a class="btn btn-outline-danger btn-sm" href="prestamos/reembolso/{{$loan->id}}">Reembolsar último abono</a>
              </div>
            </div>

          </div> {{-- collpse --}}

        </div> {{-- Card Body --}}

        @if ( $loan->status)
        <div class="card-footer">
          <div class="row">
            <div class="col">Proximo Pago</div>
            <div class="col"><a href="#" onclick="onNextDueClick({{$loan->id}})">{{ $loan->next_due_display }}</a></div>
            <div class="col">
              <a data-toggle="collapse" href="#paylist-{{ $loan->id }}">Historial&nbsp;
                <i class="fa fa-chevron-down"></i>
              </a>
            </div>
          </div>
          <form id="lf-{{$loan->id}}" action="{{ route('loans.update') }}" method="post">
            {{ csrf_field() }}
            <input type="hidden" name="id" value="{{ $loan->id }}">
            <input class="datepicker d-none" type="date" id="nd-{{ $loan->id }}" name="next_due" data-value="{{ $loan->next_due }}">
          </form>
        </div>
        @endif

      </div> {{-- Loan Card --}}
      @endforeach

    </div> {{-- col-12 scroll --}}

  </div> {{-- row --}}

</div> {{-- container --}}


<div id="cliUpdateModal" class="modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Actualizar Cliente # {{ $client->id }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <form id="cliUpdateForm" method="post" action="{{ route('clients.update') }}">
        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{ $client->id }}">

        <div class="modal-body">
          <div class="form-row">

            <div class="col-6 col-md-4 form-group">
              <input type="text"  name="first_name" class="form-control py-3" value="{{ $client->first_name }}" placeholder="Nombre *" required>
            </div>

            <div class="col-6 col-md-4 form-group}">
              <input type="text" name="last_name" class="form-control py-3" value="{{ $client->last_name }}" placeholder="Apellidos *" required>
            </div>

            <div class="col-6 col-md-4 form-group}">
              <input type="number" name="ssn" class="form-control py-3" value="{{ $client->ssn }}" placeholder="Cedula *" required>
            </div>

            <div class="col-12 col-md-4 form-group">
              <input type="number" name="phone" class="form-control py-3" value="{{ $client->phone }}" placeholder="Telefono *" required>
            </div>
            <div class="col-12 col-md-4 form-group">
              <input type="number" name="phone_home" class="form-control py-3" value="{{ $client->phone_home }}" placeholder="Telefono (casa)">
            </div>
            <div class="col-12 col-md-4 form-group">
              <input type="number" name="phone_work" class="form-control py-3" value="{{ $client->phone_work }}" placeholder="Telefono (trabajo)">
            </div>


            <div class="col-md-6 form-group mt-3">
              <textarea class="form-control" name="address_home" rows="3" placeholder="Direccion (Casa)">{{ $client->address_home }}</textarea>
            </div>
            <div class="col-md-6 form-group mt-3">
              <textarea class="form-control" name="address_work" rows="3" placeholder="Direccion (Trabajo)">{{ $client->address_work }}</textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-outline-danger">Guardar Cambios</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
        </div>

      </form>
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
    
    window.$active_form = 'lf-' + form;
    window.$active_input = 'nd-' + form;
    
    var picker = $datepicker.pickadate('picker');
    
    picker.$root[0] = get( $active_input + '_root');
    
    picker.open();
  }
  
  function onNextDueSet( context )
  {
    get( $active_form ).submit();
  }
  
  function onTypeChange(type, target)
  {

    var is_full = ( type == 'PC' );
    toggle('dues_label-' + target, is_full);
    toggle('interest_label-' + target, !is_full);
    
    toggle('round-d-' + target, is_full);
    toggle('round-i-' + target, !is_full);
    
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
