<div>
    @section('meta_description')
        <meta name="title" content="Housieblitz Game - Play & Win Prizes">
        <meta name="description" content="Join the ultimate multiplayer Housieblitz Game! Buy tickets, play real-time, and win exciting rewards. Register now!">
        <meta name="keywords" content="Housieblitz game, multiplayer bingo, play online game, win prizes, real-time game, ticket based game">
        <meta name="author" content="Housieblitz">
    @endsection
    @section('title')
        <title>{{ config('app.name', 'Laravel') }} | Home</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
        <style>
            .custom-badge {
                position: absolute;
                top: 10px;
                right: 10px;
                background-color: #ffc107;
                color: #fff;
                padding: 5px 10px;
                font-size: 12px;
                border-radius: 50px;
            }
            .currency-icon {
                display: inline-block;
                vertical-align: middle;
                margin-right: 1px;
            }
        </style>
    @endsection

    @section('preloader')
        {{-- <livewire:layout.frontend.preloader /> --}}
    @endsection

    @section('header')
        <livewire:layout.frontend.header />
    @endsection

    @section('offcanvas')
        <livewire:layout.frontend.offcanvas />
    @endsection

    @section('pwa_alart')
        <livewire:layout.frontend.pwa_alart />
    @endsection

    <div class="page-content-wrapper">
        {{--@if (Route::has('login'))
            @auth
                <div class="product-catagories-wrapper py-3">
                    <div class="container">
                    <div class="row g-2 rtl-flex-d-row-r">
                        <!-- Catagory Card -->
                        <div class="col-3">
                        <div class="card catagory-card">
                            <div class="card-body px-2">
                                <a href="catagory.html">
                                    {{$cradit}} Tk
                                    <span>Current Balance</span>
                                </a>
                            </div>
                        </div>
                        </div>
                        <!-- Catagory Card -->
                        <div class="col-3">
                        <div class="card catagory-card">
                            <div class="card-body px-2">
                                <a href="catagory.html">

                                    0
                                    <span>Total Wins</span>
                                </a>
                            </div>
                        </div>
                        </div>
                        <!-- Catagory Card -->
                        <div class="col-3">
                        <div class="card catagory-card">
                            <div class="card-body px-2">
                                <a href="catagory.html">

                                    0
                                    <span>Participations</span>
                                </a>
                            </div>
                        </div>
                        </div>
                        <!-- Catagory Card -->
                        <div class="col-3">
                        <div class="card catagory-card">
                            <div class="card-body px-2">
                                <a href="catagory.html">

                                    0
                                    <span>Tickets Purchased Today</span>
                                </a>
                            </div>
                        </div>
                        </div>
                    </div>
                    </div>
                </div>
            @endauth
        @endif--}}
        <div class="hero-wrapper">
            <div class="container">
                <div class="pt-2">
                    <!-- Hero Slides-->
                    <div class="hero-slides owl-carousel">
                        @foreach ($addBanners as $banner )
                        <!-- Single Hero Slide-->
                        <div class="single-hero-slide" style="background-image: url('{{ Storage::url($banner->image_path) }}')">
                            <div class="slide-content h-100 d-flex align-items-center">
                                <div class="slide-text">
                                    <h4 class="text-white mb-0" data-animation="fadeInUp" data-delay="100ms" data-duration="1000ms">
                                        {{$banner->title}}
                                    </h4>
                                    <p class="text-white" data-animation="fadeInUp" data-delay="400ms" data-duration="1000ms">
                                        {{$banner->text}}
                                    </p>
                                    <a class="btn btn-primary" href="{{$banner->url}}" data-animation="fadeInUp" data-delay="800ms" data-duration="1000ms">
                                        {{$banner->button_name}}
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
        {{-- @if(auth()->user())
	        <div class="container">
	            @livewire('location-updater')
	        </div>
        @endif --}}

        <div class="featured-products-wrapper py-3">
            <div class="container">
              <div class="section-heading d-flex align-items-center justify-content-between dir-rtl">
                <h6>Other Games</h6>
                {{-- <a id="installAppBtn" style="display:none;cursor: pointer;" class="btn btn-primary">
                    <i class="ti ti-device-mobile" style="font-size: 18px; margin-right: 5px;"></i>
                    <span>Install App</span>
                </a>
                <script>
                    let deferredPrompt;
                    const installBtn = document.getElementById("installAppBtn");

                    window.addEventListener("beforeinstallprompt", (e) => {
                        e.preventDefault();
                        deferredPrompt = e;
                        installBtn.style.display = "block";
                    });

                    installBtn.addEventListener("click", () => {
                        installBtn.style.display = "none";
                        deferredPrompt.prompt();
                        deferredPrompt.userChoice.then((choiceResult) => {
                            console.log(choiceResult.outcome === "accepted"
                                ? "User accepted the install prompt"
                                : "User dismissed the install prompt"
                            );
                            deferredPrompt = null;
                        });
                    });
                </script> --}}
              </div>
              <div class="row g-2">
                <!-- Featured Product Card-->
                <div class="col-4">
                    <div class="card featured-product-card">
                        <div class="card-body">
                            <div class="product-thumbnail-side">
                                <!-- Thumbnail -->
                                <a class="product-thumbnail d-block" href="{{route('buy_ticket')}}">
                                    <img src="{{asset('assets/frontend/img/core-img/housie.png')}}" alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card featured-product-card">
                        <div class="card-body">
                            <div class="product-thumbnail-side">
                                <!-- Thumbnail -->
                                <a class="product-thumbnail d-block" href="{{route('lottery.index')}}">
                                    <img src="{{asset('assets/frontend/img/core-img/g1.png')}}" alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card featured-product-card">
                        <div class="card-body">
                            <div class="product-thumbnail-side">
                                <!-- Thumbnail -->
                                <a class="product-thumbnail d-block" href="{{route('games.index')}}">
                                    <img src="{{asset('assets/frontend/img/core-img/hajari.png')}}" alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-4">
                    <div class="card featured-product-card">
                        <div class="card-body">
                            <div class="product-thumbnail-side">
                                <!-- Thumbnail -->
                                <a class="product-thumbnail d-block" href="{{route('lucky_spin')}}">
                                    <img src="{{asset('assets/frontend/img/core-img/lucky-spin.png')}}" alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card featured-product-card">
                        <div class="card-body">
                            <div class="product-thumbnail-side">
                                <!-- Thumbnail -->
                                <a class="product-thumbnail d-block" href="{{route('crash_game')}}">
                                    <img src="{{asset('assets/frontend/img/core-img/crash-game.png')}}" alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card featured-product-card">
                        <div class="card-body">
                            <div class="product-thumbnail-side">
                                <!-- Thumbnail -->
                                <a class="product-thumbnail d-block" href="#">
                                    <img src="{{asset('assets/frontend/img/core-img/g4.png')}}" alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-4">
                    <div class="card featured-product-card">
                        <div class="card-body">
                            <div class="product-thumbnail-side">
                                <!-- Thumbnail -->
                                <a class="product-thumbnail d-block" href="#">
                                    <img src="{{asset('assets/frontend/img/core-img/g5.png')}}" alt="">
                                </a>
                            </div>
                        </div>
                    </div>
                </div> --}}
                {{-- @foreach ($prizes as $prize )
                    <div class="col-4">
                        <div class="card featured-product-card">
                            <div class="card-body">
                                <div class="product-thumbnail-side">
                                    <a class="product-thumbnail d-block" href="#">
                                        <img src="{{ Storage::url($prize->image_path) }}" alt="">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach --}}
              </div>
            </div>
        </div>
        <div class="container">
            <center>
                <!-- Action Buttons -->
                <div class="action-buttons mb-4">
                    <a href="{{route('rifleAccount')}}" class="btn btn-success me-2">
                        <i class="fas fa-plus-circle me-2"></i>Deposit
                    </a>
                    <a href="{{route('withdrawal')}}" class="btn btn-warning">
                        <i class="fas fa-hand-holding-usd me-2"></i>Withdraw
                    </a>
                </div>
                <div class="mb-4">
                    .
                </div>
            </center>
        </div>

    </div>


    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>
