<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, viewport-fit=cover, shrink-to-fit=no">
    <!-- SEO Meta Tags -->
    <meta name="title" content="Housieblitz Game - Play & Win Prizes">
    <meta name="description" content="Join the ultimate multiplayer Housieblitz Game! Buy tickets, play real-time, and win exciting rewards. Register now!">
    <meta name="keywords" content="Housieblitz game, multiplayer bingo, play online game, win prizes, real-time game, ticket based game">
    <meta name="author" content="Housieblitz">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="theme-color" content="#625AFA">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <!-- The above tags *must* come first in the head, any other head content must come *after* these tags -->
    <!-- Title -->
    <title>Housieblitz | Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&amp;display=swap" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" href="{{asset('assets/frontend/img/icons/icon-72x72.png')}}">
    <!-- Apple Touch Icon -->
    <link rel="apple-touch-icon" href="{{asset('assets/frontend/img/icons/icon-96x96.png')}}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{asset('assets/frontend/img/icons/icon-152x152.png')}}">
    <link rel="apple-touch-icon" sizes="167x167" href="{{asset('assets/frontend/img/icons/icon-167x167.png')}}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{asset('assets/frontend/img/icons/icon-180x180.png')}}">
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="{{asset('assets/frontend/css/bootstrap.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/frontend/css/tabler-icons.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/frontend/css/animate.css')}}">
    <link rel="stylesheet" href="{{asset('assets/frontend/css/owl.carousel.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/frontend/css/magnific-popup.css')}}">
    <link rel="stylesheet" href="{{asset('assets/frontend/css/nice-select.css')}}">
    <!-- Stylesheet -->
    <link rel="stylesheet" href="{{asset('style.css')}}">
    <!-- Web App Manifest -->
    <link rel="manifest" href="{{asset('manifest.json')}}">
    <style>
        /* CSS */
        .preloader1 {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff; /* Change background color as needed */
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
        }

        .preloader1-content {
            position: relative;
            width: 152px;
            height: 152px;
        }

        .preloader1-img {
            width: 100%;
            height: auto;
            animation: pulseScale 1.5s infinite ease-in-out, rotate 4s infinite linear;
        }

        @keyframes pulseScale {
            0% { transform: scale(0.95); opacity: 0.8; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.8; }
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

  </head>
  <body>
    <!-- Preloader-->
     <div class="preloader1" id="preloader">
        <div class="preloader1-content">
            <img src="{{asset('assets/frontend/img/icons/icon-152x152.png')}}" alt="Logo" class="preloader1-img">
        </div>
    </div>

    <script>

        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');

            window.addEventListener('load', function() {

                setTimeout(function() {
                    preloader.style.opacity = '0';
                    preloader.style.visibility = 'hidden';
                }, 1500);
            });
        });
    </script>
    <!-- Preloader End-->
    <!-- Login Wrapper Area-->
    {{ $slot }}
    <!-- All JavaScript Files-->
    <script src="{{asset('assets/frontend/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('assets/frontend/js/jquery.min.js')}}"></script>
    <script src="{{asset('assets/frontend/js/waypoints.min.js')}}"></script>
    <script src="{{asset('assets/frontend/js/jquery.easing.min.js')}}"></script>
    <script src="{{asset('assets/frontend/js/jquery.magnific-popup.min.js')}}"></script>
    <script src="{{asset('assets/frontend/js/owl.carousel.min.js')}}"></script>
    <script src="{{asset('assets/frontend/js/jquery.counterup.min.js')}}"></script>
    <script src="{{asset('assets/frontend/js/jquery.countdown.min.js')}}"></script>
    <script src="{{asset('assets/frontend/js/jquery.passwordstrength.js')}}"></script>
    <script src="{{asset('assets/frontend/js/jquery.nice-select.min.js')}}"></script>
    <script src="{{asset('assets/frontend/js/theme-switching.js')}}"></script>
    <script src="{{asset('assets/frontend/js/active.js')}}"></script>
    <script src="{{asset('assets/frontend/js/pwa.js')}}"></script>
  </body>
</html>
