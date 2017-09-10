<!DOCTYPE html>
<html>
<head>
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
<body onload="toggle('loader', false );">
  
  <nav class="navbar navbar-expand-md navbar-dark bg-danger">
    <a class="navbar-brand" href="{{ route('home') }}">Presta<strong>Control</strong></a>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
          {{ Auth::user()->name }} <span class="caret"></span>
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
          <a class="dropdown-item" href="{{ route('logout') }}" onclick="logout()">Cerrar Sesión</a>
          <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
          </form>
        </div>
      </li>
    </ul>
  </nav>
  
  
  <div id="loader" class="loader">
    <img src="{{ asset('img/gears-loader-red.gif') }}">
  </div>
  
  <br>
  
  @yield('content')
  
  <footer>
    <script src="{{ asset('js/jquery-3.2.1.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/awesomplete.js') }}"></script>
    <script src="{{ asset('js/nouislider.min.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script type="text/javascript">
    function logout() { event.preventDefault(); document.getElementById('logout-form').submit(); }
    </script>
    @yield('scripts')
  </footer>
</body>
</html>
