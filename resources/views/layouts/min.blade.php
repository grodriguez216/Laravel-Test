<!DOCTYPE html>
<html>
<head><meta http-equiv="Content-Type" content="text/html; charset=gb18030">
  <title>Control - Prestamos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Options: cosmo | paper | cyborg | flaty | sandstone | yeti -->
  <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('css/font-awesome.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('css/awesomplete.base.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('css/awesomplete.theme.css') }}">
  
  @yield('styles')
  
  <link rel="stylesheet" type="text/css" href="{{ asset('css/app.css') }}">
  <link href="https://fonts.googleapis.com/css?family=Roboto:200,300,500" rel="stylesheet">
</head>
<body>

  <div class="container-fluid pt-2 text-left">
    <div class="row">
      <div class="col">
        <samp> {{ date('d-M-Y') }} </samp>
      </div>
    </div>
  </div>
  
  @yield('content')  
  <footer>
    <script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/awesomplete.js') }}"></script>
    <script src="{{ asset('js/nouislider.min.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
  </footer>
</body>
</html>
