<main>
    @section('title')
        <title>Admin | Dashboard</title>
    @endsection
    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
    @endsection


    <main class="page-content">
        <section class="py-4">
            <div class="container">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 row-cols-xxl-4">
                    <div class="col">
                        <div class="card radius-10 bg-primary">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="">
                                        <p class="mb-1 text-white">Account Blance</p>
                                        <h4 class="mb-0 text-white">
                                            ৳ {{$user_credit}}
                                        </h4>
                                    </div>
                                    <div class="ms-auto fs-2 text-white">
                                        <i class="bi bi-currency-exchange"></i>
                                    </div>
                                </div>
                                <hr class="my-2 border-top border-light">
                                <small class="mb-0 text-white"><i class="bi bi-wallet2"></i>
                                    <span>
                                        <button wire:click='addMony' type="button" class="btn btn-sm btn-success px-5 radius-30">Add Money</button>
                                    </span>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 bg-orange">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-white">Total Posts</p>
                                <h4 class="mb-0 text-white">249</h4>
                            </div>
                            <div class="ms-auto fs-2 text-white">
                                <i class="bi bi-pencil"></i>
                            </div>
                            </div>
                            <hr class="my-2 border-top border-light">
                            <small class="mb-0 text-white"><i class="bi bi-arrow-up"></i> <span>+10.5% from last week</span></small>
                        </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 bg-orange">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-white">Total Posts</p>
                                <h4 class="mb-0 text-white">249</h4>
                            </div>
                            <div class="ms-auto fs-2 text-white">
                                <i class="bi bi-pencil"></i>
                            </div>
                            </div>
                            <hr class="my-2 border-top border-light">
                            <small class="mb-0 text-white"><i class="bi bi-arrow-up"></i> <span>+10.5% from last week</span></small>
                        </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card radius-10 bg-purple">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                            <div class="">
                                <p class="mb-1 text-white">Articles</p>
                                <h4 class="mb-0 text-white">645</h4>
                            </div>
                            <div class="ms-auto fs-2 text-white">
                                <i class="bi bi-book"></i>
                            </div>
                            </div>
                            <hr class="my-2 border-top border-light">
                            <small class="mb-0 text-white"><i class="bi bi-arrow-up"></i> <span>+16.5% from last week</span></small>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section>
            <div class="row">
                <div class="col-md-4">
                    <button wire:click='testClick' class="btn btn-primary">Click</button>
                </div>
            </div>
        </section>
            @if ($rechargeModal)
                <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5)">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content border-success">
                            <form wire:submit.prevent='updateUser'>
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title">Recharge {{auth()->user()->name}}'s Account</h5>
                                </div>
                                <div class="modal-body">

                                    <div class="card">
                                        <div class="card-body">
                                            <div class="border p-3 rounded">
                                                <div class="row g-3">
                                                    <input type="hidden" wire:model='rechargeUser_id'>
                                                    <div class="col-12" style="display: {{$amountMode ? 'block' : 'none'}}">
                                                        <label class="form-label"> Amount</label>
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
                                       <button wire:click="comfirm('{{ $rechargeUser_id }}')" type="button" class="btn btn-danger">
                                        <span wire:loading.delay.long wire:target="comfirm('{{ $rechargeUser_id }}')" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
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
    @endsection
</main>

