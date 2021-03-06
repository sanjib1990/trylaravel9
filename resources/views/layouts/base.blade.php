<!doctype html>
<html lang="{!! str_replace('_', '-', app()->getLocale()) !!}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{!! csrf_token() !!}">

    <title>{!! config('app.name', 'Undefined') !!}</title>
    <!-- Styles -->
    <link href="{!! asset('css/app.css') !!}" rel="stylesheet">
    @yield("styles")
</head>
<body>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Laravel') }}
            </a>
        </div>
    </nav>
    <div class="container-fluid">
        @yield('body')
    </div>
</div>
</body>
<script src="{!! asset('js/app.js') !!}" defer></script>
@yield("scripts")
</html>
