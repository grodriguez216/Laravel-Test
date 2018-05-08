@extends('layouts.app')

@php
use App\Helper;
@endphp

@section('styles')

@endsection

@section('content')

<div class="container pt-3">

  <div class="row justify-content-end">
    <div class="col-8">
      <h4 class="text-right"><strong>Progreso:</strong>
        <small>₡</small><span class="money">{{$payed}}</span>
        /
        <small>₡</small><span class="money">{{$total}}</span>
      </h4>
    </div>
  </div>

  <div class="row">
    @if ($loans->isNotEmpty())

    @foreach ($zones->sortBy('name') as $zone)

    @if ($zone->loans->isNotEmpty())

    <div class="col-12 mt-3">
      <h4>{{ $zone->name }}</h4>
      <hr>
    </div>
    @foreach ($zone->loans->sortBy('paytime') as $loan)
    @if ( $loan->client->zone_id == $zone->id )
    <div class="col-12 col-md-6">
      <div style="cursor:pointer" class="card w-100 mb-3" onclick="redir({{ $loan->client->id }})">
        <div class="card-header text-white {{ $loan->delays > 1 ? 'bg-danger' : 'bg-dark' }}">
          <h5 class="card-title mb-0">
            {{ $loan->client->first_name }} {{ $loan->client->last_name }}
          </h5> {{-- card-title --}}
        </div> {{-- header --}}
        <div class="card-body text-center">
          <div class="row">
            <div class="col-12 col-md-9">
              @php
              if ( !$loan->address_work ) $loan->address_work = $loan->address_home;
              @endphp
              @if ( strpos($loan->client->address_work, 'maps') )
              <a target="_blank" href="{{ $loan->client->address_work }}">Ver Mapa</a>
              @else
              {{ $loan->client->address_work }}
              @endif
            </div>
            <div class="col-12 col-md-3">
              @php
              $time = $loan->paytime > 12 ? $loan->paytime -12 : $loan->paytime;
              $aa = $loan->paytime >= 12 ? 'PM' : 'AM'; 
              @endphp
              {{ $time . ':00 ' . $aa }}
            </div>
          </div>
        </div> {{-- body --}}
        <div class="card-footer">
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
          </div>
        </div> {{-- footer --}}
      </div>
    </div>

    @endif
    @endforeach
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
