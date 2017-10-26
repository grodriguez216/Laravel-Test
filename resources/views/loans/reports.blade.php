@extends('layouts.app')

@php
use App\Helper;
@endphp

@section('styles')
  
@endsection

@section('content')
  
  <div class="container-fluid">
    
    <div class="row justify-content-center">
      
      <div class="col-12 text-center">
        <h3 class="page-title">
          Reporte desde <small><strong>{{ $rep_start }}</strong></small>
          hasta <small><strong>{{ $rep_end }}</strong></small>
        </h3>
        
        <hr>
        
        <div class="card-deck">
          <div class="card text-center mb-1">
            <div class="card-header text-white bg-danger py-1">
              Total General
            </div> {{-- header --}}
            <div class="card-body py-1">
              <table class="table table-sm mb-0 text-left">
                <tr>
                  <th>Prestado</th>
                  <td>₡&nbsp;<span class="money">{{ $total_loaned }}</span></td>
                </tr>
                <tr>
                  <th>Pendiente</th>
                  <td>₡&nbsp;<span class="money">{{ $total_pending }}</span></td>
                </tr>
                <tr>
                  <th>Prestamos</th>
                  <td><span class="money">{{ $active_loans }}</span></td>
                </tr>
              </table>
            </div> {{-- body --}}
            <div class="card-footer py-1">
              <strong>₡&nbsp;<span class="money">{{ $total_loaned }}</span></strong>
            </div> {{-- footer --}}
          </div> {{-- card --}}
          
          
          <div class="card text-center mb-1">
            <div class="card-header text-white bg-danger py-1">
              Total Recibido
            </div> {{-- header --}}
            <div class="card-body py-1">
              <table class="table table-sm mb-0 text-left">
                <tr>
                  <th>Cuotas</th>
                  <td>₡&nbsp;<span class="money">{{ $total_received_pc }}</span></td>
                </tr>
                <tr>
                  <th>Intereses</th>
                  <td>₡&nbsp;<span class="money">{{ $total_received_pm }}</span></td>
                </tr>
                <tr>
                  <th>Abonos</th>
                  <td>₡&nbsp;<span class="money">{{ $total_received_ab }}</span></td>
                </tr>
              </table>
            </div> {{-- body --}}
            <div class="card-footer py-1">
              <strong>₡&nbsp;<span class="money">{{ $total_received }}</span></strong>
            </div> {{-- footer --}}
          </div> {{-- card --}}
          
          
          <div class="card text-center mb-1">
            <div class="card-header text-white bg-danger py-1">
              Ganancia Total
            </div> {{-- header --}}
            <div class="card-body py-1">
              <table class="table table-sm mb-0 text-left">
                <tr>
                  <th>Intereses</th>
                  <td>₡&nbsp;<span class="money">{{ $total_earnings_pc }}</span></td>
                </tr>
                <tr>
                  <th>Extenciones</th>
                  <td>₡&nbsp;<span class="money">{{ $total_earnings_pm }}</span></td>
                </tr>
              </table>
            </div> {{-- body --}}
            <div class="card-footer py-1">
              <strong>₡&nbsp;<span class="money">{{ $total_earnings }}</span></strong>
            </div> {{-- footer --}}
          </div> {{-- card --}}
          
        </div> {{-- deck --}}
        
        <hr>
        
      </div> {{-- col-12 --}}
      
      <div class="col-12">
        @if ($loans->count() > 0)
          <div class="card">
            <div class="card-header">
              Prestamos Activos
            </div>
            <div class="card-body scroll-2">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Cliente</th>
                    <th>Inicio</th>
                    <th>Saldo</th>
                    <th>Cuotas</th>
                    <th>Prox. Pago</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($loans->sortByDesc('created_at') as $loan)
                    <tr>
                      @php $c = $loan->client; @endphp
                      <td><a href="/clientes/perfil/{{$c->id}}">{{ $c->first_name }} {{ $c->last_name }}</a></td>
                      <td>{{ Helper::date($loan->created_at) }}</td>
                      <td>₡&nbsp;<span class="money">{{ $loan->balance }}</span></td>
                      <td>
                        ₡&nbsp;<span class="money">{{ $loan->dues }}</span>
                      </td>
                      <td>{{ Helper::date($loan->next_due) }}</td>
                    </tr>
                  @endforeach
                  
                </tbody>
                
              </table>
            </div>
          </div>
          
        @else
          <h1 class="text-center pt-5 mt-5">No hay prestamos activos.</h1>
        @endif
        
      </div>
      
      <div class="col-12 text-center py-3">
        <a  class="btn btn-outline-danger" href="/files/reporte_completo.csv">Descargar</a>
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
