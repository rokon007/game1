<!DOCTYPE html>
<html lang="en">


  <head>

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-TS7BZ52P');</script>
    <!-- End Google Tag Manager -->

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
    <meta name="google-site-verification" content="UbkvPt_oAAGgpEZayP4bF_JG9vrdCLqhwF39WZ0gTDA" />
  </head>
  <body>

    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-TS7BZ52P"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->

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

    <!-- Global Live Draw Modal -->
    <livewire:frontend.lottery.live-draw-modal />
    <!-- Internet Connection Status-->
    <div class="internet-connection-status" id="internetStatus"></div>


    <!-- Footer Nav-->
    @yield('footer')
    {{-- <livewire:layout.frontend.footer /> --}}

{{-- এপের জন্য  --}}
    {{-- <script>
        if ("serviceWorker" in navigator) {
            window.addEventListener("load", function () {
                navigator.serviceWorker.register("/sw.js").then(function (reg) {
                    console.log("Service Worker Registered", reg);
                }).catch(function (err) {
                    console.log("Service Worker Registration Failed", err);
                });
            });
        }
    </script> --}}


    <!-- All JavaScript Files-->
    @yield('JS')
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

                if (timezone) {
                    fetch('{{ route('set.timezone') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ timezone })
                    });
                }
            });

            // Listen for draw started events globally
            Echo.channel('lottery-channel')
                .listen('DrawStarted', (e) => {
                    // Show notification
                    if (Notification.permission === 'granted') {
                        new Notification('🎰 লটারি ড্র শুরু!', {
                            body: e.lottery_name + ' এর ড্র শুরু হয়েছে!',
                            icon: '/favicon.ico'
                        });
                    }

                    // Start the live draw modal
                    Livewire.dispatch('startLiveDraw', e.lottery_id);
                });

            // Request notification permission
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission();
            }
        </script>

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
