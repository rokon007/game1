<main>
    @section('title')
        <title>Admin - Crash Game Settings</title>
    @endsection

    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />

    @endsection

    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
            <div class="breadcrumb-title pe-3">Crash Game Settings</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="#">Crash Game Settings</a></li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="container py-4">
            <div>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-cog me-2"></i>Crash Game Settings
                        </h4>
                    </div>
                    <div class="card-body">
                        @if (session()->has('message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if (session()->has('info'))
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Environment Information Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-server me-2"></i>Environment Information
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>Environment:</strong>
                                                <span class="badge bg-{{ $is_production ? 'success' : 'warning' }}">
                                                    {{ $environment_info['environment'] ?? app()->environment() }}
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Shell Execution:</strong>
                                                <span class="badge bg-{{ $can_execute ? 'success' : 'danger' }}">
                                                    {{ $can_execute ? 'Available' : 'Disabled' }}
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>Platform:</strong>
                                                <span class="badge bg-secondary">
                                                    {{ $environment_info['is_windows'] ?? (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? 'Windows' : 'Linux') }}
                                                </span>
                                            </div>
                                        </div>

                                        @if(!$is_production)
                                        <div class="mt-3 alert alert-warning">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Local Development Mode:</strong> Some features may be limited.
                                            Full functionality will be available in production (Coolify/VPS).
                                        </div>
                                        @else
                                        <div class="mt-3 alert alert-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <strong>Production Environment:</strong> All features are available.
                                            Game process management will work perfectly.
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Game Process Control -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h5 class="border-bottom pb-2">
                                    <i class="fas fa-play-circle me-2"></i>Game Process Control
                                </h5>
                            </div>

                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="card-title">Current Status</h6>
                                        <span class="badge bg-{{ $is_running ? 'success' : 'danger' }} fs-6 p-2 mb-2">
                                            {{ $process_status }}
                                        </span>
                                        @if($process_id)
                                            <p class="mb-1 small text-muted">PID: {{ $process_id }}</p>
                                        @endif
                                        @if($process_started_at)
                                            <p class="mb-0 small text-muted">Started: {{ $process_started_at }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="d-grid gap-2 d-md-flex">
                                    <button type="button" wire:click="startGame"
                                            class="btn btn-success flex-fill"
                                            {{ $is_running || !$can_execute ? 'disabled' : '' }}>
                                        <i class="fas fa-play me-1"></i> Start Game
                                    </button>

                                    <button type="button" wire:click="stopGame"
                                            class="btn btn-warning flex-fill"
                                            {{ !$is_running ? 'disabled' : '' }}>
                                        <i class="fas fa-stop me-1"></i> Stop Game
                                    </button>

                                    <button type="button" wire:click="restartGame"
                                            class="btn btn-info flex-fill">
                                        <i class="fas fa-redo me-1"></i> Restart
                                    </button>

                                    <button type="button" wire:click="forceStopGame"
                                            class="btn btn-danger flex-fill"
                                            {{ !$is_running ? 'disabled' : '' }}>
                                        <i class="fas fa-skull-crossbones me-1"></i> Force Stop
                                    </button>

                                    <button type="button" wire:click="refreshStatus"
                                            class="btn btn-secondary flex-fill">
                                        <i class="fas fa-sync-alt me-1"></i> Refresh
                                    </button>
                                </div>

                                @if(!$can_execute)
                                <div class="mt-2 alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    Shell execution is disabled on your server. You may need to start the game manually via SSH.
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Rest of your form remains the same -->
                        <form wire:submit="updateSettings">
                            <!-- Basic Settings -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">
                                        <i class="fas fa-sliders-h me-2"></i>Basic Settings
                                    </h5>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">House Edge (%)</label>
                                    <input type="number" step="0.01" wire:model="house_edge" class="form-control">
                                    @error('house_edge') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">Recommended: 5-10%</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Bet Waiting Time (seconds)</label>
                                    <input type="number" wire:model="bet_waiting_time" class="form-control">
                                    @error('bet_waiting_time') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Minimum Multiplier</label>
                                    <input type="number" step="0.01" wire:model="min_multiplier" class="form-control">
                                    @error('min_multiplier') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Maximum Multiplier</label>
                                    <input type="number" step="0.01" wire:model="max_multiplier" class="form-control">
                                    @error('max_multiplier') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Minimum Bet Amount</label>
                                    <input type="number" step="0.01" wire:model="min_bet_amount" class="form-control">
                                    @error('min_bet_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Maximum Bet Amount</label>
                                    <input type="number" step="0.01" wire:model="max_bet_amount" class="form-control">
                                    @error('max_bet_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-12 mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" wire:model="is_active" id="isActive">
                                        <label class="form-check-label" for="isActive">Game Active</label>
                                    </div>
                                    <small class="text-muted">If disabled, game cannot be started</small>
                                </div>
                            </div>

                            <!-- Speed Control Settings -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">
                                        <i class="fas fa-tachometer-alt me-2"></i>Multiplier Speed Control
                                    </h5>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Speed Profile</label>
                                    <select wire:model="speed_profile" class="form-select">
                                        <option value="slow">Slow - Smooth & Realistic</option>
                                        <option value="medium">Medium - Balanced</option>
                                        <option value="fast">Fast - Exciting</option>
                                        <option value="custom">Custom - Manual Settings</option>
                                    </select>
                                    @error('speed_profile') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Enable Auto Acceleration</label>
                                    <select wire:model="enable_auto_acceleration" class="form-select">
                                        <option value="1">Yes</option>
                                        <option value="0">No</option>
                                    </select>
                                    <small class="text-muted">Speed increases automatically at higher multipliers</small>
                                    @error('enable_auto_acceleration') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                <!-- Custom Speed Settings -->
                                @if($speed_profile === 'custom')
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Multiplier Increment</label>
                                    <input type="number" step="0.0001" wire:model="multiplier_increment" class="form-control">
                                    @error('multiplier_increment') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">How much multiplier increases each cycle</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Cycle Interval (Milliseconds)</label>
                                    <input type="number" wire:model="multiplier_interval_ms" class="form-control">
                                    @error('multiplier_interval_ms') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">Time between each multiplier update</small>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Max Speed Multiplier</label>
                                    <input type="number" step="0.01" wire:model="max_speed_multiplier" class="form-control">
                                    @error('max_speed_multiplier') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">Auto acceleration stops at this multiplier</small>
                                </div>
                                @endif

                                <!-- Speed Preview -->
                                <div class="col-12 mt-3">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-clock me-2"></i>Speed Preview
                                            </h6>
                                            <div class="row text-center">
                                                <div class="col-md-3">
                                                    <small>1x to 2x</small><br>
                                                    <strong class="text-primary">{{ $this->calculateTime(1, 2) }}</strong>
                                                </div>
                                                <div class="col-md-3">
                                                    <small>1x to 5x</small><br>
                                                    <strong class="text-primary">{{ $this->calculateTime(1, 5) }}</strong>
                                                </div>
                                                <div class="col-md-3">
                                                    <small>1x to 10x</small><br>
                                                    <strong class="text-primary">{{ $this->calculateTime(1, 10) }}</strong>
                                                </div>
                                                <div class="col-md-3">
                                                    <small>1x to 20x</small><br>
                                                    <strong class="text-primary">{{ $this->calculateTime(1, 20) }}</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Settings
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
@endpush

    </div>

    @section('JS')
        @include('livewire.layout.backend.inc.js')
        <script src="{{ asset('backend/assets/js/index4.js') }}"></script>
    @endsection
</main>
