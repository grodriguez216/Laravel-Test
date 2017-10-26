@extends('layouts.app')

@section('content')
  
  <div class="container">
    
    <div class="row">
      <div class="col-md-12">
        <input type="text" class="form-control form-control-lg my-2 mr-sm-2 mb-sm-0 w-100" id="client_name" name="name" placeholder="Buscar clientes por nombre o numero de telefono" required>
        <input type="hidden" id="client_id" name="id" value="0">
      </div>
    </div>
    
    <hr class="my-5">
    
    <div class="card-deck card-menu mb-4">
      <div class="card bg-danger mb-3">
        <a class="card-link" href="{{ route('loans.create') }}">
          <div class="card-body text-light">
            <h4 class="card-title"><i class="fa fa-credit-card"></i>&nbsp;Nuevo Prestamo</h4>
            <p class="card-text">Crear un prestamo para un cliente nuevo o existente</p>
          </div>
        </a>
      </div>
      
      
      <div class="card border-danger mb-3">
        <a class="card-link" href="{{ route('loans.today') }}">
          <div class="card-body text-danger">
            <h4 class="card-title"><i class="fa fa-list-ol"></i>&nbsp;Cobros del dia</h4>
            <p class="card-text">Prestamos que deben pagar la cuota <strong>hoy</strong></p>
          </div>
        </a>
      </div>
    </div>
    
    <div class="card-deck card-menu mb-4">
      <div class="card border-danger mb-3">
        <a class="card-link" href="{{ route('app.reports') }}">
          <div class="card-body text-danger">
            <h4 class="card-title"><i class="fa fa-list-alt"></i>&nbsp;Reportes</h4>
            <p class="card-text">Total de los prestamos activos.</p>
          </div>
        </a>
      </div>
      
      <div class="card border-dark mb-3">
        <a class="card-link" href="{{ route('app.settings') }}">
          <div class="card-body text-dark">
            <h4 class="card-title"><i class="fa fa-cog"></i>&nbsp;Configuracion</h4>
            <p class="card-text">Mensages de Texto</p>
          </div>
        </a>
      </div>
    </div>
    
  </div><!-- /.row -->
</div><!-- /.container -->
@endsection

@section('scripts')
  <script type="text/javascript">
  
  var data = { list: @php echo $client_list; @endphp };
  
  var input = get("client_name");
  
  var aws = new Awesomplete( input , data);
  
  /* Every time the user inputs a character, set the flag to false */
  //input.addEventListener("input", function(){});
  
  /* When the user clicks a suggestion, set the flag to the value */
  input.addEventListener("awesomplete-select", function(selection)
  {
    console.log( selection.text.value );
    window.location.href = "/clientes/perfil/" + selection.text.value;
  });
  
  </script>
  @endsection
