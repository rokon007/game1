<main>
    @section('title')
        <title>Admin | Spin Settings</title>
    @endsection

    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
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
            .ticket-item, .prize-item {
                background: #f9f9f9;
                border-radius: 6px;
                padding: 15px;
                margin-bottom: 15px;
            }
            .pool-display {
                background: linear-gradient(135deg, #f1c40f, #f39c12);
                color: #2c3e50;
                border-radius: 50px;
                padding: 12px 25px;
                font-weight: bold;
                box-shadow: 0 5px 15px rgba(241, 196, 15, 0.4);
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
            {{-- <div class="ms-auto">
                <a href="{{ route('admin.lottery.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bx bx-arrow-back"></i> ফিরে যান
                </a>
            </div> --}}
        </div>
        <!--end breadcrumb-->

        <div class="container py-4">
            <h4 class="mb-3 fw-bold text-primary">⚙️ Spin Settings</h4>

            <div class="card mb-4 shadow-sm">
                <div wire:poll.1s="updatePoolAmount" class="text-center mb-4 mt-4">
                    <div class="pool-display d-inline-block">
                        <i class="fas fa-crown me-2"></i>
                        <strong>JACKPOT:</strong>
                        <span id="poolAmount">{{ number_format($poolAmount) }}</span>
                        <small class="ms-1">credits</small>
                    </div>
                </div>
            </div>

            {{-- Add New Setting --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light fw-semibold">Add New Setting</div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <input type="text" wire:model="newKey" class="form-control" placeholder="Setting Key (e.g. site_name)">
                        </div>
                        <div class="col-md-5">
                            <input type="text" wire:model="newValue" class="form-control" placeholder="Value (e.g. HousieBlitz)">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-success w-100" wire:click="addSetting">Add</button>
                        </div>
                    </div>
                    @error('newKey') <small class="text-danger">{{ $message }}</small> @enderror
                    @error('newValue') <small class="text-danger">{{ $message }}</small> @enderror
                </div>
            </div>

            {{-- Settings Table --}}
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-semibold">All Settings</div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Key</th>
                                <th>Value</th>
                                <th width="120">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($settings as $index => $setting)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="text-muted">{{ $setting['key'] }}</td>
                                    <td>
                                        <input type="text"
                                            wire:change="saveSetting({{ $setting['id'] }}, $event.target.value)"
                                            class="form-control form-control-sm"
                                            value="{{ $setting['value'] }}">
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger"
                                            wire:click="deleteSetting({{ $setting['id'] }})">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No settings found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Toast Alert --}}
            <script>
                document.addEventListener('livewire:init', () => {
                    Livewire.on('toast', data => {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            timer: 2500,
                            icon: 'success',
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
