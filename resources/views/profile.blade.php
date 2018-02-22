@extends('layouts.app')

@php
use App\Helper;
use App\User;
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

      <div class="card loan mb-4">
        <div class="card-header {{ $loan->status ? "bg-dark" : "bg-secondary" }}">
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
              @if ($loan->status)
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
                <td class="text-right {{ $loan->delays > 1 ? "text-danger" : "" }}">
                  <small>₡</small>
                  <span class="money">{{ $loan->pending }}</span> ({{ $loan->delays }})</td>
                </tr>
                @endif
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

              <div class="row">
                <div class="form-row justify-content-center">
                  <div class="col-9 big-input m-0">
                    <div class="input-group">
                      <span class="input-group-addon">₡</span>
                      <input id="input_credits-{{ $loan->id }}" type="text" pattern="\d*"
                      class="form-control amount-control money p-0" oninput="onCreditsChange({{$loan->id}})"
                      placeholder="Monto"  value="{{ $loan->due }}" required>
                    </div>
                  </div>
                  <div class="col-12 text-center">
                    <span id="label_mods-{{ $loan->id }}">
                      @if($loan->mod)
                      Descuento: <span class="money">{{ $loan->mod }}</span>
                      @else
                      @if ($loan->due > $loan->regdue)
                      Cuota Especial
                      @else
                      &nbsp;
                      @endif
                      @endif
                    </span>
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

                <div class="form-check form-check-inline mb-0 d-none">
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
                  <div class="col-4">
                    <div class="row">
                      <div class="col-3 text-center mt-2">X</div>
                      <div class="col">
                        <input id="input_multi-{{ $loan->id }}" type="text" pattern="\d*" class="form-control text-center" value="1" oninput="onMultiplierChange({{$loan->id}})">
                      </div>
                    </div>

                  </div>

                </div>
              </div> {{-- row --}}

              <div id="confirModal-{{ $loan->id }}" class="modal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-md" role="document">
                  <div class="modal-content">

                    <div class="modal-header">
                      <h5 class="modal-title text-center">Confirmación de Pago</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>

                    <div class="modal-body text-center">
                      <p>Seguro que desea grabar este pago?</p>
                      <h3>
                        <small>₡</small>
                        <span id="modal_total-{{$loan->id}}"></span>
                      </h3>
                    </div>

                    <div class="modal-footer justify-content-center py-4">
                      <form id="pf-{{ $loan->id }}" action="{{ route('loans.pay') }}" method="post">

                        {{ csrf_field() }}

                        {{-- Read-Only Values --}}
                        <input type="hidden" id="due-{{ $loan->id }}" value="{{ $loan->due }}">
                        <input type="hidden" id="reg-{{ $loan->id }}" value="{{ $loan->regdue }}">
                        <input type="hidden" id="mod-{{ $loan->id }}" value="{{ $loan->mod }}">
                        <input type="hidden" id="cred-{{ $loan->id }}" value="{{ $loan->credits }}">
                        <input type="hidden" id="min-{{ $loan->id }}" value="{{ $loan->mindue }}">

                        {{-- Values to Post --}}
                        <input type="hidden" name="id" value="{{ $loan->id }}">
                        <input type="hidden" name="credits" id="post_credits-{{ $loan->id }}" value="{{$loan->due}}">
                        <input type="hidden" name="type" id="post_type-{{ $loan->id }}" value="PC">
                        <input type="hidden" name="multi" id="post_multi-{{ $loan->id }}" value="1">

                        <button type="submit" class="btn btn-danger py-2 px-5">Confirmar Pago</button>
                        <button type="button" class="btn btn-dark py-2 px-3" data-dismiss="modal">Cancelar</button>
                      </form>
                    </div><!-- modal-footer -->
                  </div>
                </div>
              </div>


              @else
              <div class="col text-center mt-3">
                <a data-toggle="collapse" href="#paylist-{{ $loan->id }}">Historial de Pagos&nbsp;
                  <i class="fa fa-list-ul"></i>
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
                    <th><small class="bold pl-2">Fecha</small></th>
                    <th><small class="bold">Agente</small></th>
                    <th><small class="bold">Tipo</small></th>
                    <th><small class="bold">Monto</small></th>
                    <th><small class="bold">Balance</small></th>
                  </thead>
                  <tbody>
                    @foreach ($loan->payments->sortBy('id') as $payment)
                    @php
                    switch ( $payment->type )
                    {
                      case 'PC': $payment->type = 'Cuota'; break;
                      case 'PM': $payment->type = 'Minimo'; break;
                      case 'IN': $payment->type = 'Intereses'; break;
                      case 'AB': $payment->type = 'Abono'; break;
                      case 'RB': $payment->type = 'R'; break;
                    }
                    $agent = User::find( $payment->user_id );
                    @endphp
                    <tr>
                      <td class="pl-2">{{ Helper::date( $payment->created_at, 'd/m/Y - h:i A' ) }}</td>
                      <td>{{ $agent->name }}</td>
                      <td class="">{{ $payment->type }}</td>
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

            @if ($loan->payments->isNotEmpty())
            <div class="col-3 text-right">
              <a data-toggle="collapse" href="#paylist-{{ $loan->id }}">
                <i class="fa fa-list-ul"></i>&nbsp;Pagos
              </a>
            </div>
            @endif
          </div>

          <form id="dateForm-{{$loan->id}}" action="{{ route('loans.update') }}" method="post">
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




  /* ---------------------------------- EVENT BINDS ---------------------------------- */
  function onCreditsChange(target)
  {
    /* Get the new amount to pay */
    var d = get('input_credits-'+target).value;
    let input_credits =  ( d ) ? parseInt(d.replaceAll(',', '')) : 0;

    /* Update the inputs and form */
    get('input_credits-'+target).value = nicecify( input_credits );
    get('post_credits-'+target).value = input_credits;
  }

  function onMultiplierChange(target)
  {
    /* new multiplier factor */
    var input_multi = get('input_multi-'+target).value;
    var post_multi = get('post_multi-'+target).value;
    var post_credits = get('post_credits-'+target).value;

    /* Force input_multi to be at least 1 */
    input_multi = input_multi > 0 ? input_multi : 1;

    var totalCreds = ( parseInt(post_credits) / parseInt(post_multi) ) * parseInt(input_multi) ;

    /* Update the inputs and form values */
    get('post_credits-'+target).value = totalCreds;
    get('post_multi-'+target).value = input_multi;
    get('input_credits-'+target).value = nicecify(totalCreds);

    /* Disable input if the multiplier is set */
    get('input_credits-'+target).readOnly = ( input_multi > 1 );
  }
  
  function onTypeChange(type, target)
  {
    var due = get('due-'+target).value;
    var mod = get('mod-'+target).value;
    var min = get('min-'+target).value;
    var cred = get('cred-'+target).value;
    var reg = get('reg-'+target).value;

    get('post_type-'+target).value = type;

    switch ( type )
    {
      case 'PC':
      /* ---------------------------------- REGULAR DUES ---------------------------------- */
      get('input_credits-' + target).value = nicecify(due);

      if( mod > 0)
      {
        get(`label_mods-${target}`).innerHTML = "Descuento: " + nicecify(mod);
      }
      else
      {
        get(`label_mods-${target}`).innerHTML = "&nbsp";
        if( due > reg )
        {
          get(`label_mods-${target}`).innerHTML = "Cuota Especial";
        }
      }

      /* Update the postable credits */
      get('post_credits-'+target).value = due;

      /* ______________________________ END: REGULAR DUES ______________________________ */
      break;
      
      case 'PM':
      /* ---------------------------------- MINIMUN DUES ---------------------------------- */
      get('input_credits-' + target).value = nicecify(min);

      if( cred !== 0)
        get(`label_mods-${target}`).innerHTML = "Alterado:" + ( cred > 0 ? " +" : " -") + nicecify(cred);
      else
        get(`label_mods-${target}`).innerHTML = "&nbsp";

      /* Update the postable credits */
      get('post_credits-'+target).value = min;
      /* ______________________________ END: MINIMUN DUES ______________________________ */
      break;
      
      default:
      /* ---------------------------------- CUSTOM DUES ---------------------------------- */
      get('input_credits-' + target).value = "";
      get(`label_mods-${target}`).innerHTML = "&nbsp";
      /* ______________________________ END: CUSTOM DUES ______________________________ */
      break;

    }
  }
  /* ______________________________ END: EVENT BINDS ______________________________ */
  /* ---------------------------------- DATAPICKER ---------------------------------- */
  var $datepicker = null;
  var $active_form = null;

  function datepicker_init()
  {
    var options =
    {
      min: true,
      today: false,
      clear: false,
      close: false,
      hiddenName: true,
      onClose: onNextDueSet,
      formatSubmit: 'yyyy-mm-dd'
    };
    $datepicker = $('.datepicker').pickadate( options );
  }

  function onNextDueClick( form )
  {
    event.stopPropagation();
    window.$active_form = 'dateForm-' + form;
    window.$active_input = 'nd-' + form;
    var picker = $datepicker.pickadate('picker');
    picker.$root[0] = get( $active_input + '_root');
    picker.open();
  }
  
  function onNextDueSet( context )
  {
    get( $active_form ).submit();
  }
  /* ______________________________ END: DATAPICKER ______________________________ */
  /* ---------------------------------- MODAL ---------------------------------- */
  function showConfirModal( target )
  {
    get('modal_total-'+target).innerHTML = get('input_credits-'+target).value;


    $('#confirModal-'+target ).modal('show');
  }
  /* ______________________________ END: MODAL ______________________________ */
  /* ---------------------------------- OTHER ---------------------------------- */
  window.onload = function()
  {
    window.datepicker_init();
    window.nicecify_money();
    toggle('loader', false );
  };

  function nicecify_money()
  {
    var items = document.getElementsByClassName('money');
    for (var i = 0; i < items.length; i++)
    {
      if( items[i].value )
      {
        items[i].value = nicecify( items[i].value );  
      }
      else
      {
        items[i].innerHTML = nicecify( items[i].innerHTML );  
      } 
    }
  }
  /* ______________________________ END: OTHER ______________________________ */

  
</script>
@endsection
