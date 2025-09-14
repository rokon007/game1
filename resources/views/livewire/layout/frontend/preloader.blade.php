{{-- <div>
    <style>
        /* Preloader Styles */
        .preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #ffffff;
            z-index: 9999;
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
        }

        .spinner {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 4px solid rgba(59, 130, 246, 0.2);
            border-radius: 50%;
            border-top-color: #3b82f6;
            animation: spin 1.2s linear infinite;
            z-index: 1;
        }

        .spinner-inner {
            position: absolute;
            width: 105%;
            height: 105%;
            border: 4px solid transparent;
            border-radius: 50%;
            border-right-color: #3b82f6;
            opacity: 0.8;
            animation: spinReverse 1.5s linear infinite;
            z-index: 1;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes spinReverse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(-360deg); }
        }

        .hidden {
            opacity: 0;
            visibility: hidden;
        }
    </style>

    <!-- Preloader HTML -->
    <div class="preloader" id="preloader">
        <div class="preloader-content">
            <div class="spinner"></div>
            <div class="spinner-inner"></div>
            <img src="{{ asset('assets/frontend/img/icons/icon-152x152.png') }}" alt="Logo" class="preloader-img">
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');

            window.addEventListener('load', function() {
                setTimeout(function() {
                    preloader.classList.add('hidden');
                }, 1000);
            });

            // For demo purposes - reload button
            document.getElementById('reloadButton').addEventListener('click', function() {
                preloader.classList.remove('hidden');
                setTimeout(function() {
                    preloader.classList.add('hidden');
                }, 3000);
            });
        });
    </script>
</div> --}}

