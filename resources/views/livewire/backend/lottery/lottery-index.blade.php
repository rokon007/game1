<main>
    @section('title')
        <title>Admin | লটারি তালিকা</title>
    @endsection

    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
            .status-badge {
                font-size: 0.8rem;
                padding: 5px 10px;
                border-radius: 20px;
            }
            .badge-active {
                background-color: #198754;
                color: white;
            }
            .badge-completed {
                background-color: #0d6efd;
                color: white;
            }
            .badge-cancelled {
                background-color: #dc3545;
                color: white;
            }
            .action-btn {
                width: 36px;
                height: 36px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                margin: 0 3px;
            }
        </style>
    @endsection

    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
            <div class="breadcrumb-title pe-3">লটারি ব্যবস্থাপনা</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">লটারি তালিকা</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.lottery.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus"></i> নতুন লটারি
                </a>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">লটারি তালিকা</h4>
                    <div class="d-flex gap-3">
                        <div class="position-relative">
                            <input type="text" class="form-control ps-5" wire:model.live="search" placeholder="লটারি খুঁজুন...">
                            <i class="bx bx-search position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                        </div>
                        <select class="form-select" wire:model.live="status" style="width: 150px;">
                            <option value="">সব স্ট্যাটাস</option>
                            <option value="active">সক্রিয়</option>
                            <option value="completed">সম্পন্ন</option>
                            <option value="cancelled">বাতিল</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-striped align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="20%">নাম</th>
                                <th width="10%">মূল্য</th>
                                <th width="15%">ড্র এর তারিখ</th>
                                <th width="10%">টিকিট</th>
                                <th width="10%">আয়</th>
                                <th width="10%">স্ট্যাটাস</th>
                                <th width="25%">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lotteries as $lottery)
                                <tr>
                                    <td>
                                        <strong>{{ $lottery->name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">৳{{ number_format($lottery->price, 2) }}</span>
                                    </td>
                                    <td>
                                        {{ $lottery->draw_date->format('d/m/Y') }}<br>
                                        <small class="text-muted">{{ $lottery->draw_date->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $lottery->getTotalTicketsSold() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">৳{{ number_format($lottery->getTotalRevenue(), 2) }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $badgeClass = [
                                                'active' => 'badge-active',
                                                'completed' => 'badge-completed',
                                                'cancelled' => 'badge-cancelled'
                                            ][$lottery->status] ?? 'badge-secondary';
                                        @endphp
                                        <span class="status-badge {{ $badgeClass }}">
                                            {{ $lottery->status === 'active' ? 'সক্রিয়' : ($lottery->status === 'completed' ? 'সম্পন্ন' : 'বাতিল') }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            @if($lottery->status === 'active')
                                                <button class="btn btn-warning btn-sm action-btn"
                                                        onclick="startLiveDraw({{ $lottery->id }})"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="লাইভ ড্র শুরু করুন">
                                                    <i class="bx bx-play"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm action-btn"
                                                        wire:click="conductDraw({{ $lottery->id }})"
                                                        onclick="confirm('আপনি কি নিশ্চিত?') || event.stopImmediatePropagation()"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="সরাসরি ড্র করুন">
                                                    <i class="bx bx-bolt"></i>
                                                </button>
                                            @endif
                                            <a href="{{ route('admin.lottery.show', $lottery->id) }}"
                                               class="btn btn-info btn-sm action-btn"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="বিস্তারিত দেখুন">
                                                <i class="bx bx-show"></i>
                                            </a>
                                            <a href="{{route('admin.lottery.edit', $lottery->id)}}"
                                               class="btn btn-primary btn-sm action-btn"
                                               data-bs-toggle="tooltip"
                                               data-bs-placement="top"
                                               title="এডিট করুন">
                                                <i class="bx bx-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bx bx-package text-muted" style="font-size: 3rem;"></i>
                                            <h5 class="mt-3 text-muted">কোন লটারি পাওয়া যায়নি</h5>
                                            <a href="{{ route('admin.lottery.create') }}" class="btn btn-primary mt-2">
                                                নতুন লটারি তৈরি করুন
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($lotteries->hasPages())
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Showing {{ $lotteries->firstItem() }} to {{ $lotteries->lastItem() }} of {{ $lotteries->total() }} entries
                        </div>
                        <div>
                            {{ $lotteries->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function startLiveDraw(lotteryId) {
            Swal.fire({
                title: 'লাইভ ড্র শুরু করবেন?',
                text: "এটি সব ইউজারের কাছে দেখানো হবে!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'হ্যাঁ, শুরু করুন!',
                cancelButtonText: 'বাতিল'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.dispatch('startLiveDraw', lotteryId);
                    @this.call('broadcastDrawStart', lotteryId);
                }
            });
        }
    </script>

    @section('JS')
        @include('livewire.layout.backend.inc.js')
        <script src="{{ asset('backend/assets/js/index4.js') }}"></script>
        <script>
            // Initialize tooltips
            document.addEventListener('livewire:initialized', () => {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            });
        </script>
    @endsection
</main>
