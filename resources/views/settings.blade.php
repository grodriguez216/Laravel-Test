@extends('layouts.app')

@php
use App\Helper;
@endphp


@php
$notification_nl = $notifications->where('type', 'NL')->first();
$notification_sp = $notifications->where('type', 'SP')->first();
$notification_pr = $notifications->where('type', 'PR')->first();
$notification_cl = $notifications->where('type', 'CL')->first();
@endphp

@section('content')
  
  <div class="container-fluid pt-3">
    
    <div class="card-deck pb-4">
      
      <div class="card">
        <div class="card-body">
          <form action="{{ route('messages.update') }}" method="post">
            <h4 class="card-title">Prestamo Nuevo</h4>
            {{ csrf_field() }}
            <input type="hidden" name="notification" value="{{ $notification_nl->id }}">
            <textarea id="message_nl" class="form-control" name="message" rows="4">{{ $notification_nl->message }}</textarea>
            <div class="row">
              <div class="col-9 py-2">
                <button type="button" onclick="addText('message_nl','4')" class="btn btn-secondary px-4">Monto</button>
                <button type="button" onclick="addText('message_nl','2')" class="btn btn-secondary px-4">Saldo</button>
                <button type="button" onclick="addText('message_nl','3')" class="btn btn-secondary px-4">Fecha</button>
              </div> {{-- col-9 --}}
              <div class="col-3 py-2">
                <button type="submit" class="btn btn-outline-danger">Guardar</button>
              </div> {{-- col-3 --}}
            </div> {{-- row --}}
          </form> {{-- form --}}
        </div> {{-- body --}}
      </div> {{-- card --}}
      
      <div class="card">
        <div class="card-body">
          <form action="{{ route('messages.update') }}" method="post">
            <h4 class="card-title">Pagos y Abonos </h4>
            {{ csrf_field() }}
            <input type="hidden" name="notification" value="{{ $notification_sp->id }}">
            <textarea id="message_sp" class="form-control" name="message" rows="4">{{ $notification_sp->message }}</textarea>
            <div class="row">
              <div class="col-9 py-2">
                <button type="button" onclick="addText('message_sp','1')" class="btn btn-secondary px-4">Cuota</button>
                <button type="button" onclick="addText('message_sp','2')" class="btn btn-secondary px-4">Saldo</button>
                <button type="button" onclick="addText('message_sp','3')" class="btn btn-secondary px-4">Fecha</button>
              </div> {{-- col-9 --}}
              <div class="col-3 py-2">
                <button type="submit" class="btn btn-outline-danger">Guardar</button>
              </div> {{-- col-3 --}}
            </div> {{-- row --}}
          </form> {{-- form --}}
        </div> {{-- body --}}
      </div> {{-- card --}}
    </div> {{-- deck --}}
    
    <div class="card-deck">
      
      <div class="card">
        <div class="card-body">
          <form action="{{ route('messages.update') }}" method="post">
            <h4 class="card-title">Prestamo Completo</h4>
            {{ csrf_field() }}
            <input type="hidden" name="notification" value="{{ $notification_cl->id }}">
            <textarea id="message_cl" class="form-control" name="message" rows="4">{{ $notification_cl->message }}</textarea>
            <div class="row">
              <div class="col-9 py-2">
                <button type="button" onclick="addText('message_cl','4')" class="btn btn-secondary px-4">Monto</button>
              </div> {{-- col-9 --}}
              <div class="col-3 py-2">
                <button type="submit" class="btn btn-outline-danger">Guardar</button>
              </div> {{-- col-3 --}}
            </div> {{-- row --}}
          </form> {{-- form --}}
        </div> {{-- body --}}
      </div> {{-- card --}}
      
      <div class="card">
        <div class="card-body">
          <form action="{{ route('messages.update') }}" method="post">
            <h4 class="card-title">Recordatorio</h4>
            {{ csrf_field() }}
            <input type="hidden" name="notification" value="{{ $notification_pr->id }}">
            <textarea id="message_pr" class="form-control" name="message" rows="4">{{ $notification_pr->message }}</textarea>
            <div class="row">
              <div class="col-9 py-2">
                <button type="button" onclick="addText('message_pr','1')" class="btn btn-secondary px-4">Cuota</button>
                <button type="button" onclick="addText('message_pr','2')" class="btn btn-secondary px-4">Saldo</button>
                <button type="button" onclick="addText('message_pr','3')" class="btn btn-secondary px-4">Fecha</button>
              </div> {{-- col-9 --}}
              <div class="col-3 py-2">
                <button type="submit" class="btn btn-outline-danger">Guardar</button>
              </div> {{-- col-3 --}}
            </div> {{-- row --}}
          </form> {{-- form --}}
        </div> {{-- body --}}
      </div> {{-- card --}}
    </div> {{-- deck --}}
    
  </div> {{-- container --}}
@endsection

@section('scripts')
  <script type="text/javascript">
  
  function addText( textarea, textid )
  {
    var item = get(textarea);
    var string = "";
    switch ( textid)
    {
      case '1': string += " [Cuota]"; break;
      case '2': string += " [Saldo]"; break;
      case '3': string += " [Fecha]"; break;
      case '4': string += " [Monto]"; break;
    }
    
    item.value += string;
  }
  
  </script>
@endsection
