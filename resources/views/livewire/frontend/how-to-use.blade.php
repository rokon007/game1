<div>
    @section('title')
        <title>{{ config('app.name') }} | How to Use</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
    @endsection

    @section('preloader')
        {{-- Optional Preloader --}}
    @endsection

    @section('header')
        <!-- Header Area-->
        <div class="header-area" id="headerArea">
            <div class="container h-100 d-flex align-items-center justify-content-between rtl-flex-d-row-r">
                <!-- Back Button-->
                <div class="back-button me-2" style="display: {{$dataMode ? 'block' : 'none'}};">
                    <a href="{{ url()->previous() }}">
                        <i class="ti ti-arrow-left"></i>
                    </a>
                </div>
                <div class="back-button me-2" style="display: {{$detailsMode ? 'block' : 'none'}};">
                    <a wire:click="backToList" style="cursor: pointer;">
                        <i class="ti ti-arrow-left"></i>
                    </a>
                </div>
                <!-- Page Title-->
                <div class="page-heading">
                    <h6 class="mb-0 text-primary">
                        User Guide
                    </h6>
                </div>
                <!-- Navbar Toggler -->
                <div class="suha-navbar-toggler ms-2" data-bs-toggle="offcanvas" data-bs-target="#suhaOffcanvas" aria-controls="suhaOffcanvas">
                    <div><span></span><span></span><span></span></div>
                </div>
            </div>
        </div>
    @endsection

    @section('offcanvas')
        <livewire:layout.frontend.offcanvas />
    @endsection

    @section('pwa_alart')
        <livewire:layout.frontend.pwa_alart />
    @endsection


    <div class="page-content-wrapper py-4">
         <div class="container" style="display: {{$dataMode ? 'block' : 'none'}};">
            <div class="notification-wrapper">
                <div class="notification-area pb-2">
                    <div class="list-group">
                        @foreach($data as $item)
                            <a class="list-group-item d-flex align-items-center border-0"

                                style="cursor: pointer"
                                wire:click="details('{{ $item->id }}')">
                                <span class="noti-icon">
                                    <i class="ti ti-check"></i>
                                </span>
                                <div class="noti-info">
                                    <h6 class="mb-1">{{ $item->title}}</h6>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
         </div>
        <div class="container" style="display: {{$detailsMode ? 'block' : 'none'}};">
            <h3 class="text-center mb-4">{{$title}}</h3>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body text-center">
                    <h5>🎥 Watch Tutorial Video</h5>
                    <div class="ratio ratio-16x9 mt-3">
                        <iframe
                            src="https://www.youtube.com/embed/{{$video_url}}"
                            title="How to use"
                            allowfullscreen>
                        </iframe>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-body">
                    {!! $description !!}
                </div>
            </div>
        </div>
    </div>

    @section('footer')
        @include('livewire.layout.frontend.footer')
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>
