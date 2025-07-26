{{-- <div class="preloader" id="preloader">
    <div class="spinner-grow text-secondary" role="status">
        <div class="sr-only"></div>
    </div>
</div> --}}
{{-- <div>
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

    <!-- HTML -->
    <div class="preloader1" id="preloader">
        <div class="preloader1-content">
            <img src="{{asset('assets/frontend/img/icons/icon-152x152.png')}}" alt="Logo" class="preloader1-img">
        </div>
    </div>

    <script>
        // JavaScript (সব শেষে </body> ট্যাগের আগে রাখুন)
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');

            window.addEventListener('load', function() {
                // 1.5 সেকেন্ড পর প্রিলোডার হাইড হবে
                setTimeout(function() {
                    preloader.style.opacity = '0';
                    preloader.style.visibility = 'hidden';
                }, 1500);
            });
        });
    </script>
</div> --}}

<div>
    <style>
        /* CSS */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            z-index: 999999;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.5s ease-out, visibility 0.5s ease-out;
        }

        .preloader-content {
            position: relative;
            width: 120px;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .preloader-img {
            width: 60px;
            height: 60px;
            z-index: 2;
            animation: pulse 1.5s infinite ease-in-out;
        }

        .spinner {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: #3b82f6; /* Blue-500 color */
            animation: spin 1s linear infinite;
            z-index: 1;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 0.9; }
            50% { transform: scale(1.05); opacity: 1; }
            100% { transform: scale(0.95); opacity: 0.9; }
        }
    </style>

    <!-- HTML -->
    <div class="preloader" id="preloader">
        <div class="preloader-content">
            <div class="spinner"></div>
            <img src="{{ asset('assets/frontend/img/icons/icon-152x152.png') }}" alt="Logo" class="preloader-img">
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');

            window.addEventListener('load', function() {
                // 1 সেকেন্ড পর প্রিলোডার হাইড হবে
                setTimeout(function() {
                    preloader.style.opacity = '0';
                    preloader.style.visibility = 'hidden';
                }, 1000);
            });
        });
    </script>
</div>
