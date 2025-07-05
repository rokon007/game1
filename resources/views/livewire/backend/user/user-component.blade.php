<main>
    @section('title')
        <title>Admin | User </title>
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
                        <li class="breadcrumb-item active" aria-current="page">User Management</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0">User Details</h5>
                    <div class="ms-auto position-relative">
                        <div class="position-absolute top-50 translate-middle-y search-icon px-3"><i class="bi bi-search"></i></div>
                        <input class="form-control ps-5" type="text" wire:model.live="search" placeholder="search">
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table align-middle">
                       <thead class="table-secondary">
                         <tr>
                          <th>#</th>
                          <th>User</th>
                          <th>Email</th>
                          <th>Mobile</th>
                          <th>Status</th>
                          <th>Location</th>
                          <th>Actions</th>
                         </tr>
                       </thead>
                       <tbody>
                         @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3 cursor-pointer">
                                        <div class="position-relative">
                                            @if($user->avatar)
                                                <img src="{{ $user->avatar }}" alt="{{ $user->unique_id }}"
                                                    class="rounded-circle mx-auto mb-2"
                                                    style="width: 44px; height: 44px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto mb-2"
                                                    style="width: 44px; height: 44px; font-size: 20px;">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span
                                                class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-light rounded-circle"
                                                style="width: 10px; height: 10px; {{ $user->is_online ? 'display: block;' : 'display: none;' }}">
                                            </span>
                                        </div>
                                        <div class="">
                                            <p class="mb-0">{{ $user->name }} <br><span class="badge bg-primary">Id: {{$user->unique_id}}</span></p>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->mobile }}</td>
                                <td>
                                    @if ($user->status === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @elseif ($user->status === 'banned')
                                        <span class="badge bg-danger">Banned</span>
                                    @elseif ($user->status === 'pending')
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    @else
                                        <span class="badge bg-secondary">Unknown</span>
                                    @endif
                                </td>

                                <td>{{ $user->last_login_location ?? 'N/A' }}</td>
                                <td>
                                    <div class="table-actions d-flex align-items-center gap-3 fs-6">
                                        <a style="cursor: pointer;" class="text-primary" wire:click="changeStatus({{ $user->id }})"
                                            data-bs-toggle="tooltip" data-bs-placement="bottom" title="Change Status" aria-label="Change Status">
                                            <i class="bi bi-person-gear"></i>
                                        </a>

                                        <a
                                            href="{{ route('admin.user_transactions', ['id' => $user->id]) }}"
                                            class="text-primary"
                                            data-bs-toggle="tooltip"
                                            data-bs-placement="bottom"
                                            title="View Transactions"
                                            aria-label="Transactions"
                                            style="cursor: pointer;"
                                        >
                                            <i class="bi bi-wallet2"></i>
                                        </a>
                                        <a style="cursor: pointer;"
                                        wire:click="changeId({{ $user->id }})"
                                        class="text-warning"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="bottom"
                                        title="Change ID"
                                        aria-label="Change ID">
                                            <i class="bi bi-arrow-repeat"></i> {{-- 🔁 Suggestion --}}
                                        </a>
                                    </div>
                                </td>
                            </tr>
                         @empty
                            <tr>
                                <td colspan="7" class="text-center">No users found.</td>
                            </tr>
                        @endforelse
                       </tbody>
                    </table>
                     <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
        {{-- Change Unique ID Modal --}}
        <div wire:ignore.self class="modal fade" id="changeIdModal" tabindex="-1" aria-labelledby="changeIdModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow-lg rounded-3">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="changeIdModalLabel">Change Unique ID for {{ $name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" wire:click="$set('changeIdModel', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Current Unique ID</label>
                            <input type="text" class="form-control bg-light" value="{{ $oldUnique_id }}" readonly>
                        </div>

                        <div class="mb-3 d-flex gap-2 align-items-start">
                            <div class="flex-grow-1">
                                <label class="form-label fw-bold">New Unique ID</label>
                                <input type="text" class="form-control" wire:model="unique_id" placeholder="Enter or generate ID">
                                @error('unique_id') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="pt-4">
                                <button class="btn btn-outline-secondary" wire:click="generateUniqueId">
                                    <i class="bi bi-stars"></i> Generate ID
                                </button>
                            </div>
                        </div>

                        <div class="text-end mt-3">
                            <button class="btn btn-success" wire:click="updateUniqueId">
                                <i class="bi bi-save2"></i> Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Change Status Modal --}}
        <div wire:ignore.self class="modal fade" id="changeStatusModal" tabindex="-1" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title" id="changeStatusModalLabel">Change User Status</h5>
                        <button type="button" class="btn-close" wire:click="$set('changeStatusModal', false)" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="statusSelect" class="form-label">Select Status</label>
                            <select id="statusSelect" wire:model="status" class="form-select">
                                <option value="active">Active</option>
                                <option value="banned">Banned</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>

                        <div class="text-end">
                            <button class="btn btn-success" wire:click="updateStatus">
                                <i class="bi bi-check-circle"></i> Update Status
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                Livewire.on('openChangeIdModal', () => {
                    const modal = new bootstrap.Modal(document.getElementById('changeIdModal'));
                    modal.show();
                });
                Livewire.on('closeChangeIdModal', () => {
                    const modalEl = document.getElementById('changeIdModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                });
                Livewire.on('openChangeStatusModal', () => {
                    const modal = new bootstrap.Modal(document.getElementById('changeStatusModal'));
                    modal.show();
                });

                Livewire.on('closeChangeStatusModal', () => {
                    const modalEl = document.getElementById('changeStatusModal');
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    modal.hide();
                });
            </script>
        @endpush

    </div>
    @section('JS')
         @include('livewire.layout.backend.inc.js')
         <script src="{{ asset('backend/assets/js/index4.js') }}"></script>
    @endsection
</main>
