<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title') — PIGEA</title>
  <meta name="description" content="@yield('description', 'Plataforma de Impulsionamento das Geociências em Angola')">
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}">
  <link rel="stylesheet" href="{{ asset('css/plugins.css') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('css/color.css') }}">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
  @stack('styles')
</head>
<body class="dashboard-body">
  @yield('content')
  <script src="{{ asset('js/jquery.min.js') }}"></script>
  <script src="{{ asset('js/plugins.js') }}"></script>
  <script src="{{ asset('js/dashboard.js') }}"></script>
  @stack('scripts')
</body>
</html>
