@extends('layouts.app')

@section('content')

<div class="container">

  <div class="row justify-content-center my-5">
    <div class="col-12 col-md-8">
      <input type="text" class="form-control form-control-lg my-2 mr-sm-2 mb-sm-0 w-100 py-3 text-center" id="client_name" name="name" placeholder="Buscar clientes por nombre o telefono" style="border-radius: 0" required>
      <input type="hidden" id="client_id" name="id" value="0">
    </div>
  </div>

  <div class="card-deck card-menu">

    <div class="card border-danger mb-3">
      <a class="card-link" href="{{ route('loans.create') }}">
        <div class="card-body text-danger">
          <h4 class="card-title"><i class="fa fa-credit-card"></i>&nbsp;Nuevo Prestamo</h4>
          <p class="card-text">Crear un prestamo para un cliente nuevo o existente</p>
        </div>
      </a>
    </div>

    <div class="card border-dark mb-3">
      <a class="card-link" href="{{ route('loans.today') }}">
        <div class="card-body text-dark">
          <h4 class="card-title"><i class="fa fa-list-ol"></i>&nbsp;Cobros del dia</h4>
          <p class="card-text">Prestamos que deben pagar la cuota <strong>hoy</strong></p>
        </div>
      </a>
    </div>

  </div>{{-- card-deck --}}

  <div class="card-deck card-menu">

    <div class="card border-dark mb-3">
      <a class="card-link" href="/usuarios">
        <div class="card-body text-dark">
          <h4 class="card-title"><i class="fa fa-users"></i>&nbsp;Usuarios</h4>
          <p class="card-text">Crear, borrar y editar cobradores</p>
        </div>
      </a>
    </div>

    <div class="card border-dark mb-3">
      <a class="card-link" href="{{ route('app.zones') }}">
        <div class="card-body text-dark">
          <h4 class="card-title"><i class="fa fa-cog"></i>&nbsp;Zonas</h4>
          <p class="card-text">Mensages de Texto</p>
        </div>
      </a>
    </div>

  </div>{{-- card-deck --}}


  <div class="card-deck card-menu">

    <div class="card border-dark mb-3">
      <a class="card-link" href="{{ route('app.reports') }}">
        <div class="card-body text-dark">
          <h4 class="card-title"><i class="fa fa-list-alt"></i>&nbsp;Reportes</h4>
          <p class="card-text">Total de los prestamos activos.</p>
        </div>
      </a>
    </div>

    <div class="card border-dark mb-3">
      <a class="card-link" href="{{ route('app.settings') }}">
        <div class="card-body text-dark">
          <h4 class="card-title"><i class="fa fa-envelope"></i>&nbsp;Mensajes</h4>
          <p class="card-text">Cambiar los mensages de texto</p>
        </div>
      </a>
    </div>

  </div>

</div><!-- /.row -->
</div><!-- /.container -->
@endsection

@section('scripts')
<script type="text/javascript">

  // var data = { list: @php echo $client_list; @endphp };
  var vdata = @php echo $client_list; @endphp;
  
  var input = get("client_name");
  
  var aws = new Awesomplete( input,
  {
    list: vdata,
    filter: function (text, input)
    {
      var d_name = d_phone = d_ssn = 0;
      var obj = JSON.parse(text.value);

      if( obj.name )
        d_name = obj.name.toString().toLowerCase().indexOf(input.toLowerCase()) !== -1;  

      if( obj.phone )
        d_phone = obj.phone.toString().toLowerCase().indexOf(input.toLowerCase()) !== -1;
      
      if( obj.ssn)
        d_ssn = obj.ssn.toString().toLowerCase().indexOf(input.toLowerCase()) !== -1;

      if( obj.addr)
        d_addr = obj.addr.toString().toLowerCase().indexOf(input.toLowerCase()) !== -1;

      return d_name || d_phone || d_ssn || d_addr;

    },
    data: function (item, input)
    {
      var obj = JSON.parse(item.label);
      var litem =
      {
        label: obj.name,
        value: item.label
      };

      if( obj.phone )
        litem.label += ` ( ${obj.phone} )`;

      return litem
    }
  });
  
  /* Every time the user inputs a character, set the flag to false */
  input.addEventListener("input", function(ev)
  {
    var replacements =
    [
    { 'key' :'á', 'rep': 'a' },
    { 'key' :'é', 'rep': 'e' },
    { 'key' :'í', 'rep': 'i' },
    { 'key' :'ó', 'rep': 'o' },
    { 'key' :'ú', 'rep': 'u' },
    { 'key' :'Á', 'rep': 'A' },
    { 'key' :'É', 'rep': 'E' },
    { 'key' :'Í', 'rep': 'I' },
    { 'key' :'Ó', 'rep': 'O' },
    { 'key' :'Ú', 'rep:': 'U' }
    ];

    for (var i = replacements.length - 1; i >= 0; i--)
    {
      if( !ev.data)
        return false;

      if( ev.data.indexOf( replacements[i].key ) > -1 )
      {
        let key = replacements[i].key;
        let rep = replacements[i].rep;
        let imp = input.value.replace( key, rep );
        get('client_name').value = imp;
        aws.evaluate();
      }
    }
  });
  
  /* When the user clicks a suggestion, set the flag to the value */
  input.addEventListener("awesomplete-select", function(selection)
  {
    var obj = JSON.parse( selection.text.value );
    window.location.href = "/clientes/perfil/" + obj.id;
  });
  
</script>
@endsection
