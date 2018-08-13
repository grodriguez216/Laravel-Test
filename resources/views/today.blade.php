@extends('layouts.app')

@php
use App\Helper;
@endphp

@section('styles')

@endsection

@section('content')

<div class="container-fluid pt-3">

  <div class="row">
    @if ($loans->isNotEmpty())

    @foreach ($zones->sortBy('name') as $zone)
    @if ($zone->loans->isNotEmpty())
    <div class="col-12 bg-white border-navy border py-4 mb-3 border border-dark sticky-top">
      <h3 class="text-center text-navy m-0">{{ $zone->name }}</h3>
    </div>
    @foreach ($zone->loans->sortByDesc('delays') as $loan)
    @if ( $loan->client->zone_id == $zone->id )
    <div class="col-12 col-md-6">
      <div style="cursor:pointer" class="card w-100 mb-3" onclick="redir({{ $loan->client->id }})">

        @php
        $bg_color = 'bg-dark';
        $tx_color = 'text-light';
        if( $loan->delays > 1)
        {
          $bg_color = 'bg-warning';
          $tx_color = 'text-dark';
        }
        if( $loan->delays > 4)
        {
          $bg_color = 'bg-danger';
          $tx_color = 'text-light';
        }

        $plan;
        switch ($loan->payplan)
        {
          case 'we': $plan = 'Sem'; break;
          case 'bw': $plan = 'Quin'; break;
          case 'mo': $plan = 'Meses'; break;
        }
        @endphp

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
    @endif
    @endforeach
    @endif
    @endforeach

    {{-- @if ( $other->isNotEmpty() ) --}}

    <div class="col-12 bg-white border-navy border py-4 mb-3 border border-dark sticky-top">
      <h3 class="text-center text-navy m-0">Asignaciones Manuales</h3>
    </div>

    @foreach ($other->sortByDesc('delays') as $loan)

    <div class="col-12 col-md-6">
      <div style="cursor:pointer" class="card w-100 mb-3" onclick="redir({{ $loan->client->id }})">

        @php
        $bg_color = 'bg-dark';
        $tx_color = 'text-light';
        if( $loan->delays > 1)
        {
          $bg_color = 'bg-warning';
          $tx_color = 'text-dark';
        }
        if( $loan->delays > 4)
        {
          $bg_color = 'bg-danger';
          $tx_color = 'text-light';
        }

        $plan;
        switch ($loan->payplan)
        {
          case 'we': $plan = 'Sem'; break;
          case 'bw': $plan = 'Quin'; break;
          case 'mo': $plan = 'Meses'; break;
        }
        @endphp

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

    @endforeach

    {{-- @endif --}}


    @else
    <div class="col-12 text-center">
      <h1 class="pt-5 mt-5">No hay cobros para hoy.</h1>  
    </div>
    @endif
    

    <div class="col-12 text-center mb-3">
      <hr>
      <a href="{{ route('loans.today_print') }}" target="_blank" class="btn btn-lg px-5 my-3 btn-dark">
        Imprimir
      </a>
    </div>


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
