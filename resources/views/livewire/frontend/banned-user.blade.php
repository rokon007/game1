<div>
    @section('meta_description')
        <meta name="title" content="Housieblitz Game - Account Banned">
        <meta name="description" content="Your access to Housieblitz has been banned. Please contact support if you believe this is a mistake.">
        <meta name="keywords" content="banned account, housieblitz, account suspended, contact support">
        <meta name="author" content="Housieblitz">
    @endsection

    @section('title')
        <title>{{ config('app.name', 'Laravel') }} | Banned</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
    @endsection

    @section('preloader')
        {{-- Preloader Disabled for Banned Users --}}
    @endsection

    @section('header')
         <!-- Header Area-->
        <div class="header-area" id="headerArea">
            <div class="container h-100 d-flex align-items-center justify-content-between rtl-flex-d-row-r">
                <!-- Back Button-->
                <div class="back-button me-2">
                    <a href="#">
                        <i class="ti ti-lock"></i>
                    </a>
                </div>
                <!-- Page Title-->
                <div class="page-heading">
                    <h6 class="mb-0 text-danger">
                        Access Denied
                    </h6>
                </div>
                <!-- Navbar Toggler Disabled -->
                <div class="suha-navbar-toggler ms-2 opacity-25">
                    <div><span></span><span></span><span></span></div>
                </div>
            </div>
        </div>
    @endsection

    @section('offcanvas')
        {{-- Offcanvas Menu Hidden for Banned Users --}}
    @endsection

    @section('pwa_alart')
        {{-- PWA Alert Hidden for Banned Users --}}
    @endsection

    <div class="page-content-wrapper">
        <div class="container">
            <!-- Banned Area -->
            <div class="offline-area-wrapper py-5 d-flex align-items-center justify-content-center">
                <div class="offline-text text-center">
                    <img class="mb-4 px-4" src="{{ asset('assets/frontend/img/bg-img/no-internet.png') }}" alt="Banned Icon" onerror="this.src='{{ asset('assets/frontend/img/bg-img/no-internet.png') }}'">
                    <h4 class="text-danger">Account Banned!</h4>
                    <p>Your account has been suspended from accessing Housieblitz.<br>
                        If you believe this was a mistake, please contact our support team.</p>
                    <a class="btn btn-danger btn-lg" href="{{ route('contact.support') }}">Contact Support</a>
                </div>
            </div>
        </div>
    </div>

    @section('footer')
        {{-- Footer Hidden for Banned Users --}}
    @endsection

    @section('JS')
        {{-- JS Hidden for Banned Users --}}
    @endsection
</div>
