<main>
    @section('title')
        <title>Admin | Manage Prize</title>
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
                <li class="breadcrumb-item active" aria-current="page">Manage Prize</li>
              </ol>
            </nav>
          </div>
          {{-- <div class="ms-auto">
            <div class="btn-group">
              <button type="button" class="btn btn-primary">Settings</button>
              <button type="button" class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">	<span class="visually-hidden">Toggle Dropdown</span>
              </button>
              <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">	<a class="dropdown-item" href="javascript:;">Action</a>
                <a class="dropdown-item" href="javascript:;">Another action</a>
                <a class="dropdown-item" href="javascript:;">Something else here</a>
                <div class="dropdown-divider"></div>	<a class="dropdown-item" href="javascript:;">Separated link</a>
              </div>
            </div>
          </div> --}}
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
              <h6 class="mb-0">Add Prize</h6>
            </div>
            <div class="card-body">
               <div class="row">
                 <div class="col-12 col-lg-4 d-flex">
                   <div class="card border shadow-none w-100">
                     <div class="card-body">
                       <form class="row g-3" wire:submit.prevent="store">
                         <div class="col-12">
                           <label for="name" class="form-label">Prize Name</label>
                           <input class="form-control" type="text" wire:model="name" placeholder="Prize Name">
                           @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                         </div>

                         <div class="col-12">
                          <label for="amount" class="form-label">Amount</label>
                          <input class="form-control" type="number" wire:model="amount" placeholder="Amount" >
                          @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" wire:model="description"></textarea>
                          @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">
                          <label for="photo" class="form-label"> Prize Image</label>
                          <input type="file" wire:model="image_path" />
                          @error('image_path') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">
                            <div class="card border shadow-none w-100 text-center">
                                <!-- Show loading spinner during photo upload -->
                                <div wire:loading.delay.short wire:target="image_path">
                                    <iframe
                                        src="https://giphy.com/embed/aqd1tYU4WvlO3FiYvo"
                                        width="200"
                                        height="200"
                                        frameborder="0"
                                        class="giphy-embed"
                                        allowfullscreen>
                                    </iframe>
                                </div>

                                <!-- Image Preview Section -->
                                <div wire:loading.remove wire:target="image_path">
                                    <center>
                                        @if ($image_path)
                                            <img src="{{ $image_path->temporaryUrl() }}" height="100" width="100" alt="Uploaded Image Preview">
                                        @elseif ($prize_id && isset($prizes) && $prizes->where('id', $prize_id)->first()?->image_path)
                                            <img src="{{ Storage::url($prizes->where('id', $prize_id)->first()->image_path) }}" alt="Image Preview" height="100" width="100">
                                        @else
                                            <img src="{{ asset('backend/upload/image/upload.png') }}" height="100" width="100" alt="Default Upload Image">
                                        @endif
                                    </center>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                          <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.delay.long wire:target="store" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ $prize_id ? 'Update' : 'Create' }} Prize
                            </button>
                          </div>
                        </div>
                       </form>
                     </div>
                   </div>
                 </div>
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
                                <th>Description</th>
                                <th>Active</th>
                                <th>Actions</th>
                             </tr>
                           </thead>
                           <tbody>
                            @if($prizes && $prizes->count())
                                @foreach($prizes as $prize)
                                    <tr>
                                        <td>
                                            @if ($prize->image_path)
                                                <img src="{{ Storage::url($prize->image_path) }}" alt="Image" width="50" />
                                            @endif
                                        </td>
                                        <td>{{ $prize->name }}</td>
                                        <td>{{ $prize->amount }}</td>
                                        <td>{{ $prize->description }}</td>
                                        <td>
                                            @if($prize->is_active)
                                                ✅
                                            @else
                                                ❌
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3 fs-6">
                                            <a style="cursor: pointer;"  wire:click="edit({{ $prize->id }})" class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="Edit info" aria-label="Edit"><i class="bi bi-pencil-fill"></i></a>
                                            <a style="cursor: pointer;"  wire:click="delete({{ $prize->id }})" class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="bi bi-trash-fill"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No prizes found.</td>
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

