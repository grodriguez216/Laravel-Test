@extends('layouts.app')

@section('styles')
<style type="text/css">
label
{
  font-weight: lighter !important;
  text-align: center;
  color: #585858;
  width: 100%;
}
</style>
<link rel="stylesheet" type="text/css" href="{{ asset('css/default.date.css') }}">
<link rel="stylesheet" type="text/css" href="{{ asset('css/datepicker.css') }}">
@endsection

@section('content')
<div class="container-fluid">
  <div class="row py-4">
    <div class="col-md-6">
      <label class="page-header text-center">Monto a Prestar</label>
      <div class="form-row justify-content-center">
        <div class="col-9 big-input">
          <div class="input-group">
            <span class="input-group-addon">₡</span>
            <input id="input_loan_amount" type="text" pattern="\d*" class="form-control amount-control py-3" placeholder="Monto" value="{{old('loan')}}" required>
          </div>
        </div>
      </div>{{-- form row --}}
    </div>{{-- col-6 --}}
    <div class="col-md-6">
      <label class="page-header text-center">Monto a Pagar</label>
      <div class="form-row justify-content-center">
        <div class="col-9 big-input">
          <div class="input-group">
            <span class="input-group-addon">₡</span>
            <input id="input_balance_amount" type="text" pattern="\d*" class="form-control amount-control py-3" placeholder="Saldo" value="{{old('balance')}}" required>
          </div>          
        </div>
      </div>{{-- form row --}}
    </div>{{-- col-6 --}}
  </div>{{-- row --}}

  <div class="row bg-dark py-3 my-1">

    <div class="col-xs-12 col-md-4 text-center">
      <h4 class="m-0 pt-2 text-light">
        <span id="label_interests" class="font-weight-bold">0</span>% de interés
      </h4>
    </div><!-- /.col-md-6 -->


    <div class="col-xs-12 col-md-3 my-4 my-sm-0">
      <div class="row justify-content-center">
        <div class="col-3 col-md-6">
          <input id="input_duration" type="number" class="form-control text-center m-0" name="duration" value="3" min="1" max="99" required>
        </div>
      </div>      
    </div>

    <div class="col-md-5">
      <div class="row justify-content-center">
        <div class="col-xs-12 pt-2">
          <div class="form-check form-check-inline m-0">
            <label class="custom-control custom-radio">
              <input class="custom-control-input" type="radio" name="type" onchange="onPayPlanChange('we')" value="we" checked>
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description text-light">Semanas</span>
            </label>
          </div>
          <div class="form-check form-check-inline m-0">
            <label class="custom-control custom-radio">
              <input class="custom-control-input" type="radio" name="type" onchange="onPayPlanChange('bw')" value="bw">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description text-light">Quincenas</span>
            </label>
          </div>
          <div class="form-check form-check-inline m-0">
            <label class="custom-control custom-radio">
              <input class="custom-control-input" type="radio" name="type" onchange="onPayPlanChange('mo')" value="mo">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description text-light">Meses</span>
            </label>
          </div>
        </div>
      </div>
    </div><!-- /.col-md-6 -->
  </div><!-- /.row -->

  <div class="row py-4">
    <div class="col-md-6">
      <label class="page-header text-center">Monto de las Cuotas</label>
      <div class="form-row justify-content-center">
        <div class="col-9 big-input">
          <div class="input-group">
            <span class="input-group-addon">₡</span>
            <input id="input_regdue_amount" type="text" pattern="\d*" class="form-control amount-control py-3" placeholder="Cuotas" required>
          </div>
        </div>
        <label class="text-center">
          <span id="label_regdue" class="d-none">000</span>
        </label>
      </div>{{-- form row --}}
    </div>{{-- col-6 --}}

    <div class="col-md-6 ">
      <label class="page-header text-center">Cuota Minima</label>
      <div class="form-row justify-content-center">
        <div class="col-9 big-input">
          <div class="input-group">
            <span class="input-group-addon">₡</span>
            <input id="input_mindue_amount" type="text" pattern="\d*" class="form-control amount-control py-3" placeholder="Minimo" required>
          </div>          
        </div>
        <label class="text-center">* <span id="label_extmin">Predefinido</span></label>
      </div>{{-- form row --}}
    </div>{{-- col-6 --}}
  </div>{{-- row --}}

  
  <div class="row py-5 bg-dark justify-content-center">
    <div class="col-md-11">
      @if ( !isset( $_GET['auto'] ) )
      <h4 class="text-center text-light pb-3">Agregar el Cliente</h4>
      <hr style="background-color: #fff">
      <br>
      @endif
      <form id="newLoanForm" method="post" action="{{ route('loans.store') }}">
        {{ csrf_field() }}
        <input id="balance" type="hidden" name="balance" value="0">
        <input id="duemod" type="hidden" name="duemod" value="0">
        <input id="duration" type="hidden" name="duration" value="3">
        <input id="intrate" type="hidden" name="intrate" value="0">
        <input id="intval" type="hidden" name="intval" value="0">
        <input id="loaned" type="hidden" name="loaned" value="0">
        <input id="mindue" type="hidden" name="mindue" value="0">
        <input id="payplan" type="hidden" name="payplan" value="we">
        <input id="firdue" type="hidden" name="firdue" value="0">
        <input id="regdue" type="hidden" name="regdue" value="0">

        @if ( isset( $_GET['auto'] ) )
        <input type="hidden" name="first_name" value="0">
        <input type="hidden" name="last_name" value="0">
        <input type="hidden" name="phone" value="{{ $_GET['key'] }}">
        @else
        <div class="form-row">
          <div class="col-6 col-md-4 form-group">
            <input type="text"  name="first_name" class="form-control py-3 {{ $errors->has('first_name') ? 'is-invalid' : '' }}" value="{{ old('first_name') }}" placeholder="Nombre *" required>
            <div class="invalid-feedback {{ $errors->has('first_name') ? 'd-block' : 'd-none' }}">{{ $errors->first('first_name') }}</div>
          </div>
          <div class="col-6 col-md-4 form-group}">
            <input type="text" name="last_name" class="form-control py-3 {{ $errors->has('last_name') ? 'is-invalid' : '' }}" value="{{ old('last_name') }}" placeholder="Apellidos *" required>
            <div class="invalid-feedback {{ $errors->has('last_name') ? 'd-block' : 'd-none' }}">{{ $errors->first('last_name') }}</div>
          </div>
          <div class="col-6 col-md-4 form-group}">
            <input type="number" name="ssn" class="form-control py-3 {{ $errors->has('ssn') ? 'is-invalid' : '' }}" value="{{ old('ssn') }}" placeholder="Cedula">
            <div class="invalid-feedback {{ $errors->has('ssn') ? 'd-block' : 'd-none' }}">{{ $errors->first('ssn') }}</div>
          </div>
          <div class="col-12 col-md-4 form-group">
            <input type="number" name="phone" class="form-control py-3 {{ $errors->has('phone') ? 'is-invalid' : '' }}" value="{{ old('phone') }}" placeholder="Telefono *" required max="99999999">
            <div class="invalid-feedback {{ $errors->has('phone') ? 'd-block' : 'd-none' }}">{{ $errors->first('phone') }}</div>
          </div>
          <div class="col-12 col-md-4 form-group">
            <input type="number" name="phone_home" class="form-control py-3" value="{{ old('phone_home') }}" placeholder="Telefono (casa)" max="99999999">
          </div>
          <div class="col-12 col-md-4 form-group">
            <input type="number" name="phone_work" class="form-control py-3" value="{{ old('phone_work') }}" placeholder="Telefono (trabajo)" max="99999999">
          </div>

          <div class="col-12"><hr style="background-color: #777"></div>

          <div class="col-md-6 form-group mt-2">
            <textarea class="form-control" name="address_home" rows="3" placeholder="Direccion (Casa)"></textarea>
          </div>
          <div class="col-md-6 form-group mt-2">
            <textarea class="form-control" name="address_work" rows="3" placeholder="Direccion (Trabajo)"></textarea>
          </div>
        </div>
        <div class="col-12"><hr style="background-color: #777"></div>
        @endif
        <div class="form-row">



          <div class="col-md-12 form-group mt-2 text-center">
            <a style="cursor: pointer;" onclick="onNextDueClick()" class="text-light">Fecha de Primer Pago:</a>
            <label  id="firdue-date" class="text-light"></label>
          </div>

          <div class="col-md-6 form-group mt-2">
            <label class="px-2 text-light">Zona de Cobro:</label>
            <div class="row justify-content-center mt-2">
              <div class="col-8 col-md-6">
                <select class="form-control custom-select" name="zone_id" required>
                  @foreach ( $zones as $zone)
                  <option value="{{ $zone->id }}">
                    {{ $zone->name }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div>

          <div class="col-md-6 form-group mt-2">
            <label class="px-2 text-light">Hora de Cobro:</label>
            <div class="row justify-content-center mt-2">
              <div class="col-8 col-md-6">
                <select class="form-control custom-select" name="paytime" required>
                  {{ $curh = date('h') }}
                  @for ($i = 1; $i <= 24; $i++)
                  <option value="{{ $i }}" @if ( $i == $curh ) selected @endif>
                    @if ($i <= 12)
                    @if ($i == 12 ) 12 : 00 p.m. @else {{ $i }} : 00 a.m. @endif
                    @else
                    @if ($i-12 == 12 ) 12 : 00 a.m. @else {{ $i-12 }} : 00 p.m. @endif                    
                    @endif
                  </option>
                  @endfor
                </select>
              </div>
            </div>
          </div>
        </div>

        <form id="lf" action="{{ route('loans.update') }}" method="post">
          {{ csrf_field() }}
          <input id="newloandp" class="datepicker d-none" type="date" name="next_due">
        </form>

      </form>
    </div>
  </div>{{-- row --}}

  <div class="row justify-content-center py-5">
    <button type="submit" onclick="onFormSubmit()" class="btn btn-lg btn-outline-danger">Finalizar</button>
  </div>
</div><!-- /.container -->
@endsection

@section('scripts')
<script src="{{ asset('js/picker.js') }}"></script>
<script src="{{ asset('js/picker.date.js') }}"></script>
<script src="{{ asset('js/picker.es_ES.js') }}"></script>
<script type="text/javascript">
  /* Event binding */
  $('#input_loan_amount').on('input', onLoanAmountChange);
  $('#input_balance_amount').on('input', onBalanceAmountChange);
  $('#input_duration').on('input', onDurationChange);
  $('#input_mindue_amount').on('input', onMinimunDueChange);
  $('#input_regdue_amount').on('input', onRegularDueChange);
  /* ------------------------------ Function Definitions ------------------------------ */
  /* Global Scope */
  var $datepicker = null;

  window.onload = function()
  {
    datepicker_init();
    toggle('loader', false );
  };


  function onLoanAmountChange()
  {
   /* Get the value */
   var d = get('input_loan_amount').value;
   /* Check for a valid number */
   var input_loan_amount =  ( d ) ? parseInt(d.replaceAll(',', '')) : 0;
   /* Update the Form */
   get('loaned').value = input_loan_amount;
   /* Update all the labels */
   window.update();
 }

 function onBalanceAmountChange()
 {
   /* Get the value */
   var d = get('input_balance_amount').value;
   /* Check for a valid number */
   let input_balance_amount =  ( d ) ? parseInt(d.replaceAll(',', '')) : 0;
   /* Update the Form */    
   get('balance').value = input_balance_amount;
   /* Update all the labels */
   window.update();
 }

 function onDurationChange()
 {
  /* Get the value */
  var input_duration = get('input_duration').value;

  /* Update the Form */    
  get('duration').value = input_duration;

  /* Update all the labels */
  window.update();
}

function onMinimunDueChange()
{
  let intval = get('intval').value;

  /* Get the value */
  var d = get('input_mindue_amount').value;
  /* Check for a valid number */
  let mindue = ( d ) ? parseInt(d.replaceAll(',', '')) : 0;

  /* Update the Form */
  get('mindue').value = mindue;
  get('duemod').value = mindue - intval;
  
  /* Update the excedent label */
  get('label_extmin').innerHTML = ( "Modificado" );
  get('label_extmin').style.color = ((mindue - intval) < 0) ? 'red' : 'green';
}

function onRegularDueChange()
{
  /* Get the value */
  var d = get('input_regdue_amount').value;
  /* Check for a valid number */
  let regdue = ( d ) ? parseInt(d.replaceAll(',', '')) : 0;

  /* Previous values */
  let olddue = get('regdue').value;
  
  /* User inputs */
  let balance_amount = get('balance').value;
  let duration = get('duration').value;

  /* Calculate the regular dues after the 1st */
  let newdue = ( balance_amount - regdue ) / (duration -1);
  
  /* Update the Form */
  get('regdue').value =  parseInt(newdue);
  get('firdue').value = regdue;
  
  /* Update the excedent label */
  get('label_regdue').innerHTML = ('₡ ' + nicecify( parseInt(newdue) ) );
  get('label_regdue').classList.remove('d-none')
}

function onPayPlanChange( plan )
{
  /* Update the Form */
  get('payplan').value = plan;

  /* Update the options */
  window.update();
}

function update()
{
  /* User inputs */
  let loan_amount = get('loaned').value;
  let balance_amount = get('balance').value;
  let duration = get('duration').value;

  /* Standard payment due + interests */
  var due = 0;

  /* Interest amount ( total value ) */
  let intval = 0;

  /* Percentage (%) of interests */
  let intrate = 0;

  /* Standard payment due. No interests */
  let regdue = 0;

  /* Minimum payment due ( interests + fees ) */
  let mindue = 0;

  if ( loan_amount > 0 && balance_amount > 0 && duration > 0 )
  {
    /* Calc the regular fare due */
    regdue = ( balance_amount / duration ).toFixed(0);

    /* Calc the regular fare due for the int calculation */
    due = ( loan_amount / duration ).toFixed(0);

    /* Calc the interest rate based on the current remaining balance */
    intrate = ( (balance_amount - loan_amount) / loan_amount );

    /* Standard interest calculation method */
    intval = ( due * intrate ).toFixed(0);

    /* Minimun amount to pay equals the interest value */
    mindue = intval;

    /* ---------------------------------- BIWEEKLY RULES ---------------------------------- */
    if( get('payplan').value == 'bw' )
    {
      /* Fixed 20% minimum due */
      let fixmin = loan_amount * 0.2;

      /* Increase the minimun due if necesary */
      if ( mindue < fixmin) mindue = fixmin;
    }

    /* ______________________________ END: BIWEEKLY RULES ______________________________ */

    /* Add up the regular due + the interests */
    // due = parseInt(regdue) + parseInt(intval);
  }

  /* Update the Form */
  get('intrate').value = intrate *100;
  get('intval').value = intval;
  get('mindue').value = mindue;
  get('regdue').value = regdue;

  get('firdue').value = 0;

  /* Update the interests label */
  get('label_interests').innerHTML = ( intrate *100 ).toFixed(0);
  get('label_interests').style.color = (intrate < 0) ? 'red' : 'white';

  /* Update the dues ammount inputs */
  get('input_regdue_amount').value = nicecify( regdue );
  get('input_mindue_amount').value = nicecify( mindue );
  get('label_regdue').classList.add('d-none')
}

function onFormSubmit()
{
  if ( get('loaned').value > 0 ) get('newLoanForm').submit();
  else alert('Monto debe ser Mayor a 0');
}

/* ---------------------------------- DATEPICKER ---------------------------------- */

function datepicker_init()
{
  var options =
  {
    min: new Date(2018,0,1),
    today: false,
    clear: false,
    close: false,
    hiddenName: true,
    onClose: onNextDueSet,
    formatSubmit: 'yyyy-mm-dd'
  };
  $datepicker = $('.datepicker').pickadate( options );
}

function onNextDueClick()
{
  event.stopPropagation();
  var picker = $datepicker.pickadate('picker');
  picker.open();
}
var a;

function onNextDueSet()
{
  var d = document.getElementsByName('next_due')[0].value;
  get('firdue-date').innerHTML = d;
}
/* ______________________________ END: DATEPICKER ______________________________ */

</script>
@endsection
