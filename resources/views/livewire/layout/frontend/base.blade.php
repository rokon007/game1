<!DOCTYPE html>
<html lang="en">


  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @yield('meta_description')
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#625AFA">
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
      <!-- গেমরুমে রিডাইরেক্ট করার জন্য-->
      @auth
        <script>
            window.userId = {{ json_encode(auth()->id()) }};
        </script>
      @endauth
  </body>


</html>
