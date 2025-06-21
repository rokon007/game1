<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ?? 'Hazari Game Room' }} - {{ config('app.name', 'Hazari Card Game') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles
    
    <!-- Game Room Specific Styles -->
    <style>
        /* Prevent zoom and scroll on mobile */
        html, body {
            height: 100%;
            overflow: hidden;
            touch-action: manipulation;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Disable pull-to-refresh */
        body {
            overscroll-behavior: none;
        }
        
        /* Full screen game experience */
        #game-container {
            height: 100vh;
            width: 100vw;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
        }
        
        /* Prevent text selection during gameplay */
        .game-area {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Custom scrollbar for card areas */
        .card-scroll::-webkit-scrollbar {
            height: 4px;
        }
        
        .card-scroll::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
        }
        
        .card-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }
        
        .card-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
        
        /* Landscape orientation optimization */
        @media screen and (orientation: landscape) {
            body {
                overflow: hidden;
            }
        }
        
        /* Portrait warning for better experience */
        @media screen and (orientation: portrait) and (max-width: 768px) {
            .portrait-warning {
                display: block;
            }
        }
        
        .portrait-warning {
            display: none;
        }
        
        /* Prevent context menu on long press */
        * {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Allow text selection only for specific elements */
        input, textarea, .selectable {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        
        /* Loading spinner */
        .loading-spinner {
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 3px solid #fff;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Game notifications */
        .game-notification {
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        /* Card hover effects */
        .card-hover:hover {
            transform: translateY(-5px);
            transition: transform 0.2s ease;
        }
        
        /* Disable zoom on double tap */
        .no-zoom {
            touch-action: manipulation;
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-900 no-zoom">
    <!-- Portrait Warning for Mobile -->
    <div class="portrait-warning fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 text-center max-w-sm">
            <div class="text-4xl mb-4">📱</div>
            <h3 class="text-lg font-bold mb-2">Better Experience</h3>
            <p class="text-gray-600 mb-4">
                For the best gaming experience, please rotate your device to landscape mode.
            </p>
            <div class="text-2xl">🔄</div>
        </div>
    </div>
    
    <!-- Main Game Container -->
    <div id="game-container" class="game-area">
        <!-- Loading Overlay -->
        <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-40" style="display: none;">
            <div class="bg-white rounded-lg p-6 text-center">
                <div class="loading-spinner mx-auto mb-4"></div>
                <p class="text-gray-700">Loading game...</p>
            </div>
        </div>
        
        <!-- Game Content -->
        <main class="h-full w-full">
            {{ $slot }}
        </main>
        
        <!-- Connection Status -->
        <div id="connection-status" class="fixed top-2 left-2 z-30">
            <div class="bg-green-500 text-white px-2 py-1 rounded text-xs" id="online-indicator">
                <i class="fas fa-wifi mr-1"></i> Online
            </div>
            <div class="bg-red-500 text-white px-2 py-1 rounded text-xs" id="offline-indicator" style="display: none;">
                <i class="fas fa-wifi-slash mr-1"></i> Offline
            </div>
        </div>
        
        <!-- Exit Game Button -->
        <div class="fixed top-2 left-1/2 transform -translate-x-1/2 z-30">
            <button onclick="confirmExit()" 
                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-xs">
                <i class="fas fa-times mr-1"></i> Exit Game
            </button>
        </div>
    </div>
    
    <!-- Exit Confirmation Modal -->
    <div id="exit-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
        <div class="bg-white rounded-lg p-6 max-w-sm w-full mx-4">
            <div class="text-center">
                <div class="text-3xl mb-4">⚠️</div>
                <h3 class="text-lg font-bold mb-2">Exit Game?</h3>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to leave the game? You may lose your progress.
                </p>
                <div class="flex gap-4 justify-center">
                    <button onclick="closeExitModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <a href="{{ route('games.index') }}" 
                       class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Exit Game
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Livewire Scripts -->
    @livewireScripts
    
    <!-- Game Room Specific Scripts -->
    <script>
        // Prevent zoom on double tap
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
        
        // Prevent pinch zoom
        document.addEventListener('gesturestart', function (e) {
            e.preventDefault();
        });
        
        document.addEventListener('gesturechange', function (e) {
            e.preventDefault();
        });
        
        document.addEventListener('gestureend', function (e) {
            e.preventDefault();
        });
        
        // Prevent context menu
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
        });
        
        // Connection status monitoring
        function updateConnectionStatus() {
            const onlineIndicator = document.getElementById('online-indicator');
            const offlineIndicator = document.getElementById('offline-indicator');
            
            if (navigator.onLine) {
                onlineIndicator.style.display = 'block';
                offlineIndicator.style.display = 'none';
            } else {
                onlineIndicator.style.display = 'none';
                offlineIndicator.style.display = 'block';
            }
        }
        
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);
        
        // Exit game confirmation
        function confirmExit() {
            document.getElementById('exit-modal').style.display = 'flex';
        }
        
        function closeExitModal() {
            document.getElementById('exit-modal').style.display = 'none';
        }
        
        // Loading overlay functions
        function showLoading() {
            document.getElementById('loading-overlay').style.display = 'flex';
        }
        
        function hideLoading() {
            document.getElementById('loading-overlay').style.display = 'none';
        }
        
        // Auto-hide loading after page load
        window.addEventListener('load', function() {
            setTimeout(hideLoading, 1000);
        });
        
        // Prevent back button during game
        history.pushState(null, null, location.href);
        window.onpopstate = function () {
            confirmExit();
            history.go(1);
        };
        
        // Screen orientation handling
        function handleOrientationChange() {
            // Force landscape on mobile for better experience
            if (window.innerHeight > window.innerWidth && window.innerWidth < 768) {
                document.querySelector('.portrait-warning').style.display = 'flex';
            } else {
                document.querySelector('.portrait-warning').style.display = 'none';
            }
        }
        
        window.addEventListener('orientationchange', handleOrientationChange);
        window.addEventListener('resize', handleOrientationChange);
        
        // Initial check
        handleOrientationChange();
        
        // Prevent sleep mode during game
        let wakeLock = null;
        
        async function requestWakeLock() {
            try {
                if ('wakeLock' in navigator) {
                    wakeLock = await navigator.wakeLock.request('screen');
                }
            } catch (err) {
                console.log('Wake lock failed:', err);
            }
        }
        
        // Request wake lock when game starts
        document.addEventListener('DOMContentLoaded', requestWakeLock);
        
        // Re-request wake lock when page becomes visible
        document.addEventListener('visibilitychange', () => {
            if (wakeLock !== null && document.visibilityState === 'visible') {
                requestWakeLock();
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // ESC to exit game
            if (e.key === 'Escape') {
                confirmExit();
            }
            
            // F11 for fullscreen
            if (e.key === 'F11') {
                e.preventDefault();
                if (!document.fullscreenElement) {
                    document.documentElement.requestFullscreen();
                } else {
                    document.exitFullscreen();
                }
            }
        });
        
        // Performance monitoring
        let performanceWarning = false;
        
        function checkPerformance() {
            const fps = 60;
            const threshold = 30;
            
            if (fps < threshold && !performanceWarning) {
                performanceWarning = true;
                console.warn('Low performance detected. Consider closing other apps.');
            }
        }
        
        setInterval(checkPerformance, 5000);
    </script>
    
    @stack('scripts')
</body>
</html>
