@extends('layouts.app')

@section('content')
  
  <div class="container">
    
    <div class="row">
      <div class="col-md-12">
        <form class="form-inline justify-content-center" method="get" action="{{ route('clients.profile') }}">
          <input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0 w-100" id="client_name" name="name" placeholder="Buscar clientes por nombre" required>
          <input type="hidden" id="client_id" name="id" value="0">
          <button type="submit" class="btn btn-outline-danger">Ver Perfil</button>
        </form>
      </div>
    </div>
    
    <hr>
    
    <div class="card-deck card-menu mb-4">
      <div class="card bg-danger mb-3">
        <a class="card-link" href="{{ route('loans.create') }}">
          <div class="card-body text-light">
            <h4 class="card-title"><i class="fa fa-credit-card"></i>&nbsp;Nuevo Prestamo</h4>
            <p class="card-text">Crear un prestamo y nuevo cliente</p>
          </div>
        </a>
      </div>
      
      
      <div class="card border-danger mb-3">
        <a class="card-link" href="{{ route('loans.today') }}">
          <div class="card-body text-danger">
            <h4 class="card-title"><i class="fa fa-list-ol"></i>&nbsp;Cobros del dia</h4>
            <p class="card-text">Prestamos que deben pagar <strong>hoy</strong></p>
          </div>
        </a>
      </div>
    </div>
    
    <div class="card-deck card-menu mb-4">
      <div class="card border-danger mb-3">
        <a class="card-link" href="/reportes/">
          <div class="card-body text-danger">
            <h4 class="card-title"><i class="fa fa-list-alt"></i>&nbsp;Reportes</h4>
            <p class="card-text">Detalle de los prestamos activos</p>
          </div>
        </a>
      </div>
      
      <div class="card border-danger mb-3">
        <a class="card-link" href="{{ route('clients.list') }}">
          <div class="card-body text-danger">
            <h4 class="card-title"><i class="fa fa-address-book-o"></i>&nbsp;Clientes</h4>
            <p class="card-text">Detalle de personas con prestamos</p>
          </div>
        </a>
      </div>
      
      <div class="card border-dark mb-3">
        <a class="card-link" href="/configuracion">
          <div class="card-body text-dark">
            <h4 class="card-title"><i class="fa fa-cog"></i>&nbsp;Configuracion</h4>
            <p class="card-text">Modalidades de prestamo y mas</p>
          </div>
        </a>
      </div>
    </div>
    
  </div><!-- /.row -->
</div><!-- /.container -->
@endsection


@section('scripts')
  <script type="text/javascript">
  var data = { list:
    [
      { "value": 1, "label": "Glynda Ardito"},
      { "value": 2, "label": "Kerri Mroz"},
      { "value": 3, "label": "Collene Bickhamn"},
      { "value": 4, "label": "Staci Fricks"},
      { "value": 5, "label": "Jen Hashimoto"},
      { "value": 6, "label": "Florentina Jepsonion"},
      { "value": 7, "label": "Isaiah Mcfall"},
      { "value": 8, "label": "Shalon Koran"},
      { "value": 9, "label": "Karry Lipman"},
      { "value": 10, "label": "Corrin Slowik"},
      { "value": 11, "label": "Andree Frisby"}
    ]
  };
  
  var input = get("client_name");
  
  var aws = new Awesomplete( input , data);
  
  /* Every time the user inputs a character, set the flag to false */
  input.addEventListener("input", function()
  { get('client_id').value = 0; });
  
  /* When the user clicks a suggestion, set the flag to the value */
  input.addEventListener("awesomplete-select", function(selection)
  { get('client_id').value = selection.text.value; });
  
  </script>
@endsection
