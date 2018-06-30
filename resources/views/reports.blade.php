@extends('layouts.app')

@php
use App\Helper;
use App\User;
use App\Models\Loan;
@endphp

@section('styles')

@endsection

@section('content')

<div class="container pt-3">

  <div class="row justify-content-center">

    <div class="col-12 text-center py-3">
      <h3 class="page-title"> Reporte de Actividad Diaria (Sólo hoy)</h3>
    </div>
    @if ($newLoans->count() > 0)
    <div class="col-12 bg-white border-navy border py-3 mb-2 border border-dark sticky-top">
      <h4 class="text-center text-navy m-0"> Nuevos Prestamos </h4>
    </div>
    <table class="table ta table-hover">
      <thead class="bg-light text-navy">
        <tr>
          <th>Cliente</th>
          <th>Monto</th>
          <th>Plazo</th>
          <th>Cuotas</th>
          <th>Interés.</th>
          <th>Prox. Pago</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($newLoans as $loan)
        @php
        $ptext;
        switch ($loan->payplan)
        {
          case 'we': $ptext = "S"; break;
          case 'bw': $ptext = "Q"; break;
          case 'mo': $ptext = "M"; break;
        }
        @endphp
        <tr>
          <td>{{$loan->client_id}}</td>
          <td><span class="money">{{$loan->loaned}}</span></td>
          <td>{{$loan->duration}} {{$ptext}}</td>
          <td><span class="money">{{$loan->regdue}}</span></td>
          <td>{{$loan->intrate}} %</td>
          <td>{{$loan->next_due}}</td>  
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="col-12 text-center">
      <strong>Total Prestado: </strong><span class="money">{{ $newLoans->sum('loaned') }}</span>  
      <hr>
    </div>
    @endif

    @if ($earnings->count() > 0)
    <div class="col-12 bg-white border-navy border py-3 mb-2 border border-dark sticky-top">
      <h4 class="text-center text-navy m-0"> Intereses Ganados </h4>
    </div>
    <table class="table ta table-hover">
      <thead class="bg-light text-navy">
        <tr>
          <th>Préstamo</th>
          <th>Monto</th>
          <th>Agente</th>
          <th>Cuota Agente</th>
          <th>Ganancia Neta</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($earnings as $payment)
        <tr>
          <td><a href="/clientes/perfil/{{Loan::find($payment->loan_id)->client_id}}">{{$payment->loan_id}}</a></td>
          <td><span class="money">{{$payment->amount}}</span></td>
          <td>{{ User::find($payment->user_id)->name }}</td>
          <td><span class="money">{{$payment->fee}}</span></td>
          <td><span class="money">{{$payment->amount -$payment->fee}}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="col-6 text-center">
      <strong>Total Cobrado: </strong><span class="money">{{ $earnings->sum('amount') }}</span>  
    </div>
    <div class="col-6 text-center">
      <strong>Ganancia Real: </strong><span class="money">{{ $earnings->sum('amount') - $earnings->sum('fee') }}</span>  
    </div>
    <div class="col-12">
      <hr>
    </div>
    @endif


    @if ($earnings->count() > 0)
    <div class="col-12 bg-white border-navy border py-3 mb-2 border border-dark sticky-top">
      <h4 class="text-center text-navy m-0"> Dinero Recuperado </h4>
    </div>
    <table class="table ta table-hover">
      <thead class="bg-light text-navy">
        <tr>
          <th>Préstamo</th>
          <th>Saldo Pendiente</th>
          <th>Monto Recuperado</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($reimbursements as $payment)
        <tr>
          <td>{{$payment->loan_id}}</td>
          <td><span class="money">{{$payment->balance}}</span></td>
          <td><span class="money">{{$payment->amount}}</span></td>
        </tr>
        @endforeach
      </tbody>
    </table>
    <div class="col-12 text-center">
      <strong>Total Recuperado: </strong><span class="money">{{ $reimbursements->sum('amount') }}</span>  
      <hr>
    </div>
    @endif


    <div class="col-12 text-center py-3">
      {{-- <a  class="btn btn-outline-info" href="/files/reporte_completo.csv">Descargar</a> --}}
      <h1 class="display-4 text-navy my-5" >En progreso...</h1>
    </div>

  </div> {{-- row --}}

</div> {{-- conntainer --}}

@endsection

@section('scripts')
<script type="text/javascript">
  window.onload = function()
  {
    window.nicecify_money();
    toggle('loader', false );
  };
  
  function nicecify_money()
  {
    var items = document.getElementsByClassName('money');
    for (var i = 0; i < items.length; i++)
      items[i].innerHTML = nicecify( items[i].innerHTML );
  }
  
  function redir(id)
  {
    toggle('loader', true );
    window.location = '/clientes/perfil/' + id;
  }
  
</script>

@endsection
