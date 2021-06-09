<html>
    <head>
        <title>DAFTAR TAMU</title>
     <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
     <link rel="stylesheet" href="{{ asset('tparty/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
     <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
     <script src="{{ asset('vendor/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
     <script type="text/javascript" src="{{url('tparty/bower_components/underscore/underscore-min.js')}}"></script>

        
    </head>
    <body>
        <div id="app"></div>
        <div id="site-landing" style="width:100vw;
        position:fixed; top:0; left:0; z-index:1;  background-color: rgb(30, 30, 30);
        min-height:100vh"></div>
            <div class="" style="float:left; width:100%; z-index:99; position:relative; ;padding:10px; margin-top:50px">
            @yield('content')</div>
</body>
    <script src="{{ asset(('js/app.js')) }}"></script>
    <script src="{{ asset(('js/tree.js')) }}"></script>
    <script src="{{ asset(('js/vanta1.js')) }}"></script>
    <style>
        .card{
            background-color: #ffffffde!important;
        }
    </style>

    <script>
        
    </script>
    <script>
        let $sitelading = $('#site-landing');
    $sitelading.polygonizr();

    // Update size.
    $(window).resize(function () {
      $sitelading.polygonizr("stop");
      $sitelading.polygonizr({
        canvasHeight: $(this).height(),
        canvasWidth: $(this).width()
      });

      $sitelading.polygonizr("refresh");
    });
        </script>
    @yield('js')
</html>