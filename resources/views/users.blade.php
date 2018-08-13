@extends('layouts.app')

@php
use App\Helper;
@endphp

@section('styles')

@endsection

@section('content')

<div class="container pt-5">

  <div class="row">

    <div class="col-12 text-center mb-5">
      <h1 class="page-title">Usuarios Cobradores</h1>
    </div>

    <div class="col-md-12 mb-3">
      @if ($users->isNotEmpty())
      <table class="table">
        <thead class="thead-dark">
          <tr>
            <th scope="col">#</th>
            <th scope="col">Nombre</th>
            <th scope="col">Usuario</th>
            <th scope="col" class="text-center">Opciones</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($users as $user)
          <tr>
            <th scope="row">{{ $user->id }}</th>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td class="text-center">

              <a class="btn btn-outline-success mx-1 px-3" href="#" data-toggle="modal" data-target="#confirModal-{{ $user->id }}">Pagar</a>
              <a class="btn btn-outline-info mx-1 px-3" href="/usuarios/perfil/{{ $user->id }}">Editar Zonas</a>
              <a class="btn btn-outline-danger mx-1" href="/usuarios/bloquear/{{ $user->id }}">Bloquear</a>
              {{-- <a class="btn btn-danger mx-1" href="/usuarios/borrar/{{ $user->id }}">Eliminar</a> --}}
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
      @else
      <h1 class="text-center pt-5 mt-5">No hay usuarios adicionales</h1>
      @endif
    </div> {{-- col-6 --}}

    @foreach ($users as $user)
    <div id="confirModal-{{ $user->id }}" class="modal" tabindex="-1" role="dialog">
      <form action="{{ route('users.pay') }}" method="POST">
        {{ csrf_field() }}
        <input type="hidden" name="id" value="{{ $user->id }}">
        <div class="modal-dialog modal-sm" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title text-center">Confirmar Pago</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body text-center">
              <p>El monto pendiente a pagar es:</p>
              <h3>₡<span class="money">{{ $user->balance }}</span></h3>
            </div>
            <div class="modal-footer">
              <button type="submit" class="btn btn-outline-success px-4">Si, Pagar</button>
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
          </div>
        </div>
      </form>
    </div>
    @endforeach

  </div>

  <hr class="py-3">

  <div class="row">

    @if ( \Auth::user()->is_admin)
    <div class="col-md-12 mb-3">
      <h3 class="text-center pb-3">Nuevo Usuario</h3>
      <form method="POST" action="/usuarios/agregar">
        {{ csrf_field() }}
        <fieldset>
          <div class="form-row  justify-content-center mb-3">
            <div class="col-md-8 pb-3">
              <input type="text" class="form-control" name="name" placeholder="Nombre" required>
            </div>
            <div class="col-md-8 pb-3">
              <input type="number" class="form-control" name="email" placeholder="Telefono (Contraseña)" max="99999999" required>
            </div>
          </div>
        </fieldset>
        <div class="form-row justify-content-center">
          <button type="submit" class="btn btn-outline-danger px-5">Agregar Usuario</button>
        </div>
      </form>
    </div><!-- /.col -->
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

    if( getUrlParameter('ref') == 'error' )
      alert('Teléfono ya está registrado');
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
