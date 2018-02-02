@extends('layouts.app')

@php
use App\Helper;
@endphp

@section('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('css/default.date.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/datepicker.css') }}">
@endsection

@section('content')

<div class="container-fluid pb-3">
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
            <div class="col-12 font-weight-bold">{{ $client->first_name }}&nbsp;{{ $client->last_name }}</div>
            <div class="col-12"># {{ $client->ssn }}</div>
          </div>
          <hr>
          <div class="row mb-3">
            <div class="col-12 mb-1"><small class="bold">Direccion (Casa)</small></div>
            <div class="col-12">
              @if ( strpos($client->address_home, 'maps') )
              <a target="_blank" href="{{ $client->address_home }}">Ver Mapa</a>
              @else
              {{ $client->address_home }}
              @endif
            </div>
          </div>
          <div class="row mb-3">
            <div class="col-12 mb-1"><small class="bold">Direccion (Trabajo)</small></div>
            <div class="col-12">{{ $client->address_work }}</div>
          </div>
          <hr>
          <div class="row mb-4">
            <div class="col-12 mb-1"><small class="bold">Telefonos:</small></div>
            
            <div class="col-md-4">Principal</div>
            <div class="col-md-8"><a href="tel:{{ $client->phone }}">{{ $client->phone }}</a></div>

            <div class="col-md-4">Trabajo</div>
            <div class="col-md-8">
              <a href="tel:{{ $client->phone_work }}">{{ $client->phone_work }}</a>
            </div>

            <div class="col-md-4">Casa</div>
            <div class="col-md-8">
              <a href="tel:{{ $client->phone_home }}">{{ $client->phone_home }}</a>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-4">Zona</div>
            <div class="col-md-8 font-weight-bold">{{ $client->zone->name }}</div>
          </div>

        </div>

        <div class="card-footer text-center" style="background:#fff">
          <a class="btn btn-outline-danger w-100 my-2" href="/prestamos/agregar?auto=1&amp;key={{ $client->phone }}">Agregar Prestamo</a>

          @if ( !$is_asg )
          <a class="btn w-100 my-2" href="/clientes/asignar/{{ $client->id }}">Asignar a Wilson</a>
          @endif

        </div>

      </div> {{-- card --}}
    </div> {{-- col-12/4 Left InfoPanel --}}


    <div class="col-12 col-md-8 mt-4 mt-sm-0">
      @foreach ($loans->sortByDesc('created_at')->sortByDesc('status') as $loan)

      <div class="card loan mb-4">
        <div class="card-body">

          <h4 class="card-title">
            <div class="row">
              <div class="col-7 text-left">Prestamo # {{ $loan->id }}</div>
              <div class="col-5 text-right"><small class="text-muted">{{ $loan->date}}</small></div>
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
                  <td>Total (+ {{ $loan->intrate }}%):</td>
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
                <tr>
                  <td>Pendientes:</td>
                  <td class="text-right">{{ $loan->delays }}</td>
                </tr>
              </table>
            </div> {{-- col-5 --}}

            <div class="col-12 my-2 d-lg-none">
              <hr>
            </div>

            <div class="col-12 col-lg-7">
              @if ( $loan->status )
              <form id="pf-{{ $loan->id }}" action="{{ route('loans.pay') }}" method="post">

                <div class="row">

                  <div id="regdue_box-{{ $loan->id }}" class="col-12 text-center">
                    <h2>
                      <small>₡</small>
                      <span id="dues_label-{{ $loan->id }}" class="money">{{ $loan->nice_due }}</span>
                      <span id="interest_label-{{ $loan->id }}" class="money d-none">{{ $loan->nice_int }}</span>
                      <small>
                        <span style="color:#999" id="round-d-{{$loan->id}}" class="">{{ $loan->diff_due }}</span>
                        <span style="color:#999" id="round-i-{{$loan->id}}" class="d-none">{{ $loan->diff_int }}</span>
                      </small>
                    </h2>
                  </div>

                  <div class="col-11 my-3">
                    <div class="input-group minimal">
                      <span class="input-group-addon">+</span>
                      <input id="input_amount-{{ $loan->id }}" type="text" name="extra" pattern="\d*" class="form-control" placeholder="Extra">
                    </div>
                  </div>

                  <div class="col-12 text-center">
                    <div  id="customcheckbox-{{$loan->id}}" class="form-check form-check-inline">
                      <label class="custom-control custom-radio">
                        <input id="customcheck-{{$loan->id}}" class="custom-control-input" type="checkbox" name="custompay" onchange="onCustomAmountCheck({{ $loan->id }}, this)">
                        <span class="custom-control-indicator"></span>
                        <span class="custom-control-description">Monto Personalizado</span>
                      </label>
                    </div>
                    <hr>
                  </div>
                </div>{{--row --}}



                <div class="row mb-3">

                </div>{{--row --}}

                <span id="payType-{{ $loan->id }}" class="d-none">PC</span>
                <div class="row  justify-content-center mb-2">
                  
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
                  
                </div> {{-- row --}}


                <div class="row justify-content-center pb-4">
                  <div class="col-8 col-md-4">
                    <div class="input-group minimal">
                      <span class="input-group-addon">Cuotas</span>
                      <input id="input_duemulti-{{ $loan->id }}" type="text" name="duemulti" pattern="\d*" class="form-control" value="1">
                    </div>
                  </div>
                </div>

                <div class="row justify-content-center">
                    {{ csrf_field() }}
                    <input type="hidden" name="id" value="{{ $loan->id }}">
                    <input type="hidden" name="due" value="{{ $loan->nice_due }}">
                    <input type="hidden" name="int" value="{{ $loan->nice_int }}">
                    <button type="button" onclick="showConfirModal( {{ $loan->id }} )" class="btn btn-outline-danger btn-sm w-50 ">Pagar</button>
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
                          ₡<span id="camount-{{$loan->id}}"></span> x <span id="cduemulti-{{$loan->id}}"></span>
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
                  <thead class="thead-inverse">
                    <tr>
                      <th><small class="bold">Fecha</small></th>
                      <th class="text-center"><small class="bold">Tipo</small></th>
                      <th><small class="bold">Monto</small></th>
                      <th><small class="bold">Balance</small></th>
                    </tr>
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
                  <tfoot>
                    <tr>
                      <td></td>
                      <td style="border-top:solid 1px #eee">Pagado:</td>
                      <td style="border-top:solid 1px #eee">₡<span class="money"><b>{{ $loan->payments->sum('amount') }}</b></span></td>
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
            <div class="col">Sig. Pago</div>
            <div class="col"><a href="#" onclick="onNextDueClick({{$loan->id}})">{{ $loan->next_due_display }}</a></div>
            <div class="col">
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
    var dm = get( 'input_duemulti-' + id ).value;

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
