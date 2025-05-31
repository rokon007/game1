<main>
    @section('title')
        <title>Admin | Agent Management</title>
    @endsection
    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
              <style>
                .rounded-md {
                    border-radius: 0.375rem; /* 6px */
                }
              </style>
    @endsection


    <main class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
          <div class="breadcrumb-title pe-3">Admin</div>
          <div class="ps-3">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Agent Management</li>
              </ol>
            </nav>
          </div>
        </div>
        <!--end breadcrumb-->
        @if (session()->has('success'))
            <div class="col-md-12 text-center">
                <center>
                    <div class="col-md-5">
                        <div class="alert border-0 bg-success alert-dismissible fade show py-2">
                            <div class="d-flex align-items-center">
                            <div class="fs-3 text-white"><i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="ms-3">
                                <div class="text-white">{{ session('success') }}</div>
                            </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </center>
            </div>
        @endif

        <div class="card">
            <div class="card-header py-3">
              <h6 class="mb-0">Agent Management</h6>
            </div>
            <div class="row">
                <div class="col-12 col-lg-4 d-flex">
                    <div class="card border shadow-none w-100">
                        <div class="card-body">
                            <div class="">
						        <div class="chat-sidebar-header">
							        <div class="d-flex align-items-center">

                                    </div>
                                    <div class="input-group input-group-sm"> <span class="input-group-text bg-transparent"><i class="bx bx-search"></i></span>
                                        <input type="text" wire:model.live="search" class="form-control" placeholder="Search by name, email, or mobile..."> <span class="input-group-text bg-transparent"><i class="bx bx-dialpad"></i></span>
                                    </div>
                                </div>
                                <div class="list-group list-group-flush">
                                    @forelse($users as $user)
                                    <a  wire:click='openManagmentModal({{$user->id}})' style="cursor: pointer" class="list-group-item">
                                        <div class="d-flex">
                                            @if ($user->avatar)
                                                <div class="chat-user-online">
                                                    <img src="{{ Storage::url($user->avatar) }}" width="42" height="42" class="rounded-circle" alt="">
                                                </div>
                                            @else
                                                <div class="chat-user-online">
                                                    <div class="position-relative">
                                                        <div class="rounded-circle {{$user->is_online ? 'bg-primary' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                            <strong>{{ strtoupper(substr($user->name, 0, 1)) }}</strong>
                                                        </div>
                                                        <span style="display: {{$user->is_online ? 'block' : 'none'}}" class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"></span>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="flex-grow-1 ms-2">
                                                <h6 class="mb-0 chat-title">{{ $user->name }}</h6>
                                                <p class="mb-0 chat-msg">{{ $user->email ?? 'N/A' }}<br>{{ $user->mobile }}</p>
                                            </div>
                                            <div class="chat-time">{{ ucfirst($user->status) }}</div>
                                        </div>
                                    </a>
                                    @empty

                                     @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-8">
                    <div class="card-body">
                        <h5 class="card-title mb-0 text-center">Agents List</h5>
                        <div class="my-3 border-top"></div>
                        @if ($agents)
                            <div class="col col-lg-9 mx-auto">
                                @forelse ( $agents as $user )
                                <div class="alert border-0 border-success border-start border-4 bg-light-success alert-dismissible fade show py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-3">
                                            @if ($user->avatar)
                                                <div class="chat-user-online">
                                                    <img src="{{ Storage::url($user->avatar) }}" width="42" height="42" class="rounded-circle" alt="">
                                                </div>
                                            @else
                                                <div class="chat-user-online">
                                                    <div class="position-relative">
                                                        <div class="rounded-circle {{$user->is_online ? 'bg-primary' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                            <strong>{{ strtoupper(substr($user->name, 0, 1)) }}</strong>
                                                        </div>
                                                        <span style="display: {{$user->is_online ? 'block' : 'none'}}" class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"></span>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ms-3">
                                                <div class="text-success">{{$user->name}}</div>
                                        </div>
                                        <div class="ms-5">
                                            <a wire:click='openManagmentModal({{$user->id}})' style="cursor: pointer" class="text-dark" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="Edit" aria-label="Edit">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                        </div>
                                        <div class="ms-3">
                                            <a wire:click='openRechargeModal({{$user->id}})' style="cursor: pointer" class="text-dark" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="Recharge Amount" aria-label="Recharge Amount">
                                                <i class="bi bi-cash-stack text-danger"></i>
                                            </a>
                                        </div>
                                    </div>

                                </div>
                                @empty
                                    <div class="alert border-0 border-dark border-start border-4 bg-light-dark alert-dismissible fade show">
                                        <div class="text-dark">No AgenT found !</div>
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

            @if($agentMModal)
                <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5)">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content border-danger">
                            <form wire:submit.prevent='updateUser'>
                                <div class="modal-header bg-danger text-white">
                                    <h5 class="modal-title">Manage User</h5>
                                </div>
                                <div class="modal-body">
                                    <div class="col mb-2">
                                        <div class="card radius-10 bg-primary">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="">
                                                        <p class="mb-1 text-white">{{$name}}</p>
                                                        <h4 class="mb-0 text-white">৳ {{$credit}}</h4>
                                                    </div>
                                                    <div class="ms-auto fs-2 text-white">
                                                        @if ($avatar)
                                                            <div class="chat-user-online">
                                                                <img src="{{ Storage::url($avatar) }}" width="42" height="42" class="rounded-circle" alt="">
                                                            </div>
                                                        @else
                                                            <div class="chat-user-online">
                                                                <div class="position-relative">
                                                                    <div class="rounded-circle {{$is_online ? 'bg-success' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                                        <strong>{{ strtoupper(substr($name, 0, 1)) }}</strong>
                                                                    </div>
                                                                    <span style="display: {{$is_online ? 'block' : 'none'}}" class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"></span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <hr class="my-2 border-top border-light">
                                                <small class="mb-0 text-white">
                                                    <i class="bi bi-envelope"></i>
                                                    <span>{{$email}}</span>
                                                    <i class="bi bi-telephone"></i>
                                                    <span>{{$mobile}}</span>
                                                    <span class="badge rounded-pill {{ $status == 'banned' ? 'bg-danger' : 'bg-success' }}">{{ $status }}</span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="border p-3 rounded">
                                                <div class="row g-3">
                                                    <input type="hidden" wire:model='user_id'>
                                                    <div class="col-12">
                                                        <label class="form-label">Select user type</label>
                                                        <select wire:model='role' class="form-select mb-3" aria-label="Default select example">
                                                            <option selected="">Select user type</option>
                                                            <option value="user">User</option>
                                                            <option value="agent">Agent</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Select status</label>
                                                        <select wire:model='status' class="form-select mb-3" aria-label="Default select example">
                                                            <option selected="">Select status</option>
                                                            <option value="active">Active</option>
                                                            <option value="banned">Banned</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-danger">
                                        <span wire:loading.delay.long wire:target="updateUser" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        Update
                                    </button>
                                    <button class="btn btn-secondary" wire:click="$set('agentMModal', false)">Close</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @if ($rechargeModal)
                <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5)">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content border-success">
                            <form wire:submit.prevent='updateUser'>
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">Recharge {{$rechargeUser->name}}'s Account</h5>
                                </div>
                                <div class="modal-body">
                                    <div class="col mb-2">
                                        <div class="card radius-10 bg-primary">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="">
                                                        <p class="mb-1 text-white">{{$rechargeUser->name}}</p>
                                                        <h4 class="mb-0 text-white">৳ {{$rechargeUser->credit}}</h4>
                                                    </div>
                                                    <div class="ms-auto fs-2 text-white">
                                                        @if ($rechargeUser->avatar)
                                                            <div class="chat-user-online">
                                                                <img src="{{ Storage::url($rechargeUser->avatar) }}" width="42" height="42" class="rounded-circle" alt="">
                                                            </div>
                                                        @else
                                                            <div class="chat-user-online">
                                                                <div class="position-relative">
                                                                    <div class="rounded-circle {{$rechargeUser->is_online ? 'bg-success' : 'bg-secondary' }} text-white d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                                        <strong>{{ strtoupper(substr($rechargeUser->name, 0, 1)) }}</strong>
                                                                    </div>
                                                                    <span style="display: {{$rechargeUser->is_online ? 'block' : 'none'}}" class="position-absolute bottom-0 end-0 translate-middle p-1 bg-success border border-white rounded-circle"></span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <hr class="my-2 border-top border-light">
                                                <small class="mb-0 text-white">
                                                    <i class="bi bi-envelope"></i>
                                                    <span>{{$rechargeUser->email}}</span>
                                                    <i class="bi bi-telephone"></i>
                                                    <span>{{$rechargeUser->mobile}}</span>
                                                    <span class="badge rounded-pill {{ $rechargeUser->status == 'banned' ? 'bg-danger' : 'bg-success' }}">{{ $status }}</span>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="border p-3 rounded">
                                                <div class="row g-3">
                                                    <input type="hidden" wire:model='rechargeUser_id'>
                                                    <div class="col-12" style="display: {{$amountMode ? 'block' : 'none'}}">
                                                        <label class="form-label">Recharge Amount</label>
                                                        <input wire:model='amount' type="number" class="form-control">
                                                        @error('amount')<small class="text-danger mb-2">{{ $message }}</small>@enderror
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="card radius-10 bg-success" style="display: {{$confirmMode ? 'block' : 'none'}}">
                                                            <div class="card-body">
                                                                <div class="d-flex align-items-center">
                                                                    <h4 class="mb-0 text-white text-center">৳ {{$amount}}</h4>
                                                                </div>
                                                                <p class="text-white text-center">Plese enter your admin password for confermation</p>
                                                                <input wire:model='password' type="password" class="form-control">
                                                                @error('password')<small class="text-danger mb-2">{{ $message }}</small>@enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    @if ($amountMode)
                                        <button wire:click="rechargeNext" type="button" class="btn btn-primary">Next</button>
                                    @else
                                       <button wire:click="comfirm('{{ $rechargeUser->id }}')" type="button" class="btn btn-danger">
                                        <span wire:loading.delay.long wire:target="comfirm('{{ $rechargeUser->id }}')" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        Send Credit
                                    </button>
                                    @endif
                                    <button class="btn btn-secondary" wire:click='closeRechargeModal'>Close</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            @if($transactionSuccess)
                <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5)">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content border-success">
                            <div class="modal-header bg-success text-white">
                                <h5 class="modal-title">Transaction Successful</h5>
                            </div>
                            <div class="modal-body text-center">
                                <i class="bi bi-check-circle-fill text-success" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0 fs-5">Your transaction was completed successfully.</p>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button class="btn btn-success" wire:click="$set('transactionSuccess', false)">Close</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif



    </main>



    @section('JS')
         @include('livewire.layout.backend.inc.js')
         <script src="{{ asset('backend/assets/js/index4.js') }}"></script>
    @endsection
</main>

