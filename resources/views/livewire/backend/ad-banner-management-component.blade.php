<main>
    @section('title')
        <title>Admin | Banner Management</title>
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
                <li class="breadcrumb-item active" aria-current="page">Banner Management</li>
              </ol>
            </nav>
          </div>
          <div class="ms-auto">
            <div class="btn-group">
              <a href="#" class="btn btn-primary">Add Banner</a>
            </div>
          </div>
        </div>
        <!--end breadcrumb-->

            @if (session()->has('delete'))
                <div class="col-md-12 text-center">
                    <center>
                        <div class="col-md-5">
                            <div class="alert border-0 bg-success alert-dismissible fade show py-2">
                                <div class="d-flex align-items-center">
                                <div class="fs-3 text-white"><i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div class="ms-3">
                                    <div class="text-white">{{ session('delete') }}</div>
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
                <div class="row g-3 align-items-center">
                    <div class="col-lg-3 col-md-6 me-auto">
                        <div class="ms-auto position-relative">
                            <div class="position-absolute top-50 translate-middle-y search-icon px-3">
                                <i class="bi bi-search"></i>
                            </div>
                            <input class="form-control ps-5" type="text" placeholder="search produts">
                        </div>
                    </div>
                </div>
             </div>
             <div class="card-body">
                <div class="product-grid">
                    <div class="row row-cols-1 row-cols-lg-4 row-cols-xl-4 row-cols-xxl-5 g-3">

                        <div class="col">
                            <div class="card border shadow-none mb-0">
                                <div class="card-body text-center">
                                    <div class="card border shadow-none w-100 text-center">
                                        <!-- Show loading spinner during photo upload -->
                                        <div wire:loading.delay.short wire:target="photo1">
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
                                        <div wire:loading.remove wire:target="photo1">
                                            <center>
                                                @if ($photo1)
                                                    <img src="{{ $photo1->temporaryUrl() }}" height="200" width="200" alt="Uploaded Image Preview">
                                                @else
                                                    <img src="{{ asset('backend/upload/image/upload.png') }}" alt="Default Image" height="200" width="200">
                                                @endif
                                            </center>
                                        </div>
                                    </div>

                                    <!-- Form for adding the image -->
                                    <form wire:submit.prevent="updateMoreImage">
                                        <div class="col-12 text-start">
                                            <label for="photo1" class="form-label">Add Banner</label>
                                            <input type="file" wire:model="photo1" class="form-control form-control-sm"  />
                                            @error('photo1')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3 text-start">
                                            <label for="titel" class="form-label">Banner Title</label>
                                            <input type="text" wire:model='titel' class="from-control" placeholder="Banner Title">
                                            @error('titel')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3 text-start">
                                            <label for="text" class="form-label">Banner Text</label>
                                            <input type="text" wire:model='text' class="from-control" placeholder="Banner Text">
                                            @error('text')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3 text-start">
                                            <label for="url" class="form-label">Banner url</label>
                                            <input type="text" wire:model='url' class="from-control" placeholder="url">
                                            @error('url')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3 text-start">
                                            <label for="button_name" class="form-label">Banner button</label>
                                            <input type="text" wire:model='button_name' class="from-control" placeholder="Banner button">
                                            @error('button_name')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3 text-start">
                                            <label for="status" class="form-label">Banner Status</label>
                                            <select class="form-select" wire:model='status'>
                                                <option value="">Select status</option>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                            @error('status')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="actions d-flex align-items-center justify-content-center gap-2 mt-3">
                                            <x-action-message class="me-3" on="updated1">
                                                <span class="badge bg-success">Uploadet</span>
                                            </x-action-message>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <span wire:loading.delay.long wire:target="updateMoreImage" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                <i class="bi bi-upload"></i> Upload
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>


                    @foreach ($images as $image)
                        <div class="col">
                            <div class="card border shadow-none mb-0">
                                <div class="card-body text-center">
                                    <div class="card border shadow-none w-100 text-center">
                                        <!-- Show loading spinner during photo upload -->
                                        <div wire:loading.delay.short wire:target="photo.{{ $image->id }}">
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
                                        <div wire:loading.remove wire:target="photo.{{ $image->id }}">
                                            <center>
                                                @if (isset($photo[$image->id]))
                                                    <img src="{{ $photo[$image->id]->temporaryUrl() }}" height="200" width="200" alt="Uploaded Image Preview">
                                                @else
                                                    <img src="{{ Storage::url($image->image_path) }}" alt="Image Preview" height="200" width="200">
                                                @endif
                                            </center>
                                        </div>
                                    </div>

                                    <!-- Form for updating and deleting the image -->
                                    <form wire:submit.prevent="updateImage({{ $image->id }})">
                                        <div class="col-12 text-start">
                                            <label for="photo" class="form-label">Change Image</label>
                                            <input type="file" wire:model="photo.{{ $image->id }}" class="form-control form-control-sm" />
                                            @error("photo.{{ $image->id }}")
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="col-12 mt-3 text-start">
                                            <label for="titel1.{{ $image->id }}" class="form-label">Banner Title</label>
                                            <input type="text" wire:model='titel1.{{ $image->id }}' class="from-control">
                                            @error('titel1.{{ $image->id }}')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3 text-start">
                                            <label for="text1.{{ $image->id }}" class="form-label">Banner Text</label>
                                            <input type="text" wire:model='text1.{{ $image->id }}' class="from-control">
                                            @error('text1.{{ $image->id }}')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3 text-start">
                                            <label for="url1.{{ $image->id }}" class="form-label">Banner url</label>
                                            <input type="text" wire:model='url1.{{ $image->id }}' class="from-control">
                                            @error('url1.{{ $image->id }}')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3 text-start">
                                            <label for="button_name1.{{ $image->id }}" class="form-label">Banner button</label>
                                            <input type="text" wire:model='button_name1.{{ $image->id }}' class="from-control" >
                                            @error('button_name1.{{ $image->id }}')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <div class="col-12 mt-3 text-start">
                                            <label for="status1.{{ $image->id }}" class="form-label">Banner Status</label>
                                            <select class="form-select" wire:model='status1.{{ $image->id }}'>
                                                <option value="">Select status</option>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                            @error('status1.{{ $image->id }}')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                        <x-action-message class="me-3" on="updated.{{ $image->id }}">
                                            <span class="badge rounded-pill bg-success">Updated</span>
                                        </x-action-message>

                                        <div class="actions d-flex align-items-center justify-content-center gap-2 mt-3">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                                <span wire:loading.delay.long wire:target="updateImage({{ $image->id }})" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                                <i class="bi bi-pencil-fill"></i> Update
                                            </button>
                                            <button type="button" wire:click="deleteImage({{ $image->id }})" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash-fill"></i> Delete
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach





                    </div>
                    <nav class="float-end mt-4" aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                    </nav>
                </div>
            </div>
          </div>
            @if ($delitModel)
            <div class="modal fade show" id="exampleVerticallycenteredModal" tabindex="-1" style="display: block; padding-left: 0px;" aria-modal="true" role="dialog">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <!-- Modal Header -->
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-triangle me-2 text-white"></i> Confirmation
                            </h5>
                            <button type="button" class="btn-close btn-close-white" wire:click='cancel' aria-label="Close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body bg-light text-center">
                            <div class="d-flex flex-column align-items-center">
                                <!-- Image Preview -->
                                <img src="{{ Storage::url($imageDelet->image_path) }}" alt="Main Image" class="rounded shadow-sm mb-3" width="100">

                                <!-- Confirmation Text -->
                                <h5 class="text-danger">
                                    <i class="fas fa-trash-alt me-2"></i> Are you sure you want to delete this image?
                                </h5>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary" wire:click='cancel'>
                                <i class="fas fa-times me-2 text-white"></i> Cancel
                            </button>
                            <button type="button" class="btn btn-danger text-white" wire:click='deleteImage1'>
                                <i class="fas fa-trash me-2"></i> Delete
                            </button>
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
