@extends('layouts.login')

@section('content')

@php
$admin = isset($c) ? false : true;
@endphp

<div class="container">

  <form class="form-signin" method="POST" action="{{ $admin ? route('login') : route('login2')}}">
    {{ csrf_field() }}
    <h2 class="form-signin-heading">Ingresar | {{ $admin ? 'Admin' : 'Cobrador' }}</h2>
    <input id="email" type="number" class="form-control" name="email" value="{{ old('email') }}" placeholder="Usuario" required autofocus>
    @if ($errors->has('email'))
    <span class="help-block">
      <strong>Usuario Incorrecto</strong>
    </span>
    @endif

    <input id="password" type="password" class="form-control" name="password" placeholder="Contraseña" required>

    @if ($errors->has('password'))
    <span class="help-block">
      <strong>Contraseña Incorrecta</strong>
    </span>
    @endif
    <button class="btn btn-lg btn-danger btn-block" type="submit">Ingresar</button>
  </form>
  
{{--   <div class="row">
    <div class="col-12 text-center py-3">
      @if ( $admin )
      <strong>
        <a href="/cobrar" class="text-danger">Soy un cobrador</a>  
      </strong>
      @else
      <a href="/login" class="text-danger">volver</a>  
      @endif
    </div>
  </div> --}}


</div> <!-- /container -->

@endsection
