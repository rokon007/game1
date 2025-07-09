<main>
    @section('title')
        <title>Admin | HajariGame Settings</title>
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
            <div class="breadcrumb-title pe-3">Game Settings</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">HajariGame Configuration</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary">Settings Dashboard</button>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->

        @if (session('message'))
            <div class="col-md-12 text-center">
                <div class="col-md-5 mx-auto">
                    <div class="alert border-0 bg-success alert-dismissible fade show py-2">
                        <div class="d-flex align-items-center">
                            <div class="fs-3 text-white"><i class="bi bi-check-circle-fill"></i></div>
                            <div class="ms-3">
                                <div class="text-white">{{ session('message') }}</div>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif

        <div class="card">
            <div class="card-header bg-transparent border-bottom">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0">HajariGame Global Settings</h5>
                    <div class="ms-auto">
                        <i class="bx bx-cog fs-4 text-primary"></i>
                    </div>
                </div>
                <p class="mb-0 text-muted">Configure game parameters for all HajariGame matches</p>
            </div>

            <div class="card-body">
                <form wire:submit.prevent="saveSettings">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card shadow-none border">
                                <div class="card-body">
                                    <h6 class="mb-3 text-primary">Game Timing Settings</h6>

                                    <div class="mb-3">
                                        <label for="arrangeTimeSeconds" class="form-label">Card Arrangement Time</label>
                                        <div class="input-group">
                                            <input type="number" min="60" max="600" class="form-control"
                                                   wire:model="arrangeTimeSeconds" id="arrangeTimeSeconds">
                                            <span class="input-group-text">seconds</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">
                                                Current: {{ floor($arrangeTimeSeconds / 60) }} min {{ $arrangeTimeSeconds % 60 }} sec
                                            </small>
                                            <small class="text-muted">Range: 1-10 minutes</small>
                                        </div>
                                        @error('arrangeTimeSeconds')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card shadow-none border">
                                <div class="card-body">
                                    <h6 class="mb-3 text-primary">Commission Settings</h6>

                                    <div class="mb-3">
                                        <label for="adminCommissionPercentage" class="form-label">Admin Commission</label>
                                        <div class="input-group">
                                            <input type="number" min="0" max="50" step="0.1" class="form-control"
                                                   wire:model="adminCommissionPercentage" id="adminCommissionPercentage">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <div class="d-flex justify-content-between mt-1">
                                            <small class="text-muted">
                                                Current: {{ number_format($adminCommissionPercentage, 1) }}%
                                            </small>
                                            <small class="text-muted">Range: 0-50%</small>
                                        </div>
                                        @error('adminCommissionPercentage')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Settings Preview -->
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary bg-opacity-10 border-bottom border-primary">
                            <h6 class="mb-0 text-white"><i class="bi bi-eye-fill me-2"></i>Settings Preview</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                            <i class="bi bi-stopwatch text-white fs-4"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 text-muted">Arrangement Time</p>
                                            <h5 class="mb-0">
                                                {{ floor($arrangeTimeSeconds / 60) }}:{{ str_pad($arrangeTimeSeconds % 60, 2, '0', STR_PAD_LEFT) }} minutes
                                            </h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded me-3">
                                            <i class="bi bi-percent text-white fs-4"></i>
                                        </div>
                                        <div>
                                            <p class="mb-0 text-muted">Admin Commission</p>
                                            <h5 class="mb-0">{{ number_format($adminCommissionPercentage, 1) }}%</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 p-3 bg-light rounded">
                                <h6 class="mb-3 text-center">Example Calculation for 4-Player Game</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <div class="p-2 bg-white rounded border text-center">
                                            <p class="mb-1 small text-muted">Entry Fee</p>
                                            <h5 class="mb-0 text-success">৳100</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 bg-white rounded border text-center">
                                            <p class="mb-1 small text-muted">Total Prize Pool</p>
                                            <h5 class="mb-0 text-primary">৳400</h5>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-2 bg-white rounded border text-center">
                                            <p class="mb-1 small text-muted">Admin Commission</p>
                                            <h5 class="mb-0 text-danger">৳{{ number_format(400 * $adminCommissionPercentage / 100, 2) }}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 p-3 bg-white rounded border border-success border-opacity-50 text-center">
                                    <p class="mb-1 small text-muted">Winner Receives</p>
                                    <h4 class="mb-0 text-success">৳{{ number_format(400 - (400 * $adminCommissionPercentage / 100), 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-md-6 mb-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <span wire:loading.delay.long wire:target="saveSettings" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                <i class="bi bi-save-fill me-2"></i>Save Settings
                            </button>
                        </div>
                        <div class="col-md-6 mb-2">
                            <button type="button" wire:click="resetToDefaults" class="btn btn-outline-secondary w-100">
                                <span wire:loading.delay.long wire:target="resetToDefaults" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                <i class="bi bi-arrow-counterclockwise me-2"></i>Reset to Defaults
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
