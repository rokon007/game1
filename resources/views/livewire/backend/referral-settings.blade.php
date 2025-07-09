<main>
    @section('title')
        <title>Admin | Referral Settings</title>
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
            <div class="breadcrumb-title pe-3">Admin Panel</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i> Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Referral Settings</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary px-4">
                        <i class="bi bi-share me-2"></i>Referral Program
                    </button>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->

        @if (session('message'))
            <div class="col-12">
                <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                    <div class="d-flex align-items-center">
                        <div class="font-35 text-white"><i class="bx bxs-check-circle"></i></div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-white">Success</h6>
                            <div class="text-white">{{ session('message') }}</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="card radius-10">
            <div class="card-header bg-transparent border-bottom">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0">Referral Program Settings</h5>
                    <div class="ms-auto">
                        <i class="bi bi-gift-fill fs-4 text-primary"></i>
                    </div>
                </div>
                <p class="mb-0 text-muted">Configure your referral commission system</p>
            </div>

            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-none border">
                                <div class="card-body">
                                    <h6 class="mb-3 text-primary">Commission Settings</h6>

                                    <div class="mb-3">
                                        <label for="commission_percentage" class="form-label">Commission Percentage</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" class="form-control"
                                                   wire:model="commission_percentage" id="commission_percentage">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            Percentage of deposit amount given as referral bonus
                                        </small>
                                        @error('commission_percentage')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-none border">
                                <div class="card-body">
                                    <h6 class="mb-3 text-primary">Limitations</h6>

                                    <div class="mb-3">
                                        <label for="max_commission_count" class="form-label">Max Commission Count</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control"
                                                   wire:model="max_commission_count" id="max_commission_count">
                                            <span class="input-group-text">times</span>
                                        </div>
                                        <small class="text-muted mt-1 d-block">
                                            Maximum number of commissions a user can earn from referrals
                                        </small>
                                        @error('max_commission_count')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Example Calculation -->
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary">
                            <h6 class="mb-0 text-white text-center"><i class="bi bi-calculator me-2"></i>Example Calculation</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="p-2 bg-light rounded border text-center">
                                        <p class="mb-1 small text-muted">Referral Deposit</p>
                                        <h5 class="mb-0 text-primary">৳1000</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 bg-light rounded border text-center">
                                        <p class="mb-1 small text-muted">Commission Rate</p>
                                        <h5 class="mb-0 text-info">{{ $commission_percentage ?? '0' }}%</h5>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 bg-light rounded border text-center">
                                        <p class="mb-1 small text-muted">Referrer Earns</p>
                                        <h5 class="mb-0 text-success">৳{{ number_format((1000 * ($commission_percentage ?? 0)/100)) }}</h5>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 p-3 bg-light rounded text-center">
                                <p class="mb-0 small text-muted">
                                    This bonus will be paid <strong>{{ $max_commission_count ?? '0' }} times</strong> per referral
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-6 mb-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <span wire:loading.delay wire:target="save" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                <i class="bi bi-save me-2"></i>Save Settings
                            </button>
                        </div>
                        <div class="col-md-6 mb-2">
                            <button type="button"  class="btn btn-outline-secondary w-100">
                                <span wire:loading.delay wire:target="" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Reset Defaults
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>

    @section('JS')
        @include('livewire.layout.backend.inc.js')
    @endsection
</main>
