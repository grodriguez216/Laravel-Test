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
@endsection

@section('content')
<div class="container py-3">
  <div class="row">
    <div class="col-md-6 pb-3">
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
    <div class="col-md-6 pb-3">
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
  
  <hr>

  <div class="row">
    <div class="col-md-6 text-center">
      <h4 class="m-0 pt-2">
        <span id="label_interests" class="font-weight-bold">0</span>% de interés
      </h4>
    </div><!-- /.col-md-6 -->

    <div class="col-md-6">
      <div class="row">
        <div class="col-3 col-md-2">
          <input id="input_duration" type="numer" class="form-control text-center m-0" name="duration" value="5" min="1" max="99" required>
        </div>
        <div class="col pt-2">
          <div class="form-check form-check-inline m-0">
            <label class="custom-control custom-radio">
              <input class="custom-control-input" type="radio" name="type" onchange="onPayPlanChange('we')" value="we" checked>
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Semanas</span>
            </label>
          </div>
          <div class="form-check form-check-inline m-0">
            <label class="custom-control custom-radio">
              <input class="custom-control-input" type="radio" name="type" onchange="onPayPlanChange('bw')" value="bw">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Quincenas</span>
            </label>
          </div>
          <div class="form-check form-check-inline m-0">
            <label class="custom-control custom-radio">
              <input class="custom-control-input" type="radio" name="type" onchange="onPayPlanChange('mo')" value="mo">
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description">Meses</span>
            </label>
          </div>
        </div>
      </div>
    </div><!-- /.col-md-6 -->
  </div><!-- /.row -->

  <hr>

  <div class="row pt-5">
    <div class="col-md-6">
      <label class="page-header text-center">Monto de las Cuotas</label>
      <div class="form-row justify-content-center">
        <div class="col-9 big-input">
          <div class="input-group">
            <span class="input-group-addon">₡</span>
            <input id="input_regdue_amount" style="background: transparent !important;" type="text" class="form-control amount-control py-3" placeholder="Cuotas" readonly>
          </div>
        </div>
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
        <label>+ <span id="label_extmin">0</span></label>
      </div>{{-- form row --}}
    </div>{{-- col-6 --}}
  </div>{{-- row --}}

  <hr>

  <div class="row mt-4 pb-3">
    <div class="col-md-12">
      @if ( !isset( $_GET['auto'] ) )
      <label class="page-header pb-4">Agregar el Cliente</label>
      @endif
      <form id="newLoanForm" method="post" action="{{ route('loans.store') }}">
        {{ csrf_field() }}
        <input id="loaned" type="hidden" name="loaned" value="0">
        <input id="balance" type="hidden" name="balance" value="0">
        <input id="intrate" type="hidden" name="intrate" value="0">
        <input id="intval" type="hidden" name="intval" value="0">
        <input id="duration" type="hidden" name="duration" value="5">
        <input id="payplan" type="hidden" name="payplan" value="we">
        <input id="regdue" type="hidden" name="regdue" value="0">
        <input id="mindue" type="hidden" name="mindue" value="0">
        <input id="duemod" type="hidden" name="duemod" value="0">
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
            <input type="number" name="ssn" class="form-control py-3 {{ $errors->has('ssn') ? 'is-invalid' : '' }}" value="{{ old('ssn') }}" placeholder="Cedula *" required>
            <div class="invalid-feedback {{ $errors->has('ssn') ? 'd-block' : 'd-none' }}">{{ $errors->first('ssn') }}</div>
          </div>

          <div class="col-12 col-md-4 form-group">
            <input type="number" name="phone" class="form-control py-3 {{ $errors->has('phone') ? 'is-invalid' : '' }}" value="{{ old('phone') }}" placeholder="Telefono *" required>
            <div class="invalid-feedback {{ $errors->has('phone') ? 'd-block' : 'd-none' }}">{{ $errors->first('phone') }}</div>
          </div>
          <div class="col-12 col-md-4 form-group">
            <input type="number" name="phone_home" class="form-control py-3" value="{{ old('phone_home') }}" placeholder="Telefono (casa)">
          </div>
          <div class="col-12 col-md-4 form-group">
            <input type="number" name="phone_work" class="form-control py-3" value="{{ old('phone_work') }}" placeholder="Telefono (trabajo)">
          </div>

          <div class="col-md-6 form-group mt-3">
            <textarea class="form-control" name="address_home" rows="3" placeholder="Direccion (Casa)"></textarea>
          </div>
          <div class="col-md-6 form-group mt-3">
            <textarea class="form-control" name="address_work" rows="3" placeholder="Direccion (Trabajo)"></textarea>
          </div>

          <div class="col-md-12 mt-3 text-center">
            <label class="px-2">Zona de Cobro:</label>
            <div class="row justify-content-center">
              <div class="col-8 col-md-4">
                <select class="form-control custom-select" name="zone_id" required>
                  @foreach ( $zones as $zone)
                  <option value="{{ $zone->id }}">
                    {{ $zone->name }}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
          </div><!-- /col-6 -->
        </div>
        @endif
      </form>
    </div>
  </div>{{-- row --}}

  <hr>

  <div class="row justify-content-center py-5">
    <button type="submit" onclick="onFormSubmit()" class="btn btn-lg btn-outline-danger">Finalizar</button>
  </div>
