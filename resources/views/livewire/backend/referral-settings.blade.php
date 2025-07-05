<main>
    @section('title')
        <title>Admin | Referral Setting</title>
    @endsection
    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
    @endsection
    <main class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
          <div class="breadcrumb-title pe-3">Admin</div>
          <div class="ps-3">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Referral Setting</li>
              </ol>
            </nav>
          </div>
          <div class="ms-auto">
            <div class="btn-group">
              <a href="#" class="btn btn-primary">ReferralSetting</a>
            </div>
          </div>
        </div>
        <!--end breadcrumb-->

            @if (session('message'))
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
          <div class="card">
             <div class="card-header py-3">
                <h2>Referral Settings</h2>
             </div>
             <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="form-group mb-3">
                        <label for="commission_percentage">Commission Percentage (%)</label>
                        <input type="number" step="0.01" class="form-control" wire:model="commission_percentage" id="commission_percentage">
                        @error('commission_percentage') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group mb-3">
                        <label for="max_commission_count">Max Commission Count</label>
                        <input type="number" class="form-control" wire:model="max_commission_count" id="max_commission_count">
                        @error('max_commission_count') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
          </div>
    </main>

    @section('JS')
    @include('livewire.layout.backend.inc.js')
@endsection
</main>
