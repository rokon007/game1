<!doctype html>
  <html lang="en" class="dark-theme">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="{{ asset('public/backend/assets/css/dark-theme.css') }}" type="text/css">
        @yield('css')
        @yield('title')
        @livewireStyles
    </head>
    <body>
      <!--start wrapper-->
      <div class="wrapper">
          <!--start top header-->
            <livewire:layout.backend.inc.header />
          <!--end top header-->
          <!--start sidebar  -->

                <livewire:layout.backend.inc.sidebar />

          <!--end sidebar -->
          <!--start content-->
                  {{$slot}}
          <!--end page main-->
      </div>
    <!--end wrapper-->
      @yield('JS')

      @livewireScripts

      @stack('scripts')
    </body>
</html>
