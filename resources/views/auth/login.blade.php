@extends('layouts.login')

@section('content')
  
  <div class="container">
    
    <form class="form-signin" method="POST" action="{{ route('login') }}">
      {{ csrf_field() }}
      <h2 class="form-signin-heading">Ingresar</h2>
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
      
      
      <div class="checkbox">
        <label>
          <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Mantener activa
        </label>
      </div>
      <button class="btn btn-lg btn-danger btn-block" type="submit">Ingresar</button>
    </form>
  </div> <!-- /container -->
  
@endsection
