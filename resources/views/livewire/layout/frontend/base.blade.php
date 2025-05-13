<!DOCTYPE html>
<html lang="en">


  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover, shrink-to-fit=no">
    {{-- <meta name="description" content="Suha - Multipurpose Ecommerce Mobile HTML Template"> --}}
    @yield('meta_description')
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#625AFA">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <!-- The above tags *must* come first in the head, any other head content must come *after* these tags -->
    <!-- Title -->
    @yield('title')
    @yield('css')
    @livewireStyles
  </head>
  <body>

    <!-- Preloader-->
    @yield('preloader')
    {{-- <livewire:layout.frontend.header /> --}}


    <!-- Header Area -->
    @yield('header')
    {{-- <livewire:layout.frontend.header /> --}}

    <!-- offcanvas Area -->
    @yield('offcanvas')
    {{-- <livewire:layout.frontend.offcanvas /> --}}


    <!-- PWA Install Alert -->
    @yield('pwa_alart')
    {{-- <livewire:layout.frontend.pwa_alart /> --}}

    <!--start content-->
    {{$slot}}
    <!--end page main-->


    <!-- Internet Connection Status-->
    <div class="internet-connection-status" id="internetStatus"></div>


    <!-- Footer Nav-->
    @yield('footer')
    {{-- <livewire:layout.frontend.footer /> --}}

    <!-- All JavaScript Files-->
    @yield('JS')

      @livewireScripts

      @stack('scripts')
      @vite(['resources/js/app.js'])
  </body>


</html>
