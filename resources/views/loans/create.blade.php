@extends('layouts.app')

@section('styles')
  <link rel="stylesheet" type="text/css" href="{{ asset('css/nouislider.css') }}">
@endsection

@section('content')
  <div class="container pb-5">
    
    <div class="row">
      <div class="col-12 ">
        <br><h5 class="page-header text-center">1. Definir el monto</h5><br>
        <div class="form-row justify-content-center">
          <div class="col-9 col-md-6 big-input">
            <div class="input-group">
              <span class="input-group-addon">₡</span>
              <input id="input_amount" type="text" pattern="\d*" class="form-control amount-control py-3" placeholder="Monto" value="{{old('loan')}}" required>
            </div>
          </div>
        </div>{{-- form row --}}
      </div>{{-- col-12 --}}
    </div>{{-- row --}}
    
    <hr>
    
    <div class="row">
      <div class="col-12 ">
        <br><h5 class="page-header text-center">2. Escoger la tasa de interés</h5><br>
        <div class="form-row justify-content-center">
          <div class="col-9 col-md-6 py-3">
            <div id="interest_slider"></div>
            <h4 class="text-center pt-4"><span id="label_interests">20</span>% de interés</h4>
            <h5 class="text-center pt-2"><small>₡<span id="label_loan_amount">0</span> total.</small></h5>
          </div>
        </div>{{-- form row --}}
      </div>{{-- col-12 --}}
    </div>{{-- row --}}
    
    <hr>
    
    <div class="row">
      <div class="col-12 ">
        <br><h5 class="page-header text-center">3. Configurar el plan de pago</h5><br>
        
        <div class="form-row justify-content-center">
          <div class="col-9 col-md-6 py-3">
            <div id="duration_slider"></div>
            <h4 class="text-center pt-4">Duracion: <span id="label_duration">1</span> <span id="label_plan" >Semanas</span></h4>
          </div>
        </div>{{-- form row --}}
        
        <div class="form-row justify-content-center">
          <div class="col-9 col-md-6 py-3">
            <div class="card text-center">
              <div class="card-header">Cuotas</div>
              <div class="card-body">
                <h3 class="card-title">₡<span id="dues_amount" >0</span></h3>
                <p class="card-text mb-0">Abono: ₡<span id="dues_deposit">0</span> </p>
                <p class="card-text">Intereses: ₡<span id="dues_minimum">0</span> </p>
              </div>{{-- body --}}
              <div class="card-footer text-center pt-4">
                <div class="form-check form-check-inline">
                  <label class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" name="type" onchange="onPayPlanChange('we')" value="we" checked>
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Semanal</span>
                  </label>
                </div>
                <div class="form-check form-check-inline">
                  <label class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" name="type" onchange="onPayPlanChange('bw')" value="bw">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Quincenal</span>
                  </label>
                </div>
                <div class="form-check form-check-inline">
                  <label class="custom-control custom-radio">
                    <input class="custom-control-input" type="radio" name="type" onchange="onPayPlanChange('mo')" value="mo">
                    <span class="custom-control-indicator"></span>
                    <span class="custom-control-description">Mensual</span>
                  </label>
                </div>
              </div>
            </div> {{-- card --}}
          </div>{{-- col-3 --}}
        </div>{{-- form row --}}
        
      </div>{{-- col-12 --}}
    </div>{{-- row --}}
    
    <br>
    <hr>
    <br>
    
    <div class="row">
      
      <div class="col-md-12">
        @if ( !isset( $_GET['auto'] ) )
          <br><h5 class="page-header text-center">4. Agregar el Cliente</h5><br>
        @endif
        
        <form id="newLoanForm" method="post" action="{{ route('loans.store') }}">
          {{ csrf_field() }}
          <input id="loan" type="hidden" name="loan" value="0">
          <input id="total" type="hidden" name="total" value="0">
          <input id="interest" type="hidden" name="interest" value="20">
          <input id="duration" type="hidden" name="duration" value="6">
          <input id="payplan" type="hidden" name="payplan" value="we">
          <input id="details" type="hidden" name="details" value="1">
          <input id="dues" type="hidden" name="dues" value="0">
          <input id="partial" type="hidden" name="partial" value="0">
          
          
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
            </div>
          @endif
          
        </div>
        
        <div class="col-12 text-center py-5">
          <button type="submit" onclick="onFormSubmit()"  class="btn btn-lg btn-outline-danger">Finalizar</button>
        </div>
        
      </form>
    </div>{{-- row --}}
    
  </div><!-- /.container -->
