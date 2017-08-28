<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Styles --><!-- 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.4.1/css/bulma.min.css">
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css"> -->

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    @stack('scripts')

    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>
</head>
<body>

    <nav class="nav has-shadow">
      <div class="container">
        <div class="nav-left">
          <a class="nav-item" href="{{ url('/') }}">
            <img src="{{ asset('logo-mailcare-renard.png') }}" alt="MailCare: a modern webmail">
          </a>
        </div>
        <div class="nav-right nav-menu">
          <a class="nav-item is-tab is-hidden-mobile {{ Request::is('/') ? 'is-active' : '' }}" href="{{ url('/') }}">Home</a>
          <a class="nav-item is-tab is-hidden-mobile {{ Request::is('statistics') ? 'is-active' : '' }}" href="{{ url('/statistics') }}">Statistics</a>
          <a class="nav-item is-tab is-hidden-mobile {{ Request::is('about') ? 'is-active' : '' }}" href="{{ url('/about') }}">About</a>
        </div>
      </div>
    </nav>

    
    <section class="section">

        <div id="app" class="container">

            @yield('content')

        </div>
    </section>

    <footer class="footer">
      <div class="container">
        <div class="content has-text-centered">
          <p>
            <strong>MailCare.io</strong> by <a href="http://jgthms.com">Vincent Dauce</a>. The source code is licensed
            <a href="http://opensource.org/licenses/mit-license.php">MIT</a>. The website content
            is licensed <a href="http://creativecommons.org/licenses/by-nc-sa/4.0/">CC ANS 4.0</a>.
          </p>
          <p>
            <a class="icon" href="https://github.com/mailcare/mailcare">
              <i class="fa fa-github"></i>
            </a>
          </p>
        </div>
      </div>
    </footer>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
