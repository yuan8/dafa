@extends('adminlte::master')

@php( $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home') )

@if (config('adminlte.use_route_url', false))
    @php( $dashboard_url = $dashboard_url ? route($dashboard_url) : '' )
@else
    @php( $dashboard_url = $dashboard_url ? url($dashboard_url) : '' )
@endif

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop
<style>
    html, body {
	    height: 100%;
	    text-align:center;
	    margin: 0px;
	    padding: 0px;
	    background: #fff !important;
	    font-family:'Open Sans', sans-serif;
	    font-size: 11pt;
	    font-weight: 400;
	    color: #fff;
	}
	#holder {
	    margin :0 auto;
	    display:inline-block;
	    width: 75%;
        margin-bottom: -17%;
	}
	.left {
	    float:left !important;
	}
	.right {
	    float:right !important;
	}
	#logo {
	    align:middle !important;
	    text-align:center !important
	}
	#wrapper {
	    height:200px;
	    position: relative;
	    padding: 0em 0em 0em 0em;
	    background: rgb(255, 255, 255);
	    border: 1px solid blue;
	}
</style>

@section('classes_body'){{ ($auth_type ?? 'login') . '-page' }}@stop

@section('body')
    <div class="{{ $auth_type ?? 'login' }}-box">

        {{-- Logo --}}
        <div class="{{ $auth_type ?? 'login' }}-logo">
            <a href="{{ $dashboard_url }}">
                {{-- <img src="{{ asset(config('adminlte.logo_img')) }}" height="50"> --}}
                {!! config('adminlte.logo', '<b>Admin</b>LTE') !!}
            </a>
        </div>

        {{-- Card Box --}}
        <div class="card {{ config('adminlte.classes_auth_card', 'card-outline card-primary') }}">

            {{-- Card Header --}}
            @hasSection('auth_header')
                <div class="card-header {{ config('adminlte.classes_auth_header', '') }}">
                    <h3 class="card-title float-none text-center">
                        @yield('auth_header')
                    </h3>
                </div>
            @endif

            {{-- Card Body --}}
            <div class="card-body {{ $auth_type ?? 'login' }}-card-body {{ config('adminlte.classes_auth_body', '') }}">
                @yield('auth_body')
            </div>

            {{-- Card Footer --}}
            @hasSection('auth_footer')
                <div class="card-footer {{ config('adminlte.classes_auth_footer', '') }}">
                    @yield('auth_footer')
                </div>
            @endif

        </div>

    </div>

@stop

@section('adminlte_js')
    @stack('js')
    @yield('js')
@stop
<div id="holder">
    <div id="logo" class="left">
        <img src="{{url('logo_mabes_tni.png')}}">
    </div>
    <div id="logo" class="right">
        <img src="{{url('logo_bais_tni.jpg')}}">
    </div>
</div>
