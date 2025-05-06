<main>
    @section('title')
        <title>Admin | Rifle Request Management</title>
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
          <div class="breadcrumb-title pe-3">Admin</div>
          <div class="ps-3">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb mb-0 p-0">
                <li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Rifle Request Management</li>
              </ol>
            </nav>
          </div>
        </div>
        <!--end breadcrumb-->

        @if (session()->has('message'))
        <div class="col-md-12 text-center">
            <center>
                <div class="col-md-5">
                    <div class="alert border-0 bg-success alert-dismissible fade show py-2">
                        <div class="d-flex align-items-center">
                        <div class="fs-3 text-white"><i class="bi bi-check-circle-fill"></i>
                        </div>
                        <div class="ms-3">
                            <div class="text-white">{{ session('message') }}</div>
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
              <h6 class="mb-0">Rifle Request Management</h6>
            </div>
            <div class="card-body">
               <div class="row">
                @if ($setingsMode)
                 <div class="col-12 col-lg-4 d-flex">
                   <div class="card border shadow-none w-100">
                     <div class="card-body">

                         <div class="col-12">
                           <label for="sending_method" class="form-label">Sending method</label>
                           <input class="form-control" type="text" wire:model="sending_method">
                         </div>

                         <div class="col-12 mt-2">
                          <label for="sending_mobile" class="form-label">Sender mobile</label>
                          <input class="form-control" type="text" wire:model="sending_mobile" >
                        </div>

                        <div class="col-12 mt-2">
                            <label for="transaction_id" class="form-label">Transaction id</label>
                            <input class="form-control" type="text" wire:model="transaction_id" >
                        </div>

                        <div class="col-12 mt-2">
                            <label for="amount_rifle" class="form-label">Amount</label>
                            <input class="form-control" type="text" wire:model="amount_rifle" >
                        </div>




                        <div class="col-12 mt-2">
                            <div class="card border shadow-none w-100 text-center mt-4 mb-4">
                                <!-- Show loading spinner during photo upload -->


                                <!-- Image Preview Section -->

                                    <center>
                                        <img src="{{ Storage::url($screenshot) }}" height="350" width="200" alt="Uploaded Image Preview">
                                    </center>

                            </div>
                        </div>

                        <div class="col-12">
                          <div class="mt-1 d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-primary" wire:click='accept'>
                                <span wire:loading.delay.long wire:target="accept" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Accept
                            </button>
                            <button type="submit" class="btn btn-danger" wire:click='cancel'>
                                <span wire:loading.delay.long wire:target="cancel" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Cancel
                            </button>
                          </div>
                        </div>

                     </div>
                   </div>
                 </div>
                 @endif
                 <div class="col-12 col-lg-8 d-flex">
                  <div class="card border shadow-none w-100">
                    <div class="card-body">
                      <div class="table-responsive">
                         <table class="table align-middle">
                           <thead class="table-light">
                             <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Actions</th>
                             </tr>
                           </thead>
                           <tbody>
                            @if($rifleStatus && $rifleStatus->count())
                                @foreach($rifleStatus as $item)
                                    <tr>
                                        <td>
                                            @if ($item->screenshot)
                                                <img src="{{ Storage::url($item->screenshot) }}" alt="Image" width="50" />
                                            @endif
                                        </td>
                                        <td>{{ $item->user->name }}</td>
                                        <td>{{ $item->amount_rifle }} Tk</td>
                                        <td>{{ $item->created_at->format('d-M-Y') }}</td>  {{-- তারিখ --}}
                                        <td>{{ $item->created_at->format('h:i A') }}</td>  {{-- সময় AM/PM --}}
                                        <td>
                                            <div class="d-flex align-items-center gap-3 fs-6">
                                            <a style="cursor: pointer;"  wire:click="setings({{ $item->id }})" class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="Edit info" aria-label="Edit"><i class="bi bi-pencil-fill"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No request found.</td>
                                </tr>
                            @endif

                           </tbody>
                         </table>
                      </div>
                      <div class="custom-pagination pt-1">
                        @if(!empty($prizes))
                        {{-- {{ $prizes->links('vendor.pagination.bootstrap-4') }} --}}
                        @endif
                    </div>
                      {{-- <nav class="float-end mt-0" aria-label="Page navigation">
                        {{ $categories->links() }}
                      </nav> --}}
                    </div>
                  </div>
                </div>
               </div><!--end row-->
            </div>
          </div>

    </main>



    @section('JS')
         @include('livewire.layout.backend.inc.js')
    @endsection
</main>

