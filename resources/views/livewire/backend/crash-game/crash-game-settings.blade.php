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

                            <!-- Add this section AFTER the "Speed Control Settings" section in your blade file -->

                            <!-- ✅ NEW SECTION: Bet Pool & Commission Settings -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">
                                        <i class="fas fa-piggy-bank me-2"></i>Bet Pool & Commission Settings
                                    </h5>
                                </div>

                                <!-- Commission Type -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Commission Type</label>
                                    <select wire:model="commission_type" class="form-select">
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed">Fixed Amount</option>
                                    </select>
                                    @error('commission_type') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">How commission is calculated from bet pool</small>
                                </div>

                                <!-- Commission Rate (Percentage) -->
                                @if($commission_type === 'percentage')
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Admin Commission Rate (%)</label>
                                    <input type="number" step="0.01" wire:model="admin_commission_rate" class="form-control">
                                    @error('admin_commission_rate') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">
                                        Example: 10% of ৳1000 = ৳{{ number_format($this->getEstimatedCommission(), 2) }}
                                    </small>
                                </div>
                                @endif

                                <!-- Fixed Commission Amount -->
                                @if($commission_type === 'fixed')
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Fixed Commission Amount (৳)</label>
                                    <input type="number" step="0.01" wire:model="fixed_commission_amount" class="form-control">
                                    @error('fixed_commission_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">Fixed amount deducted from every game</small>
                                </div>
                                @endif

                                <!-- Min Pool Amount -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Minimum Pool Amount (৳)</label>
                                    <input type="number" step="0.01" wire:model="min_pool_amount" class="form-control">
                                    @error('min_pool_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">Minimum bet pool required to start game</small>
                                </div>

                                <!-- Max Payout Ratio -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Maximum Payout Ratio</label>
                                    <select wire:model="max_payout_ratio" class="form-select">
                                        <option value="0.70">70% - Very Safe</option>
                                        <option value="0.80">80% - Safe</option>
                                        <option value="0.90">90% - Balanced</option>
                                        <option value="0.95">95% - Risky</option>
                                        <option value="1.00">100% - Very Risky</option>
                                    </select>
                                    @error('max_payout_ratio') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">
                                        Max payout from pool: ৳{{ number_format($this->getMaxSafePayout(), 2) }} (from ৳1000 pool)
                                    </small>
                                </div>

                                <!-- Enable Dynamic Crash -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Enable Dynamic Crash Point</label>
                                    <select wire:model="enable_dynamic_crash" class="form-select">
                                        <option value="1">Yes - Crash increases on cashout</option>
                                        <option value="0">No - Fixed crash point</option>
                                    </select>
                                    @error('enable_dynamic_crash') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">Crash point increases when players cash out</small>
                                </div>

                                <!-- Crash Increase Rate -->
                                @if($enable_dynamic_crash)
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Crash Increase Per Cashout</label>
                                    <input type="number" step="0.01" wire:model="crash_increase_per_cashout" class="form-control">
                                    @error('crash_increase_per_cashout') <span class="text-danger small">{{ $message }}</span> @enderror
                                    <small class="text-muted">
                                        How much crash point increases per player cashout (e.g., 0.50x)
                                    </small>
                                </div>
                                @endif

                                <!-- Pool Safety Preview -->
                                <div class="col-12 mt-3">
                                    <div class="card bg-light border-primary">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-shield-alt me-2 text-primary"></i>Pool Safety Calculator
                                            </h6>
                                            <div class="row text-center">
                                                <div class="col-md-3">
                                                    <small class="text-muted d-block">Sample Bet Pool</small>
                                                    <strong class="text-primary h5">৳1,000</strong>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted d-block">Commission</small>
                                                    <strong class="text-warning h5">
                                                        ৳{{ number_format($this->getEstimatedCommission(), 2) }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted d-block">Available Pool</small>
                                                    <strong class="text-info h5">
                                                        ৳{{ number_format(1000 - $this->getEstimatedCommission(), 2) }}
                                                    </strong>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted d-block">Max Safe Payout</small>
                                                    <strong class="text-success h5">
                                                        ৳{{ number_format($this->getMaxSafePayout(), 2) }}
                                                    </strong>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="alert alert-info mb-0 mt-3">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>How it works:</strong>
                                                <ul class="mb-0 mt-2">
                                                    <li>Total bet pool is collected from all players</li>
                                                    <li>Commission ({{ $commission_type === 'percentage' ? $admin_commission_rate . '%' : '৳' . number_format($fixed_commission_amount, 2) }}) is deducted first</li>
                                                    <li>Crash point is calculated to ensure max payout ≤ {{ ($max_payout_ratio * 100) }}% of available pool</li>
                                                    <li>If players cash out early, crash point increases ({{ $enable_dynamic_crash ? 'ENABLED' : 'DISABLED' }})</li>
                                                    <li>Admin is always protected from losses</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Add this section in crash-game-settings.blade.php -->
<!-- Place it AFTER the "Bet Pool & Commission Settings" section -->

<!-- ===== 🆕 ROLLOVER CONFIGURATION ===== -->
<div class="row mb-4">
    <div class="col-12">
        <h5 class="border-bottom pb-2">
            <i class="fas fa-recycle me-2"></i>Pool Rollover Configuration
        </h5>
        <p class="text-muted small mb-3">
            <i class="fas fa-info-circle me-1"></i>
            Configure how remaining pool amount rolls over to next game
        </p>
    </div>

    <!-- Enable Rollover -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Enable Pool Rollover</label>
        <select wire:model="enable_pool_rollover" class="form-select">
            <option value="1">Yes - Enable rollover</option>
            <option value="0">No - Keep all remaining pool</option>
        </select>
        @error('enable_pool_rollover') <span class="text-danger small">{{ $message }}</span> @enderror
        <small class="text-muted">
            When enabled, unused pool amount carries to next game
        </small>
    </div>

    <!-- Include Commission -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Rollover Includes Commission</label>
        <select wire:model="rollover_includes_commission" class="form-select" {{ !$enable_pool_rollover ? 'disabled' : '' }}>
            <option value="0">No - Commission goes to admin</option>
            <option value="1">Yes - Commission also rolls over</option>
        </select>
        @error('rollover_includes_commission') <span class="text-danger small">{{ $message }}</span> @enderror
        <small class="text-muted">
            Include commission in rollover calculation
        </small>
    </div>

    <!-- Rollover Percentage -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Rollover Percentage (%)</label>
        <select wire:model="rollover_percentage" class="form-select" {{ !$enable_pool_rollover ? 'disabled' : '' }}>
            <option value="25.00">25% - Keep most profit</option>
            <option value="50.00">50% - Balanced</option>
            <option value="75.00">75% - Build pool faster</option>
            <option value="100.00">100% - Full rollover</option>
        </select>
        @error('rollover_percentage') <span class="text-danger small">{{ $message }}</span> @enderror
        <small class="text-muted">
            What percentage of remaining pool rolls over
        </small>
    </div>

    <!-- Min Rollover Amount -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Minimum Rollover Amount (৳)</label>
        <input type="number" step="0.01" wire:model="min_rollover_amount" class="form-control" {{ !$enable_pool_rollover ? 'disabled' : '' }}>
        @error('min_rollover_amount') <span class="text-danger small">{{ $message }}</span> @enderror
        <small class="text-muted">
            Don't rollover if remaining amount is below this
        </small>
    </div>

    <!-- Max Rollover Amount -->
    <div class="col-md-6 mb-3">
        <label class="form-label">Maximum Rollover Amount (৳)</label>
        <input type="number" step="0.01" wire:model="max_rollover_amount" class="form-control" {{ !$enable_pool_rollover ? 'disabled' : '' }}>
        @error('max_rollover_amount') <span class="text-danger small">{{ $message }}</span> @enderror
        <small class="text-muted">
            Cap rollover at this amount to prevent huge pools
        </small>
    </div>

    <!-- Rollover Preview -->
    <div class="col-12 mt-3">
        <div class="card {{ $enable_pool_rollover ? 'border-success' : 'border-secondary' }}">
            <div class="card-header {{ $enable_pool_rollover ? 'bg-success' : 'bg-secondary' }} text-white">
                <h6 class="mb-0">
                    <i class="fas fa-calculator me-2"></i>Rollover Calculator
                </h6>
            </div>
            <div class="card-body">
                @if($enable_pool_rollover)
                    @php
                        // Sample calculation
                        $samplePool = 1000;
                        $sampleCommission = ($samplePool * $admin_commission_rate) / 100;
                        $samplePayout = 600; // Assume 60% paid out
                        $sampleRemaining = $samplePool - $sampleCommission - $samplePayout;

                        $rolloverBase = $sampleRemaining;
                        if ($rollover_includes_commission) {
                            $rolloverBase += $sampleCommission;
                        }

                        $calculatedRollover = ($rolloverBase * $rollover_percentage) / 100;

                        // Apply min/max
                        if ($calculatedRollover < $min_rollover_amount) {
                            $finalRollover = 0;
                            $reason = "Below minimum (৳{$min_rollover_amount})";
                        } elseif ($calculatedRollover > $max_rollover_amount) {
                            $finalRollover = $max_rollover_amount;
                            $reason = "Capped at maximum";
                        } else {
                            $finalRollover = $calculatedRollover;
                            $reason = "Within limits";
                        }

                        $adminKeeps = $rolloverBase - $finalRollover + $sampleCommission;
                    @endphp

                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="p-2 bg-light rounded mb-2">
                                <small class="text-muted d-block">Sample Pool</small>
                                <strong class="text-primary">৳{{ number_format($samplePool, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2 bg-light rounded mb-2">
                                <small class="text-muted d-block">Commission</small>
                                <strong class="text-warning">৳{{ number_format($sampleCommission, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2 bg-light rounded mb-2">
                                <small class="text-muted d-block">Paid to Winners</small>
                                <strong class="text-info">৳{{ number_format($samplePayout, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2 bg-light rounded mb-2">
                                <small class="text-muted d-block">Remaining</small>
                                <strong class="text-success">৳{{ number_format($sampleRemaining, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row text-center">
                        <div class="col-md-4">
                            <div class="p-3 bg-success bg-opacity-10 rounded border border-success">
                                <i class="fas fa-arrow-circle-right text-success fa-2x mb-2"></i>
                                <h6 class="text-muted mb-1">Rolls Over to Next Game</h6>
                                <h4 class="text-success mb-0">৳{{ number_format($finalRollover, 2) }}</h4>
                                <small class="text-muted">{{ $reason }}</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-primary bg-opacity-10 rounded border border-primary">
                                <i class="fas fa-wallet text-primary fa-2x mb-2"></i>
                                <h6 class="text-muted mb-1">Admin Keeps</h6>
                                <h4 class="text-primary mb-0">৳{{ number_format($adminKeeps, 2) }}</h4>
                                <small class="text-muted">Commission + Kept pool</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-info bg-opacity-10 rounded border border-info">
                                <i class="fas fa-chart-pie text-info fa-2x mb-2"></i>
                                <h6 class="text-muted mb-1">Admin Profit %</h6>
                                <h4 class="text-info mb-0">{{ number_format(($adminKeeps / $samplePool) * 100, 1) }}%</h4>
                                <small class="text-muted">Of total pool</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-success mt-3 mb-0">
                        <i class="fas fa-check-circle me-2"></i>
                        <strong>Calculation Breakdown:</strong>
                        <ol class="mb-0 mt-2">
                            <li>Remaining pool: ৳{{ number_format($sampleRemaining, 2) }}</li>
                            @if($rollover_includes_commission)
                            <li>Add commission: ৳{{ number_format($sampleCommission, 2) }} = ৳{{ number_format($rolloverBase, 2) }}</li>
                            @endif
                            <li>Apply {{ $rollover_percentage }}%: ৳{{ number_format($calculatedRollover, 2) }}</li>
                            <li>Check limits (min: ৳{{ number_format($min_rollover_amount, 2) }}, max: ৳{{ number_format($max_rollover_amount, 2) }})</li>
                            <li><strong>Final rollover: ৳{{ number_format($finalRollover, 2) }}</strong></li>
                        </ol>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-ban fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Rollover Disabled</h5>
                        <p class="text-muted mb-0">
                            All remaining pool amount will be kept by admin.<br>
                            Enable rollover to carry pool to next game.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Strategy Recommendations -->
    <div class="col-12 mt-3">
        <div class="accordion" id="rolloverStrategies">
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#strategyCollapse">
                        <i class="fas fa-lightbulb me-2"></i>Rollover Strategies
                    </button>
                </h2>
                <div id="strategyCollapse" class="accordion-collapse collapse" data-bs-parent="#rolloverStrategies">
                    <div class="accordion-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card border-warning mb-3">
                                    <div class="card-header bg-warning text-white">
                                        <strong>Conservative</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="small mb-0">
                                            <li>Enable: Yes</li>
                                            <li>Percentage: 25-50%</li>
                                            <li>Include Commission: No</li>
                                            <li>Min: ৳50, Max: ৳1000</li>
                                        </ul>
                                        <hr>
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Maximize admin profit, slow pool growth
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-success mb-3">
                                    <div class="card-header bg-success text-white">
                                        <strong>Balanced (Recommended)</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="small mb-0">
                                            <li>Enable: Yes</li>
                                            <li>Percentage: 75-100%</li>
                                            <li>Include Commission: No</li>
                                            <li>Min: ৳10, Max: ৳5000</li>
                                        </ul>
                                        <hr>
                                        <small class="text-muted">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Good balance between profit and player excitement
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="card border-primary mb-3">
                                    <div class="card-header bg-primary text-white">
                                        <strong>Aggressive Growth</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="small mb-0">
                                            <li>Enable: Yes</li>
                                            <li>Percentage: 100%</li>
                                            <li>Include Commission: Yes</li>
                                            <li>Min: ৳1, Max: ৳10000</li>
                                        </ul>
                                        <hr>
                                        <small class="text-muted">
                                            <i class="fas fa-chart-line me-1"></i>
                                            Rapid pool growth, higher player attraction
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update your Livewire component properties -->
{{--
In CrashGameSettings.php, add these properties:

public $enable_pool_rollover;
public $rollover_percentage;
public $min_rollover_amount;
public $max_rollover_amount;
public $rollover_includes_commission;

And in loadSettings():

$this->enable_pool_rollover = $this->settings->enable_pool_rollover ?? true;
$this->rollover_percentage = $this->settings->rollover_percentage ?? 100.00;
$this->min_rollover_amount = $this->settings->min_rollover_amount ?? 10.00;
$this->max_rollover_amount = $this->settings->max_rollover_amount ?? 10000.00;
$this->rollover_includes_commission = $this->settings->rollover_includes_commission ?? false;

And in updateSettings() validation:

'enable_pool_rollover' => 'boolean',
'rollover_percentage' => 'required|numeric|min:0|max:100',
'min_rollover_amount' => 'required|numeric|min:0',
'max_rollover_amount' => 'required|numeric|min:0',
'rollover_includes_commission' => 'boolean',

And in update array:

'enable_pool_rollover' => $this->enable_pool_rollover,
'rollover_percentage' => $this->rollover_percentage,
'min_rollover_amount' => $this->min_rollover_amount,
'max_rollover_amount' => $this->max_rollover_amount,
'rollover_includes_commission' => $this->rollover_includes_commission,
--}}

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
