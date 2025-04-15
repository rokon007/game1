<div>
    @section('meta_description')
      <meta name="description" content="Altswave Shop">
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
        @if (Route::has('login'))
            @auth
                <div class="product-catagories-wrapper py-3">
                    <div class="container">
                    <div class="row g-2 rtl-flex-d-row-r">
                        <!-- Catagory Card -->
                        <div class="col-3">
                        <div class="card catagory-card">
                            <div class="card-body px-2">
                                <a href="catagory.html">
                                    {{-- <img src="{{asset('assets/frontend/img/core-img/woman-clothes.png')}}" alt=""> --}}
                                    $0
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
                                    {{-- <img src="{{asset('assets/frontend/img/core-img/grocery.png')}}" alt=""> --}}
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
                                    {{-- <img src="{{asset('assets/frontend/img/core-img/shampoo.png')}}" alt=""> --}}
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
                                    {{-- <img src="{{asset('assets/frontend/img/core-img/rowboat.png')}}" alt=""> --}}
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
        @endif
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

        <div class="featured-products-wrapper py-3">
            <div class="container">
              <div class="section-heading d-flex align-items-center justify-content-between dir-rtl">
                <h6>Prize Categories</h6><a class="btn btn-sm btn-light" href="featured-products.html">Rules<i class="ms-1 ti ti-arrow-right"></i></a>
              </div>
              <div class="row g-2">
                <!-- Featured Product Card-->
                @foreach ($prizes as $prize )
                    <div class="col-4">
                    <div class="card featured-product-card">
                        <div class="card-body">
                        <!-- Badge-->
                        {{-- <span class="badge badge-warning custom-badge">
                            <i class="ti ti-star-filled"></i>
                        </span> --}}
                        <div class="product-thumbnail-side">
                            <!-- Thumbnail -->
                            <a class="product-thumbnail d-block" href="single-product.html">
                                <img src="{{ Storage::url($prize->image_path) }}" alt="">
                            </a>
                            <a style="font-size: 12px; color: black; font-weight: bold;" href="#">
                                {{ $prize->name }}
                            </a>
                            <p class="sale-price">
                                TK {{ $prize->amount }}
                            </p>
                        </div>

                        </div>
                    </div>
                    </div>
                @endforeach
              </div>
            </div>
          </div>

    </div>


    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>
