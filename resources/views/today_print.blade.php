@extends('layouts.min')

@php
use App\Helper;
@endphp

@section('styles')

<style type="text/css">
</style>

@endsection

@section('content')

<div class="container pt-3">

  <div class="row">
    @if ($loans->isNotEmpty())

    @foreach ($zones->sortBy('name') as $zone)
    @if ($zone->loans->isNotEmpty())
    <div class="col-12 py-2 mb-3 border border-dark">
      <h3 class="text-center text-dark m-0">{{ $zone->name }}</h3>
    </div>
    <table class="table table-striped table-bordered">
      <thead>
        <th>Cliente</th>
        <th class="text-right">Cuota</th>
        <th class="text-right">Minimo</th>
        <th class="text-right">Saldo</th>
        <th class="text-center">Pendiente</th>
      </thead>
      <tbody>
        @foreach ($zone->loans->sortByDesc('delays') as $loan)
        @if ( $loan->client->zone_id == $zone->id )
        <tr>
          @php

          $tx_color = 'text-dark';
          if( $loan->delays > 1)
          {
            $tx_color = 'text-warning';
          }
          if( $loan->delays > 4)
          {
            $bg_color = 'bg-danger';
            $tx_color = 'text-danger';
          }
          $plan;
          switch ($loan->payplan)
          {
            case 'we': $plan = 'Sem'; break;
            case 'bw': $plan = 'Quin'; break;
            case 'mo': $plan = 'Meses'; break;
          }
          @endphp

          <td>{{ ucwords($loan->client->first_name) }} {{ ucwords($loan->client->last_name) }}</td>
          
          <td class="text-right">
            <span class="money">{{ $loan->regdue }}</span>
          </td>
          
          <td class="text-right">
            <span class="money">{{ $loan->mindue }}</span>
          </td>

          <td class="text-right">
            <span class="money">{{ $loan->balance }}</span>
          </td>

          <td class="{{ $tx_color }} text-center">
            @if ($loan->delays > 1)
            {{ $loan->delays }} {{ $plan }}
            @else
            {{ "Hoy" }}
            @endif
          </td>

          <div class="col-12 col-md-6 d-none">
            <div class="card w-100 mb-3" >
              <div class="card-header text-white {{ $bg_color }}">

                <h5 class="card-title mb-0 {{$tx_color}}">
                  {{ ucwords($loan->client->first_name) }} {{ ucwords($loan->client->last_name) }}
                </h5> {{-- card-title --}}

              </div> {{-- header --}}
              <div class="card-body py-1">
                <div class="row text-center">

                  <div class="col">
                    <small><strong>Cuota:&nbsp;</strong></small>
                    <p>
                      @if ( $loan->firdue )
                      ₡<span class="money">{{ $loan->firdue }}</span>
                      @else
                      ₡<span class="money">{{ $loan->regdue }}</span>
                      @endif
                    </p>
                  </div>

                  <div class="col">
                    <small><strong>Minimo:&nbsp;</strong></small>
                    <p>₡<span class="money">{{ $loan->mindue }}</span></p>
                  </div>

                  <div class="col">
                    <small><strong>Saldo:&nbsp;</strong></small>
                    <p>₡<span class="money">{{ $loan->balance }}</span></p>
                  </div>

                  <div class="col">
                    <small><strong>Pendiente:&nbsp;</strong></small>
                    @if ($loan->delays > 1)
                    <p>{{ $loan->delays }} {{ $plan }}  </p>
                    @else
                    <p>{{ "Hoy" }}</p>
                    @endif
                  </div>


                </div>
              </div> {{-- footer --}}
            </div>
          </div>
        </tr>
        @endif
        @endforeach
      </tbody>
    </table>
    @endif
    @endforeach


    @else
    <div class="col-12 text-center">
      <h1 class="pt-5 mt-5">No hay cobros para hoy.</h1>  
    </div>
    @endif
  </div> {{-- row --}}
</div>
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
