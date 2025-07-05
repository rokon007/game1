<main>
    @section('title')
        <title>Admin | Transictions </title>
    @endsection
    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
              <!-- Select2 CSS -->
                <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

                <!-- Select2 JS -->
                <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <style>
                .rounded-md {
                    border-radius: 0.375rem; /* 6px */
                }

                .modal {
                    transition: opacity 0.3s ease;
                }
                .fa-spinner {
                    color: #4a5568;
                }
                .gameOver-container {
                    position: relative;
                    /* height: 170px; */
                    overflow: hidden;
                }
                .gameOver-container .gameOver-text {
                    position: absolute;
                    top: 50%; /* কন্টেইনারের মাঝখানে সেট করা */
                    left: 50%;
                    transform: translate(-50%, -50%) rotate(-15deg); /* হালকা ঘুরিয়ে দেওয়া */
                    font-size: 36px; /* টেক্সটের আকার */
                    color:black; /* স্টাম্পের জন্য হালকা লাল রঙ */
                    font-weight: bold;
                    text-transform: uppercase; /* টেক্সটকে বড়হাতের করে দেওয়া */
                    white-space: nowrap; /* এক লাইনে রাখার জন্য */
                    pointer-events: none; /* টেক্সটকে ক্লিক করা নিষিদ্ধ */
                    background-color: hsl(45, 100%, 51%);
                    border: 1px solid black; /* স্টাম্পের বর্ডার */
                    border-radius: 50%; /* গোলাকার আকৃতি */
                    padding: 20px 40px; /* স্টাম্পের জায়গা ঠিক করার জন্য প্যাডিং */
                    box-shadow: 0 0 15px rgba(255, 0, 0, 0.3); /* হালকা শেডো */

                }

                .pu-select option {
                    padding: 10px;
                    margin: 2px 0;
                    border-radius: 5px;
                }
                .pu-select option:hover {
                    background-color: #f8f9fa;
                }
                .pu-select:focus {
                    border-color: #86b7fe;
                    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
                }
                .select2-container--default .select2-selection--single {
                    height: auto;
                    padding: 0.5rem;
                }
                .select2-container--default .select2-selection--single .select2-selection__rendered {
                    line-height: normal;
                }
                .select2-container--default .select2-selection__arrow {
                    height: 100%;
                }
            </style>
    @endsection
    <div class="page-content">
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <div class="breadcrumb-title pe-3">User</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">User Transictions</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
            <h5 class="mb-0">Transactions for {{ $user->name }} ({{ $user->unique_id }})</h5>
                <div class="card-header py-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <input type="date" wire:model.live="startDate" class="form-control" placeholder="Start Date">
                        </div>
                        <div class="col-md-1 text-center">
                            <h5 class="mt-2">To</h5>
                        </div>
                        <div class="col-md-3">
                            <input type="date" wire:model.live="endDate" class="form-control" placeholder="End Date">
                        </div>
                        <div class="col-md-3">
                            <select wire:model.live="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="credit">Credit</option>
                                <option value="debit">Debit</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table align-middle">
                        <thead class="table-secondary">
                            <tr>
                                <th>#</th>
                                <th>Type</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $index => $txn)
                                <tr>
                                    <td>{{ $transactions->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3 cursor-pointer">
                                            <span class="noti-icon text-white">
                                                @if($txn->type === 'credit')
                                                    <i class="ti ti-arrow-down-left"></i>
                                                @elseif($txn->type === 'debit')
                                                    <i class="ti ti-arrow-up-right"></i>
                                                @else
                                                    <i class="ti ti-bell"></i>
                                                @endif
                                            </span>
                                            <div class="">
                                                <span class="badge bg-{{ $txn->type === 'credit' ? 'success' : 'danger' }}">
                                                    {{ ucfirst($txn->type) }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $txn->created_at->format('d M Y h:i A') }}</td>
                                    <td>{{ number_format($txn->amount, 2) }}</td>
                                    <td>{{ $txn->details }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No transactions found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                     <div class="mt-2">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('JS')
         @include('livewire.layout.backend.inc.js')
         <script src="{{ asset('backend/assets/js/index4.js') }}"></script>
    @endsection
</main>
