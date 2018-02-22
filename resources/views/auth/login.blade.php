@extends('layouts.login')

@section('styles')

<style type="text/css">
body
{
  height: 100vh;
}
</style>

@endsection


@section('content')

<div class="container-fluid h-100 bg-dark ">

  <div class="row align-items-center justify-content-center h-75">


    <div class="col-8 col-md-6 col-lg-4">

      <form class="form-signin" method="POST" action="{{ route('login') }}">

        {{ csrf_field() }}
        <h2 class="display-4 text-light text-center">Iniciar Sesión</h2>

        <div class="form-group">
          <input id="email" type="number" class="form-control py-3" name="email"
          value="{{ old('email') }}" placeholder="Usuario" required autofocus>
          @if ($errors->has('email'))
          <span class="help-block">
            <strong>Usuario Incorrecto</strong>
          </span>
          @endif
        </div>
        
        <div class="form-group">
         <input id="password" type="password" class="form-control py-3" name="password"
         placeholder="Contraseña" required>
         @if ($errors->has('password'))
         <span class="help-block">
          <strong>Contraseña Incorrecta</strong>
        </span>
        @endif
      </div>




      <button class="btn btn-lg btn-outline-light btn-block" type="submit">Ingresar</button>
    </form>


  </div>
</div>  
</div> <!-- /container -->

@endsection
