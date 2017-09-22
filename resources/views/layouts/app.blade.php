<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    @stack('scripts')

    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
            'csrfToken' => csrf_token(),
        ]) !!};
    </script>

    <style type="text/css">
      body {
        font-size: 1.25rem;
      }
      .navbar-item {
        font-family: 'Exo 2', sans-serif;
      }
    </style>
</head>
<body>



<nav class="navbar is-transparent  has-shadow" role="navigation" aria-label="main navigation">
<div class="container">
  <div class="navbar-brand">
    <a class="navbar-item" href="{{ url('/') }}">
      <img src="{{ asset('logo-mailcare-renard.png') }}" alt="MailCare: a modern disposable email address" width="32" height="50">
    </a>
  </div>
  <div class="navbar-menu is-active">
    <div class="navbar-start">
      <a class="navbar-item title is-3" href="{{ url('/') }}">
        {{ config('app.name') }}
      </a>
    </div>
    <div class="navbar-end">
      <div class="navbar-tabs">
          <a class="navbar-item is-tab {{ Request::is('/', 'emails*') ? 'is-active' : '' }}" href="{{ url('/') }}">Emails</a>
          <a class="navbar-item is-tab {{ Request::is('statistics') ? 'is-active' : '' }}" href="{{ url('/statistics') }}">Statistics</a>
          <a class="navbar-item is-tab {{ Request::is('about') ? 'is-active' : '' }}" href="{{ url('/about') }}">About</a>
      </div>
    </div>
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
            <strong>MailCare.io</strong> by <a href="http://vincent.dauce.fr/">Vincent Dauce</a>. The source code is licensed
            <a href="http://opensource.org/licenses/mit-license.php">MIT</a>.
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
