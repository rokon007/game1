<div>
    @section('meta_description')
        <meta name="title" content="Housieblitz Game - Contact Support">
        <meta name="description" content="Having trouble? Contact the Housieblitz support team for help. We're here to assist you.">
        <meta name="keywords" content="contact support, help, banned account, housieblitz support">
        <meta name="author" content="Housieblitz">
    @endsection

    @section('title')
        <title>{{ config('app.name', 'Laravel') }} | Contact Support</title>
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
                <div class="back-button me-2">
                    <a href="{{ url()->previous() }}">
                        <i class="ti ti-arrow-left"></i>
                    </a>
                </div>
                <!-- Page Title-->
                <div class="page-heading">
                    <h6 class="mb-0 text-primary">
                        Contact Support
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

    <div class="page-content-wrapper">
        <div class="container my-5">
            <div class="row justify-content-center">
                <div class="col-md-12 pt-5 pb-5">
                    <div class="card border-primary shadow rounded">
                        <div class="card-header bg-primary text-white text-center">
                            <h4 style="color: white;">Contact Support</h4>
                        </div>

                        <div class="card-body">
                            @if (session()->has('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            <form wire:submit.prevent="send">
                                <div class="mb-3">
                                    <label>Your Name</label>
                                    <input type="text" class="form-control" wire:model.defer="name">
                                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3">
                                    <label>Your Email</label>
                                    <input type="email" class="form-control" wire:model.defer="email">
                                    @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <style>
                                    textarea.form-control {
                                        min-height: 150px !important;
                                    }
                                </style>
                                <div class="mb-3">
                                    <label>Your Message</label>
                                    <textarea class="form-control" rows="5" wire:model.defer="message"></textarea>
                                    @error('message') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <button class="btn btn-primary w-100" type="submit" wire:loading.attr="disabled">
                                    <span wire:loading.remove>Send Message</span>
                                    <span wire:loading>Sending...</span>
                                </button>
                            </form>
                        </div>
                    </div>
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
