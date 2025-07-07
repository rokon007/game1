<main>
    @section('title')
        <title>Admin | How To Guide Manager </title>
    @endsection
    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
              {{-- <script src="//unpkg.com/alpinejs" defer></script> --}}

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
                <li class="breadcrumb-item active" aria-current="page">How To Guide Manager</li>
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
              <h6 class="mb-0">{{ $isEditMode ? 'Edit' : 'Add New' }} How-To Guide</h6>
            </div>
            <div class="card-body">
               <div class="row">
                 <div class="col-12 col-lg-4 d-flex">
                   <div class="card border shadow-none w-100">
                     <div class="card-body">
                         <form class="row g-3" wire:submit.prevent="{{ $isEditMode ? 'update' : 'save' }}">
                         <div class="col-12">
                            <label>Title</label>
                            <input type="text" class="form-control" wire:model="title">
                            @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <div wire:ignore>
                                <textarea id="note" data-note="@this" wire:model="description" class="form-control" rows="4"></textarea>
                            </div>
                            @error('description') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-12">
                            <label>Video URL</label>
                            <input type="text" class="form-control" wire:model="video_url">
                            @error('video_url') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-{{ $isEditMode ? 'warning' : 'primary' }}">
                                        <span wire:loading.delay.long wire:target="{{ $isEditMode ? 'update' : 'save' }}" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                        {{ $isEditMode ? 'Update' : 'Add' }}
                                    </button>
                                </div>
                            </div>

                            @if ($isEditMode)
                                <div class="col-md-6 mt-2 mt-md-0">
                                    <div class="d-grid">
                                        <button type="button" wire:click="resetForm" class="btn btn-secondary">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>



                        {{-- <div class="col-12">
                          <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.delay.long wire:target="store" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ $prize_id ? 'Update' : 'Create' }} Prize
                            </button>
                          </div>
                        </div> --}}
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
                                    <th>Title</th>
                                    <th>Video</th>
                                    <th>Actions</th>
                                </tr>
                           </thead>
                           <tbody>
                                @forelse($guides as $guide)
                                    <tr>
                                        <td>{{ $guide->title }}</td>
                                        <td>
                                            @if ($guide->video_url)
                                                <a href="{{ $guide->video_url }}" target="_blank">Watch</a>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            <button wire:click="edit({{ $guide->id }})" class="btn btn-sm btn-warning">Edit</button>
                                            <button wire:click="delete({{ $guide->id }})" class="btn btn-sm btn-danger" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">Delete</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3">No guides found.</td></tr>
                                @endforelse
                            </tbody>
                         </table>
                      </div>
                    </div>
                  </div>
                </div>
               </div><!--end row-->
            </div>
          </div>

    </main>




    @section('JS')
     <script src="https://cdn.ckeditor.com/ckeditor5/25.0.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            ClassicEditor
                .create(document.querySelector("#note"), {
                    ckfinder: {
                        uploadUrl: "{{ route('ckeditor.upload') }}", // রাউট চেক করুন
                        headers: {
                            'X-CSRF-TOKEN': "{{ csrf_token() }}" // CSRF টোকেন যোগ করুন
                        }
                    }
                })
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        @this.set('description', editor.getData());
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        });
    </script>

         @include('livewire.layout.backend.inc.js')
    @endsection
</main>

