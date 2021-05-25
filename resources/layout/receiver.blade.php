<html>
    <head>
     <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
     <link rel="stylesheet" href="{{ asset('tparty/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
     <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
     <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
        
    </head>
    <body>
        @yield('content')
    </body>
    <script type="text/javascript"  src="{{ mix(config('adminlte.laravel_mix_js_path', 'js/app.js')) }}"></script>

    @yield('js')
</html>