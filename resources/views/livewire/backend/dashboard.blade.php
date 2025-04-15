<main>
    @section('title')
        <title>Admin | Dashboard</title>
    @endsection
    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
    @endsection


    <main class="page-content">
        <section class="py-4">
            <div class="container">
                <div class="row g-4">

                    <div class="col-12">
                        <!-- Counter START -->
                        <div class="row g-4">


                            @if (session()->has('message'))
                            <div class="col-md-12 text-center">
                                <center>
                                    <div class="col-md-5">
                                        <div class="alert border-0 bg-success alert-dismissible fade show py-2">
                                            <div class="d-flex align-items-center">
                                            <div class="fs-3 text-white"><i class="bi bi-check-circle-fill"></i>
                                            </div>
                                            <div class="ms-3">
                                                <div class="text-white">{{ session('message') }}</div>
                                            </div>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                        </div>
                                    </div>
                                </center>
                            </div>
                            @endif


                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>



    @section('JS')
         @include('livewire.layout.backend.inc.js')
    @endsection
</main>

