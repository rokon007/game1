<main>
    @section('title')
        <title>Admin | Manage Game</title>
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
                <li class="breadcrumb-item active" aria-current="page">Manage Game</li>
              </ol>
            </nav>
          </div>
          <div class="ms-auto">
            <div class="btn-group">
              <a href="#" class="btn btn-primary">Manage Game</a>
            </div>
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
              <h6 class="mb-0">Manage Game</h6>
            </div>
            <div class="card-body">
               <div class="row">
                 <div class="col-12 col-lg-4 d-flex">
                   <div class="card border shadow-none w-100">
                     <div class="card-body">
                       <form class="row g-3" wire:submit.prevent="store">
                         <div class="col-12">
                           <label for="title" class="form-label">Game Title</label>
                           <input class="form-control" type="text" wire:model="title" placeholder="Game Title">
                           @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                         </div>

                         <div class="col-12">
                          <label for="scheduled_at" class="form-label">Scheduled At</label>
                          <input class="form-control" type="datetime-local" wire:model="scheduled_at" >
                          @error('scheduled_at') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">
                            <label for="ticket_price" class="form-label">Ticket Price</label>
                            <input class="form-control" type="number" step="0.01" wire:model="ticket_price" >
                            @error('ticket_price') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">
                            <hr>
                            <h6>Winer price</h6>
                            <hr>
                        </div>
                        <div class="col-12">
                            <label for="corner_prize" class="form-label">Corner Price</label>
                            <input class="form-control" type="number" step="0.01" wire:model="corner_prize" >
                            @error('corner_prize') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">
                            <label for="top_line_prize" class="form-label">Top line Price</label>
                            <input class="form-control" type="number" step="0.01" wire:model="top_line_prize" >
                            @error('top_line_prize') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">
                            <label for="middle_line_prize" class="form-label">Middle line Price</label>
                            <input class="form-control" type="number" step="0.01" wire:model="middle_line_prize" >
                            @error('middle_line_prize') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">
                            <label for="bottom_line_prize" class="form-label">Bottom line Price</label>
                            <input class="form-control" type="number" step="0.01" wire:model="bottom_line_prize" >
                            @error('bottom_line_prize') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">
                            <label for="full_house_prize" class="form-label">Full house Price</label>
                            <input class="form-control" type="number" step="0.01" wire:model="full_house_prize" >
                            @error('full_house_prize') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-12">

                                <label class="inline-flex items-center">
                                    <input type="checkbox" class="form-checkbox" {{ $is_active ? 'checked' : '' }}>
                                    <span class="ml-2">Is Active?</span>
                                </label>

                        </div>


                        <div class="col-12">
                          <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.delay.long wire:target="store" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                {{ $game_id ? 'Update' : 'Create' }} Game

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
                                <th>Announced</th>
                                <th>Game Title</th>
                                <th>Scheduled At</th>
                                <th>Ticket Price</th>
                                <th>Active</th>
                                <th>Actions</th>
                             </tr>
                           </thead>
                           <tbody>
                            @if($games && $games->count())
                                @foreach($games as $game)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3 fs-6">
                                                <a href="{{ route('admin.number_announcer', ['gameId' => $game->id]) }}"
                                                class="text-warning"
                                                data-bs-toggle="tooltip"
                                                data-bs-placement="bottom"
                                                title="Edit info"
                                                aria-label="Edit Number Announcer">
                                                <i class="bi bi-pencil-fill"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td>{{ $game->title }}</td>
                                        <td>{{ \Carbon\Carbon::parse($game->scheduled_at)->format('l, j F Y - h:i A') }}</td>
                                        <td>{{ $game->ticket_price }}</td>
                                        <td>
                                            @if($game->is_active)
                                                ✅
                                            @else
                                                ❌
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3 fs-6">
                                            <a style="cursor: pointer;"  wire:click="edit({{ $game->id }})" class="text-warning" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="Edit info" aria-label="Edit"><i class="bi bi-pencil-fill"></i></a>
                                            <a style="cursor: pointer;"  wire:click="delete({{ $game->id }})" class="text-danger" data-bs-toggle="tooltip" data-bs-placement="bottom" title="" data-bs-original-title="Delete" aria-label="Delete"><i class="bi bi-trash-fill"></i></a>
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

                        @endif
                    </div>

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
