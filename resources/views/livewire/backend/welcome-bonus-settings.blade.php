<main>
    @section('title')
        <title>Admin | Welcome Bonus Settings</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Welcome Bonus Settings</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <div class="btn-group">
                    <button type="button" class="btn btn-primary px-4">
                        <i class="bi bi-share me-2"></i>Welcome Bonus Settings
                    </button>
                </div>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card shadow p-4">
            <h3 class="mb-4">Welcome Bonus Settings</h3>

            @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit.prevent="save">
                <div class="mb-3">
                    <label class="form-label">Bonus Amount</label>
                    <input type="number" class="form-control" wire:model="amount" min="0">
                    @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="isActive" wire:model="is_active">
                    <label class="form-check-label" for="isActive">Activate Welcome Bonus</label>
                </div>

                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>

    </main>

    @section('JS')
        @include('livewire.layout.backend.inc.js')
    @endsection
</main>
