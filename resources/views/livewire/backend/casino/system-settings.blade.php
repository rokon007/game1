<main>
    @section('title')
        <title>Admin | Spin Settings</title>
    @endsection

    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            .form-section {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 0 15px rgba(0,0,0,0.05);
                margin-bottom: 20px;
                padding: 20px;
            }
            .form-section-header {
                border-bottom: 1px solid #eee;
                padding-bottom: 15px;
                margin-bottom: 20px;
            }
            .pool-display {
                background: linear-gradient(135deg, #f1c40f, #f39c12);
                color: #2c3e50;
                border-radius: 50px;
                padding: 12px 25px;
                font-weight: bold;
                box-shadow: 0 5px 15px rgba(241, 196, 15, 0.4);
            }
            .stat-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 12px;
                padding: 20px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
                margin-bottom: 15px;
            }
            .stat-card h4 {
                font-size: 2rem;
                font-weight: bold;
                margin-bottom: 5px;
            }
            .stat-card small {
                opacity: 0.9;
            }
            .preset-btn {
                padding: 8px 20px;
                border-radius: 20px;
                border: 2px solid;
                font-weight: 600;
                transition: all 0.3s;
            }
            .preset-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            }
            .key-highlight {
                background: #f8f9fa;
                padding: 4px 8px;
                border-radius: 4px;
                font-family: 'Courier New', monospace;
                font-size: 0.9rem;
                color: #495057;
            }
        </style>
    @endsection

    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
            <div class="breadcrumb-title pe-3">Spin Settings</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="#">Spin Settings</a></li>
                    </ol>
                </nav>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="container py-4">
            <h4 class="mb-3 fw-bold text-primary"><i class="fas fa-cog me-2"></i>Spin Settings & Configuration</h4>

            <!-- Pool Display -->
            <div class="card mb-4 shadow-sm">
                <div wire:poll.1s="updatePoolAmount" class="text-center mb-4 mt-4">
                    <div class="pool-display d-inline-block">
                        <i class="fas fa-crown me-2"></i>
                        <strong>CURRENT POOL:</strong>
                        <span id="poolAmount">{{ number_format($poolAmount) }}</span>
                        <small class="ms-1">credits</small>
                    </div>
                </div>
            </div>

            <!-- Stats Dashboard -->
            <div class="row mb-4" wire:poll.5s="calculateStats">
                <div class="col-md-3">
                    <div class="stat-card">
                        <h4>{{ $stats['rtp'] ?? 0 }}%</h4>
                        <small><i class="fas fa-chart-line me-1"></i>RTP (Return to Player)</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h4>{{ $stats['house_edge'] ?? 0 }}%</h4>
                        <small><i class="fas fa-home me-1"></i>House Edge</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h4>{{ $stats['jackpot_chance'] ?? 0 }}%</h4>
                        <small><i class="fas fa-dice me-1"></i>Jackpot Chance</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <h4>{{ number_format($stats['available_pool'] ?? 0) }}</h4>
                        <small><i class="fas fa-coins me-1"></i>Available Pool</small>
                    </div>
                </div>
            </div>

            <!-- Quick Presets -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light fw-semibold">
                    <i class="fas fa-magic me-2"></i>Quick Presets
                </div>
                <div class="card-body">
                    <div class="d-flex gap-3 flex-wrap">
                        <button wire:click="applyPreset('easy')" class="preset-btn btn btn-success">
                            <i class="fas fa-smile me-1"></i>Easy Mode (30% Win)
                        </button>
                        <button wire:click="applyPreset('normal')" class="preset-btn btn btn-primary">
                            <i class="fas fa-balance-scale me-1"></i>Normal Mode (20% Win)
                        </button>
                        <button wire:click="applyPreset('hard')" class="preset-btn btn btn-danger">
                            <i class="fas fa-fire me-1"></i>Hard Mode (15% Win)
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        <i class="fas fa-info-circle me-1"></i>Presets automatically configure win chance, commission, and jackpot probability
                    </small>
                </div>
            </div>

            <!-- Add New Setting -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light fw-semibold">
                    <i class="fas fa-plus-circle me-2"></i>Add New Setting
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" wire:model="newKey" class="form-control" placeholder="Setting Key (e.g. site_name)">
                        </div>
                        <div class="col-md-5">
                            <input type="text" wire:model="newValue" class="form-control" placeholder="Value (e.g. HousieBlitz)">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-success w-100" wire:click="addSetting">
                                <i class="fas fa-save me-1"></i>Add
                            </button>
                        </div>
                    </div>
                    @error('newKey') <small class="text-danger">{{ $message }}</small> @enderror
                    @error('newValue') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            <!-- Settings Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-semibold">
                    <i class="fas fa-list me-2"></i>All Settings
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">#</th>
                                    <th>Key</th>
                                    <th>Value</th>
                                    <th width="200">Description</th>
                                    <th width="120">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($settings as $index => $setting)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <span class="key-highlight">{{ $setting['key'] }}</span>
                                            @if(in_array($setting['key'], ['jackpot_chance_percent', 'minimum_pool_reserve', 'max_win_percentage']))
                                                <span class="badge bg-success ms-1">NEW</span>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="text"
                                                wire:change="saveSetting({{ $setting['id'] }}, $event.target.value)"
                                                class="form-control form-control-sm"
                                                value="{{ $setting['value'] }}"
                                                style="max-width: 200px;">
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                @switch($setting['key'])
                                                    @case('min_bet')
                                                        Minimum bet amount
                                                        @break
                                                    @case('max_bet')
                                                        Maximum bet amount
                                                        @break
                                                    @case('win_chance_percent')
                                                        Win probability (0-100%)
                                                        @break
                                                    @case('admin_commission')
                                                        Commission on wins (%)
                                                        @break
                                                    @case('jackpot_limit')
                                                        Pool size to activate jackpot
                                                        @break
                                                    @case('jackpot_chance_percent')
                                                        <strong>Jackpot win probability (%)</strong>
                                                        @break
                                                    @case('minimum_pool_reserve')
                                                        <strong>Minimum pool reserve</strong>
                                                        @break
                                                    @case('max_win_percentage')
                                                        <strong>Max win % of pool</strong>
                                                        @break
                                                    @default
                                                        Custom setting
                                                @endswitch
                                            </small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-danger"
                                                wire:click="deleteSetting({{ $setting['id'] }})"
                                                onclick="return confirm('Delete this setting?')">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No settings found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4 shadow-sm">
                <div class="card-header bg-info text-white fw-semibold">
                    <i class="fas fa-info-circle me-2"></i>Important Information
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li><strong>RTP (Return to Player):</strong> Percentage of bets returned to players over time</li>
                        <li><strong>House Edge:</strong> Casino's statistical advantage (100% - RTP)</li>
                        <li><strong>Jackpot Chance:</strong> Probability of winning jackpot when pool ≥ limit</li>
                        <li><strong>Pool Reserve:</strong> Minimum balance kept in pool to ensure payouts</li>
                        <li><strong>Max Win %:</strong> Maximum single win as percentage of available pool</li>
                    </ul>
                </div>
            </div>

            <!-- Toast Alert -->
            <script>
                document.addEventListener('livewire:init', () => {
                    Livewire.on('toast', data => {
                        const type = data.type || 'success';
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            timer: 2500,
                            icon: type,
                            title: data.message,
                            showConfirmButton: false
                        });
                    });
                });
            </script>
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