@endsection

@section('scripts')
  <script type="text/javascript">
  
  // initialize the sliders
  var interest_slider = document.getElementById('interest_slider');
  var duration_slider = document.getElementById('duration_slider');
  
  noUiSlider.create(interest_slider, {start: 20, step: 10, range: { 'min': 0, 'max': 100 } });
  noUiSlider.create(duration_slider, {start: 6, step: 1, range: { 'min': 1, 'max': 12 } });
  
  // Event binding
  interest_slider.noUiSlider.on('update', onInterestSliderUpdate);
  duration_slider.noUiSlider.on('update', onDurationSliderUpdate);
  
  $('#input_amount').on('input', onAmountValueChange);
  
  
  get('input_amount').value = nicecify( valueOf('input_amount') );
  
  
  /* ------------------------------ Function Definitions ------------------------------ */
  
  function onInterestSliderUpdate(values)
  {
    var int_rate = parseInt( values[0] );
    get('label_interests').innerHTML =  int_rate;
    get('interest').value = int_rate;
    onAmountValueChange();
  }
  
  function onDurationSliderUpdate(values)
  {
    var duration = parseInt( values[0] );
    get('label_duration').innerHTML =  duration;
    // get('wk_label_duration').innerHTML =  duration;
    // get('bw_label_duration').innerHTML =  duration;
    // get('mo_label_duration').innerHTML =  duration;
    get('duration').value = duration;
    
    onPayPlanChange( get('payplan').value );
    
    
  }
  
  function onAmountValueChange()
  {
    var int_rate= get('interest').value;
    
    var input_amount = get('input_amount').value;
    
    // Check for a valid number
    input_amount =  ( input_amount ) ? parseFloat(input_amount.replaceAll(',', '')) : 0;
    
    var total_amount = input_amount + ( ( input_amount * int_rate ) / 100 );
    
    // Update the Form
    get('loan').value = input_amount;
    get('total').value = total_amount;
    
    // Update the options
    window.updatePayPlanOptions()
    
    if (input_amount > 0)
    {
      get('label_loan_amount').innerHTML = nicecify( total_amount );
    }
    else
    {
      get('label_loan_amount').innerHTML = 0;
    }
    
  }
  
  
  function onPayPlanChange( plan )
  {
    // get('payplan-we').classList.remove('active');
    // get('payplan-bw').classList.remove('active');
    // get('payplan-mo').classList.remove('active');
    
    // var active = get( 'payplan-' + plan ).classList.add( 'active' );
    // var duration = get('duration').value;
    switch ( plan)
    {
      case 'we': get('label_plan').innerHTML = 'Semanas'; break;
      case 'bw': get('label_plan').innerHTML = 'Quincenas'; break;
      case 'mo': get('label_plan').innerHTML = 'Meses'; break;
    }
    // Set the value to the input
    get('payplan').value = plan;
    
    // Update the options
    window.updatePayPlanOptions()
  }
  
  function updatePayPlanOptions()
  {
    // Amount to finance:
    var loan_amount = get('loan').value;
    var total_amount = get('total').value;
    var interest_rate= get('interest').value;
    
    // Can be either weeks, fortnights or months
    var duration = get('duration').value;
    
    // Number of payments to do:
    var dues_amount = loan_amount / duration;
    var dues_interest = ( dues_amount * interest_rate ) / 100;
    var total_dues = dues_amount + dues_interest;
    
    get('dues_amount').innerHTML = nicecify( parseInt( total_dues ) );
    get('dues_deposit').innerHTML = nicecify( parseInt( dues_amount ) );
    get('dues_minimum').innerHTML = nicecify( parseInt( dues_interest ) );
    
    
    get('dues').value = total_dues;
    get('partial').value = dues_interest;
  }
  
  
  function updatePayPlanDetails( plan )
  {
    
    var payday = get( plan + '_payday').value;
    get('details').value = payday;
  }
  
  function onFormSubmit()
  {
    if ( get('loan').value > 0 )
    {
      get('newLoanForm').submit();
    }
    else
    {
      alert('Monto debe ser Mayor a 0');
    }
  }
  
  
  
  
  </script>
@endsection
