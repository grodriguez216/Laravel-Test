@extends('layouts.app')

@php
use App\Helper;
@endphp

@section('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('css/default.date.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/datepicker.css') }}">
<style type="text/css">
.thin
{
  font-weight: 100 !important;
}

.table-hover td, .table-hover th
{
  border: none !important;
}

</style>
@endsection

@section('content')
<br>
<div class="container-fluid pb-3">
  <div class="row">

    <div class="col-12 col-md-4">
      <div class="card">

        <div class="card-header bg-dark">
          <div class="row text-light">
            <div class="col text-left">Cliente # {{$client->id}}</div>
            <div class="col text-right">
              <a class="text-light" href="#" data-toggle="modal" data-target="#cli_modal">editar</a>
            </div>
          </div>
        </div>

        <div class="card-body">

          <div class="row">
            <div class="col-12"><small>Nombre:</small></div>
            <div class="col-12 font-weight-bold">{{ $client->first_name }}&nbsp;{{ $client->last_name }}</div>
          </div>

          <br>

          <div class="row">
            <div class="col">
              <div class="row">
                <div class="col-12"><small>Cédula:</small></div>
                <div class="col-12 font-weight-bold">{{ $client->ssn }}</div>
              </div>
            </div>
            <div class="col">
              <div class="row">
                <div class="col-12"><small>Zona Cobro:</small></div>
                <div class="col-12 font-weight-bold">{{ $client->zone->name }}</div>
              </div>
            </div>
          </div>

          <hr>

          <div class="row">
            <div class="col-12"><small>Direccion (Casa)</small></div>
            @if ( strpos($client->address_home, 'maps') )
            <div class="col-12 py-2">
              <a target="_blank" class="text-danger" href="{{$client->address_home}}">Ver en Google Maps</a>
            </div>
            @else
            <div class="col-12 font-weight-bold">{{ $client->address_home }}</div>
            @endif
          </div>

          <div class="row">
            <div class="col-12"><small>Direccion (Trabajo)</small></div>
            @if ( strpos($client->address_work, 'maps') )
            <div class="col-12 py-2">
              <a target="_blank" class="text-danger" href="{{$client->address_work}}">Ver en Google Maps</a>
            </div>
            @else
            <div class="col-12 font-weight-bold">{{ $client->address_work }}</div>
            @endif
          </div>

          <hr>

          <div class="row">
            <div class="col-12"><small>Telefonos:</small></div>

            <div class="col-12">

              <div class="row">
                <div class="col font-weight-bold ">Principal</div>
                <div class="col">
                  <a class="text-danger" href="tel:{{ $client->phone }}">{{ $client->phone }}</a>
                </div>
              </div>

              @if ( $client->phone_work )
              <div class="row">
                <div class="col font-weight-bold ">Trabajo</div>
                <div class="col">
                  <a class="text-danger" href="tel:{{ $client->phone_work }}">{{ $client->phone_work }}</a>
                </div>
              </div>
              @endif              

              @if ( $client->phone_home )
              <div class="row">
                <div class="col font-weight-bold ">Casa</div>
                <div class="col">
                  <a class="text-danger" href="tel:{{ $client->phone_home }}">{{ $client->phone_home }}</a>
                </div>
              </div>
              @endif


            </div>
          </div>

        </div>

        <div class="card-footer text-center" style="background:#fff">
          <a class="btn text-light bg-dark w-100 my-2" href="/prestamos/agregar?auto=1&amp;key={{ $client->phone }}">Agregar Prestamo</a>

          @if ( !$is_asg )
          <a class="btn w-100 mt-1" href="/clientes/asignar/{{ $client->id }}">Asignar a Wilson</a>
          @endif

        </div>

      </div> {{-- card --}}
    </div> {{-- col-12/4 Left InfoPanel --}}

    <div class="col-12 col-md-8 mt-4 mt-sm-0">
      @foreach ($loans->sortByDesc('created_at')->sortByDesc('status') as $loan)

      <div class="d-none">
        <span id="due-{{ $loan->id }}">{{ $loan->due }}</span>
        <span id="mod-{{ $loan->id }}">{{ $loan->mod }}</span>
        <span id="cred-{{ $loan->id }}">{{ $loan->credits }}</span>
        <span id="min-{{ $loan->id }}">{{ $loan->mindue }}</span>
        <span id="type-{{ $loan->id }}">PC</span>  
      </div>

      <div class="card loan mb-4">
        <div class="card-header bg-dark">
         <div class="row text-light">
          <div class="col text-left">Prestamo # {{ $loan->id }}</div>
          <div class="col text-right">{{ $loan->date}}</div>
        </div>
      </div>

      <div class="card-body">

        <div class="row">
          <div class="col-12 col-lg-5" style="border-right: 1px solid #eee">
            <table class="table table-hover">
              <tr>
                <th>Prestado</th>
                <td class="text-right"><small>₡</small><span class="money">{{ $loan->loaned }}</span></td>
              </tr>
              <tr>
                <th>Saldo</th>
                <td class="text-right text-danger">₡<span class="money">{{ $loan->balance }}</span></td>
              </tr>
              <tr>
                <th>Duración</th>
                <td class="text-right">{{ $loan->duration }}</td>
              </tr>
              <tr>
                <th>Pendientes</th>
                <td class="text-right"><small>₡</small><span class="money">{{ $loan->pending }}</span> ({{ $loan->delays }})</td>
              </tr>
              <tr>
                <th>Intereses</th>
                <td class="text-right">{{ $loan->intrate }}%</td>
              </tr>
              <tr>
                <th>Extensiones</th>
                <td class="text-right">{{ $loan->extentions }}</td>
              </tr>
            </table>
          </div> {{-- col-5 --}}

          <div class="col-12 my-2 d-lg-none">
            <hr>
          </div>

          <div class="col-12 col-lg-7" style="padding-right: 10px">

            @if ( $loan->status )
            <form id="pf-{{ $loan->id }}" action="{{ route('loans.pay') }}" method="post">

              <div class="row">

                <div class="form-row justify-content-center">
                  <div class="col-9 big-input m-0">
                    <div class="input-group">
                      <span class="input-group-addon">₡</span>
                      <input id="credits-{{ $loan->id }}" type="text" pattern="\d*"
                      class="form-control amount-control p-0"
                      placeholder="Monto"  value="{{ $loan->due }}" required>
                    </div>
                  </div>
                  <div class="col-12 text-center">
                    Alterado: {{ $loan->mod >= 0 ? '+' : ''}}   {{ $loan->mod }}
                  </div>
                  
                </div>
              </div>{{--row --}}

              <div class="row justify-content-center bg-light py-3 my-3" style="border: 1px solid #eee">
                <div class="form-check form-check-inline mb-0">
                  <label class="custom-control custom-radio mb-0">
                    <input class="custom-control-input" type="radio" name="type" onchange="onTypeChange('PC','{{ $loan->id }}')" value="PC" checked>
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Completo</span>
                  </label>
                </div>

                <div class="form-check form-check-inline mb-0">
                  <label class="custom-control custom-radio mb-0">
                    <input class="custom-control-input" type="radio" name="type" onchange="onTypeChange('PM','{{ $loan->id }}')" value="PM">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Minimo</span>
                  </label>
                </div>

                <div class="form-check form-check-inline mb-0">
                  <label class="custom-control custom-radio mb-0">
                    <input class="custom-control-input" type="radio" name="type" onchange="onTypeChange('OT','{{ $loan->id }}')" value="OT">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Otro</span>
                  </label>
                </div>
              </div> {{-- row --}}

              <div class="row mt-5">
                {{ csrf_field() }}
                <input type="hidden" name="id" value="{{ $loan->id }}">
                <input type="hidden" name="due" value="{{ $loan->credits }}">
                <input type="hidden" name="int" value="{{ $loan->interest }}">
                <div class="col-12 d-flex justify-content-center">
                  <button type="button" onclick="showConfirModal( {{ $loan->id }} )" class="btn btn-dark px-5">Pagar</button>
                  <div class="col-3">
                    <div class="row">
                      <div class="col-3 text-center mt-2">X</div>
                      <div class="col">
                        <input id="input_multi-{{ $loan->id }}" type="text" name="duemulti" pattern="\d*" class="form-control text-center" value="1">
                      </div>
                    </div>

                  </div>

                </div>
              </div> {{-- row --}}


              <div id="confirModal-{{ $loan->id }}" class="modal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-sm" role="document">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title text-center">Confirmar Pago</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body text-center">
                      <p>Seguro que desea realizar este pago <span id="ctype-{{$loan->id}}" class="font-weight-bold"></span>?</p>
                      <h3>
                        <small>₡</small><span id="camount-{{$loan->id}}"></span> x <span id="cduemulti-{{$loan->id}}"></span>
                      </h3>

                    </div>
                    <div class="modal-footer">
                      <button type="submit" class="btn btn-outline-danger px-4">Si, Pagar</button>
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                  </div>
                </div>
              </div>
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
            <div class="col-12 py-3">
              <table class="table table-sm table-hover">
                <thead class="bg-dark text-light">
                  <th><small class="bold pl-3">Fecha</small></th>
                  <th><small class="bold">Tipo</small></th>
                  <th><small class="bold">Monto</small></th>
                  <th><small class="bold">Balance</small></th>
                </thead>
                <tbody>
                  @foreach ($loan->payments->sortBy('id') as $payment)
                  <tr>
                    <td>{{ Helper::date( $payment->created_at, 'd-m-Y' ) }}</td>
                    @php
                    switch ( $payment->type )
                    {
                      case 'PC': $payment->type = 'C'; break;
                      case 'PM': $payment->type = 'M'; break;
                      case 'AB': $payment->type = 'A'; break;
                      case 'RB': $payment->type = 'R'; break;
                    }
                    @endphp
                    <td class="text-center">{{ $payment->type }}</td>
                    <td><span class="money">{{ $payment->amount }}</span></td>
                    <td><span class="money">{{ $payment->balance }}</span></td>
                  </tr>
                  @endforeach
                </tbody>
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

          <div class="col">
            <span class="pr-5">Próximo Pago</span>
            <a href="#" onclick="onNextDueClick( {{$loan->id}} )">
              {{ $loan->next_due_display }}
            </a>
            ( {{ date('h A', strtotime("{$loan->paytime}:00")) }} )
          </div>

          <div class="col-3 text-right">
            <a data-toggle="collapse" href="#paylist-{{ $loan->id }}">Pagos&nbsp;
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


<div id="cli_modal" class="modal" tabindex="-1" role="dialog">
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
              <input type="number" name="phone" class="form-control py-3" value="{{ $client->phone }}" placeholder="Telefono *" max="99999999" required>
            </div>
            <div class="col-12 col-md-4 form-group">
              <input type="number" name="phone_home" class="form-control py-3" value="{{ $client->phone_home }}" placeholder="Telefono (casa)" max="99999999">
            </div>
            <div class="col-12 col-md-4 form-group">
              <input type="number" name="phone_work" class="form-control py-3" value="{{ $client->phone_work }}" placeholder="Telefono (trabajo)" max="99999999">
            </div>
            <div class="col-md-6 form-group mt-3">
              <textarea class="form-control" name="address_home" rows="3" placeholder="Direccion (Casa)">{{ $client->address_home }}
              </textarea>
            </div>
            <div class="col-md-6 form-group mt-3">
              <textarea class="form-control" name="address_work" rows="3" placeholder="Direccion (Trabajo)">{{ $client->address_work }}</textarea>
            </div>

            <div class="col-md-12 mt-3">
              <label class="px-2">Zona de Cobro:</label>
              <select class="custom-select" name="zone_id">
                @foreach ( $zones as $zone)
                <option value="{{ $zone->id }}" {{ $zone->id == $client->zone_id ? 'selected' : '' }}>
                  {{ $zone->name }}
                </option>
                @endforeach
              </select>
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

    get('payType-'+target).innerHTML = type;

    var is_full = ( type == 'PC' );
    toggle('dues_label-' + target, is_full);
    toggle('interest_label-' + target, !is_full);
    
    toggle('round-d-' + target, is_full);
    toggle('round-i-' + target, !is_full);

    get('customcheck-'+ target).checked = false;
    toggle_v('customcheckbox-'+ target, is_full);

    toggle_v('regdue_box-'+ target, true );
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

  function showConfirModal( id )
  {
    var type = get('payType-' + id ).innerHTML;
    var pc =   get('dues_label-'+ id).innerHTML;
    var pm = get('interest_label-'+ id).innerHTML;
    var ex = get('input_amount-'+ id).value;
    var dm = get( 'input_multi-' + id ).value;

    /* Parse ints */
    pc = parseInt(pc.replaceAll(',', ''));
    pm = parseInt(pm.replaceAll(',', ''));
    ex = ex ? parseInt( ex ) : 0;

    get('ctype-' + id).innerHTML = ( type === 'PC' ) ? 'Completo' : 'Minimo';

    if ( get('customcheck-'+ id).checked ) type = 'EX';

    switch( type )
    {
      case 'PC':get('camount-' + id).innerHTML = nicecify(pc + ex); break;
      case 'PM':get('camount-' + id).innerHTML = nicecify(pm + ex); break;
      case 'EX':get('camount-' + id).innerHTML = nicecify(ex);
    }

    get('cduemulti-'+id).innerHTML = dm;

    $('#confirModal-' + id ).modal('show');
  }

  function onCustomAmountCheck( id, element )
  {
    toggle_v( 'regdue_box-' +id , !element.checked );
  }
  
</script>
@endsection