</div><!-- /.container -->
@endsection

@section('scripts')
<script type="text/javascript">
  /* Event binding */
  $('#input_loan_amount').on('input', onLoanAmountChange);
  $('#input_balance_amount').on('input', onBalanceAmountChange);
  $('#input_duration').on('input', onDurationChange);
  $('#input_mindue_amount').on('input', onMinimunDueChange);
  /* ------------------------------ Function Definitions ------------------------------ */

  function onLoanAmountChange()
  {
   /* Get the value */
   var d = get('input_loan_amount').value;
   /* Check for a valid number */
   let input_loan_amount =  ( d ) ? parseFloat(d.replaceAll(',', '')) : 0;
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
   let input_balance_amount =  ( d ) ? parseFloat(d.replaceAll(',', '')) : 0;
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
  let mindue = ( d ) ? parseFloat(d.replaceAll(',', '')) : 0;

  /* Update the Form */
  get('mindue').value = mindue;
  get('duemod').value = mindue - intval;
  
  /* Update the excedent label */
  get('label_extmin').innerHTML = ( mindue - intval );
  get('label_extmin').style.color = ((mindue - intval) < 0) ? 'red' : 'black';
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
  let loan_amount = get('loaned').value;
  let balance_amount = get('balance').value;
  let duration = get('duration').value;

  let regdue = 0;
  let mindue = 0;
  let intval = 0;
  let intrate = 0;

  if ( loan_amount > 0 && balance_amount > 0 && duration > 0)
  {
    /* Calc the interest rate */
    intrate = ( (balance_amount - loan_amount) / loan_amount );
    /* Calc the due and minimin due */
    var normdue = ( loan_amount / duration ).toFixed(0);
    var intdue = ( normdue * intrate ).toFixed(0);
    /* Set the values on display */
    regdue = ( parseInt(normdue) + parseInt(intdue) );
    intval = mindue = intdue;
  }

  /* Update the Form */
  get('intrate').value = intrate *100;
  get('intval').value = intval;
  get('regdue').value = regdue;
  get('mindue').value = mindue;

  /* Update the interests label */
  get('label_interests').innerHTML = ( intrate *100 ).toFixed(0);
  get('label_interests').style.color = (intrate < 0) ? 'red' : 'black';

  /* Update the dues ammount inputs */
  get('input_regdue_amount').value = nicecify( regdue );
  get('input_mindue_amount').value = nicecify( mindue );

  /* Update the excedent minimun due */
  get('label_extmin').innerHTML = 0;
  get('label_extmin').style.color = 'black';

}

function onFormSubmit()
{
  if ( get('loaned').value > 0 ) get('newLoanForm').submit();
  else alert('Monto debe ser Mayor a 0');
}
</script>
@endsection
