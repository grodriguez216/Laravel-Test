@extends('layouts.app')

@php
use App\Helper;
@endphp

@section('styles')

@endsection

@section('content')

<div class="container-fluid">

  <div class="row justify-content-center">
    <div class="col-12 text-center mb-3">
      <h1 class="page-title">Cobros para hoy <small><strong>{{ date('d-m-Y') }}</strong></small></h1>
      <hr>
    </div>

    @if ( count($loans) > 0 )
    @foreach ($loans as $loan)
    <div class="card-deck col-12">
      <div class="col-6 col-xl-4">
        <div style="cursor:pointer" class="card mb-3" onclick="redir({{ $loan->id }})">
          <div class="card-header text-white bg-danger">
            <h4 class="card-title mb-0">
              <div class="row">
                <div class="col text-left">{{ $loan->first_name }} {{ $loan->last_name }}</div>
                <div class="col text-right">
                  <small>
                    <i class="fa fa-phone"></i>&nbsp;
                    <a href="tel:{{ $loan->phone}}">{{ $loan->phone }}</a> {{-- Phone --}}
                  </small>
                </div> {{-- col --}}
              </div> {{-- col --}}
            </h4> {{-- card-title --}}
          </div> {{-- header --}}
          <div class="card-body">
            {{ $loan->address_home }}
            <hr>
            {{ $loan->address_work }}
          </div> {{-- body --}}

          <div class="card-footer">
            <div class="row text-center">
              <div class="col">
                <small><strong>Cuota:&nbsp;</strong></small>
                <small>₡<span class="money">{{ $loan->dues }}</span></small>
              </div>

              <div class="col">
                <small><strong>Minimo:&nbsp;</strong></small>
                <small>₡<span class="money">{{ $loan->interest }}</span></small>
              </div>

              <div class="col">
                <small><strong>Saldo:&nbsp;</strong></small>
                <small>₡<span class="money">{{ $loan->balance }}</span></small>
              </div>
            </div>
          </div> {{-- footer --}}
        </div> {{-- card --}}
      </div>
    </div> {{-- deck --}}

    @endforeach
    @else
    <h1 class="text-center pt-5 mt-5">No hay cobros para hoy.</h1>
    @endif
  </div>
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
