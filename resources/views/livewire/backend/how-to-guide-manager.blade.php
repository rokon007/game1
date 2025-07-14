<main>
    @section('title')
        <title>Admin | How-To Guide Manager</title>
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
            <div class="breadcrumb-title pe-3">Content Management</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item active" aria-current="page">How-To Guides</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <button class="btn btn-primary" wire:click="resetForm">
                    <i class="bi bi-plus-circle me-1"></i> Add New Guide
                </button>
            </div>
        </div>
        <!--end breadcrumb-->

        @if (session()->has('success'))
            <div class="col-12">
                <div class="alert alert-success border-0 bg-success alert-dismissible fade show">
                    <div class="d-flex align-items-center">
                        <div class="font-35 text-white"><i class="bx bxs-check-circle"></i></div>
                        <div class="ms-3">
                            <h6 class="mb-0 text-white">Success</h6>
                            <div class="text-white">{{ session('success') }}</div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <div class="card radius-10">
            <div class="card-header bg-transparent border-bottom">
                <div class="d-flex align-items-center">
                    <h5 class="mb-0">{{ $isEditMode ? 'Edit' : 'Add New' }} How-To Guide</h5>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Form Section -->
                    <div class="col-12 col-lg-4">
                        <div class="card border shadow-none">
                            <div class="card-body">
                                <form wire:submit.prevent="{{ $isEditMode ? 'update' : 'save' }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" class="form-control" wire:model="title" placeholder="Enter guide title">
                                        @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <div wire:ignore>
                                            <textarea id="editor" wire:model="description"></textarea>
                                        </div>
                                        @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Video URL</label>
                                        <input type="text" class="form-control" wire:model="video_url" placeholder="https://example.com/video">
                                        @error('video_url') <small class="text-danger">{{ $message }}</small> @enderror
                                    </div>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-{{ $isEditMode ? 'warning' : 'primary' }}">
                                            <span wire:loading.delay wire:target="{{ $isEditMode ? 'update' : 'save' }}" class="spinner-border spinner-border-sm me-1"></span>
                                            <i class="bi bi-{{ $isEditMode ? 'pencil' : 'save' }} me-1"></i>
                                            {{ $isEditMode ? 'Update' : 'Save' }} Guide
                                        </button>

                                        @if($isEditMode)
                                            <button type="button" wire:click="resetForm" class="btn btn-secondary">
                                                <i class="bi bi-x-circle me-1"></i> Cancel
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- List Section -->
                    <div class="col-12 col-lg-8 mt-3 mt-lg-0">
                        <div class="card border shadow-none">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Title</th>
                                                <th>Video</th>
                                                <th>Last Updated</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($guides as $guide)
                                                <tr>
                                                    <td>{{ Str::limit($guide->title, 30) }}</td>
                                                    <td>
                                                        @if($guide->video_url)
                                                            <a href="{{ $guide->video_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                <i class="bi bi-play-circle me-1"></i> Watch
                                                            </a>
                                                        @else
                                                            <span class="text-muted">N/A</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $guide->updated_at->format('d M, Y') }}</td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <button wire:click="edit({{ $guide->id }})" class="btn btn-sm btn-warning">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <button wire:click="delete({{ $guide->id }})"
                                                                    onclick="return confirm('Are you sure you want to delete this guide?') || event.stopImmediatePropagation()"
                                                                    class="btn btn-sm btn-danger">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center py-4">
                                                        <div class="py-3">
                                                            <i class="bi bi-info-circle-fill fs-1 text-primary"></i>
                                                            <h5 class="mt-3">No Guides Found</h5>
                                                            <p class="text-muted">Start by adding your first how-to guide</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    {{-- @section('JS')
        <script src="https://cdn.ckeditor.com/ckeditor5/25.0.0/classic/ckeditor.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let editor;

                // Initialize CKEditor with simpleUpload
                ClassicEditor
                    .create(document.querySelector('#editor'), {
                        toolbar: {
                            items: [
                                'heading', '|',
                                'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                                'outdent', 'indent', '|',
                                'imageUpload', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo'
                            ]
                        },
                        image: {
                            toolbar: [
                                'imageTextAlternative', 'imageStyle:full', 'imageStyle:side'
                            ]
                        },
                        simpleUpload: {
                            uploadUrl: "{{ route('ckeditor.upload') }}",
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            }
                        }
                    })
                    .then(editorInstance => {
                        editor = editorInstance;

                        // Update Livewire when editor content changes
                        editor.model.document.on('change:data', () => {
                            @this.set('description', editor.getData());
                        });

                        // Listen for Livewire events
                        setupEventListeners();
                    })
                    .catch(error => {
                        console.error('CKEditor initialization error:', error);
                    });

                function setupEventListeners() {
                    // Listen for updateEditor event
                    window.addEventListener('updateEditor', function(event) {
                        if (editor && event.detail && event.detail.content !== undefined) {
                            editor.setData(event.detail.content || '');
                        }
                    });

                    // Listen for resetEditor event
                    window.addEventListener('resetEditor', function() {
                        if (editor) {
                            editor.setData('');
                        }
                    });

                    // Listen for resetForm event
                    window.addEventListener('resetForm', function() {
                        if (editor) {
                            editor.setData('');
                        }
                    });
                }

                // Alternative: Listen for Livewire events directly
                Livewire.on('updateEditor', (event) => {
                    console.log('updateEditor event received:', event);
                    if (editor && event.content !== undefined) {
                        editor.setData(event.content || '');
                    }
                });

                Livewire.on('resetEditor', () => {
                    console.log('resetEditor event received');
                    if (editor) {
                        editor.setData('');
                    }
                });

                Livewire.on('resetForm', () => {
                    console.log('resetForm event received');
                    if (editor) {
                        editor.setData('');
                    }
                });
            });
        </script>
        @include('livewire.layout.backend.inc.js')
    @endsection --}}

    @section('JS')
        <script src="https://cdn.ckeditor.com/ckeditor5/25.0.0/classic/ckeditor.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let editor;

                // Initialize CKEditor
                ClassicEditor
                    .create(document.querySelector('#editor'), {
                        ckfinder: {
                            uploadUrl: "{{ route('ckeditor.upload') }}",
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            }
                        }
                    })
                    .then(editorInstance => {
                        editor = editorInstance;

                        // Update Livewire when editor content changes
                        editor.model.document.on('change:data', () => {
                            @this.set('description', editor.getData());
                        });

                        // Listen for Livewire events
                        setupEventListeners();
                    })
                    .catch(error => {
                        console.error('CKEditor initialization error:', error);
                    });

                function setupEventListeners() {
                    // Listen for updateEditor event
                    window.addEventListener('updateEditor', function(event) {
                        if (editor && event.detail && event.detail.content !== undefined) {
                            editor.setData(event.detail.content || '');
                        }
                    });

                    // Listen for resetEditor event
                    window.addEventListener('resetEditor', function() {
                        if (editor) {
                            editor.setData('');
                        }
                    });

                    // Listen for resetForm event
                    window.addEventListener('resetForm', function() {
                        if (editor) {
                            editor.setData('');
                        }
                    });
                }

                // Alternative: Listen for Livewire events directly
                Livewire.on('updateEditor', (event) => {
                    console.log('updateEditor event received:', event);
                    if (editor && event.content !== undefined) {
                        editor.setData(event.content || '');
                    }
                });

                Livewire.on('resetEditor', () => {
                    console.log('resetEditor event received');
                    if (editor) {
                        editor.setData('');
                    }
                });

                Livewire.on('resetForm', () => {
                    console.log('resetForm event received');
                    if (editor) {
                        editor.setData('');
                    }
                });
            });
        </script>
        @include('livewire.layout.backend.inc.js')
    @endsection

    {{-- @section('JS')
        <script src="https://cdn.ckeditor.com/ckeditor5/25.0.0/classic/ckeditor.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let editor;

                // Initialize CKEditor with proper image upload configuration
                ClassicEditor
                    .create(document.querySelector('#editor'), {
                        toolbar: {
                            items: [
                                'heading', '|',
                                'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|',
                                'outdent', 'indent', '|',
                                'imageUpload', 'blockQuote', 'insertTable', 'mediaEmbed', 'undo', 'redo'
                            ]
                        },
                        image: {
                            toolbar: [
                                'imageTextAlternative', 'imageStyle:full', 'imageStyle:side'
                            ]
                        },
                        simpleUpload: {
                            uploadUrl: "{{ route('ckeditor.upload') }}",
                            headers: {
                                'X-CSRF-TOKEN': "{{ csrf_token() }}"
                            }
                        }
                    })
                    .then(editorInstance => {
                        editor = editorInstance;

                        // Update Livewire when editor content changes
                        editor.model.document.on('change:data', () => {
                            @this.set('description', editor.getData());
                        });

                        // Listen for Livewire events
                        setupEventListeners();
                    })
                    .catch(error => {
                        console.error('CKEditor initialization error:', error);
                    });

                function setupEventListeners() {
                    // Listen for updateEditor event
                    window.addEventListener('updateEditor', function(event) {
                        if (editor && event.detail && event.detail.content !== undefined) {
                            editor.setData(event.detail.content || '');
                        }
                    });

                    // Listen for resetEditor event
                    window.addEventListener('resetEditor', function() {
                        if (editor) {
                            editor.setData('');
                        }
                    });

                    // Listen for resetForm event
                    window.addEventListener('resetForm', function() {
                        if (editor) {
                            editor.setData('');
                        }
                    });
                }

                // Alternative: Listen for Livewire events directly
                Livewire.on('updateEditor', (event) => {
                    console.log('updateEditor event received:', event);
                    if (editor && event.content !== undefined) {
                        editor.setData(event.content || '');
                    }
                });

                Livewire.on('resetEditor', () => {
                    console.log('resetEditor event received');
                    if (editor) {
                        editor.setData('');
                    }
                });

                Livewire.on('resetForm', () => {
                    console.log('resetForm event received');
                    if (editor) {
                        editor.setData('');
                    }
                });
            });
        </script>
        @include('livewire.layout.backend.inc.js')
    @endsection --}}
</main>
