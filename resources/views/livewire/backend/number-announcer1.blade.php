<main>
    @section('title')
        <title>Admin | Number Announcer</title>
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
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-2 row-cols-xl-4">
            <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Total participants</p>
                            <h4 class="my-1">4805</h4>
                            <p class="mb-0 font-13 text-success"><i class="bi bi-caret-up-fill"></i> 5% from last week</p>
                        </div>
                        <div class="widget-icon-large bg-gradient-purple text-white ms-auto"><i class="bi bi-basket2-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="col">
                <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Total ticket sales</p>
                            <h4 class="my-1">$24K</h4>
                            <p class="mb-0 font-13 text-success"><i class="bi bi-caret-up-fill"></i> 4.6 from last week</p>
                        </div>
                        <div class="widget-icon-large bg-gradient-success text-white ms-auto"><i class="bi bi-currency-exchange"></i>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Total sales amaunt</p>
                            <h4 class="my-1">5.8K</h4>
                            <p class="mb-0 font-13 text-danger"><i class="bi bi-caret-down-fill"></i> 2.7 from last week</p>
                        </div>
                        <div class="widget-icon-large bg-gradient-danger text-white ms-auto"><i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            <div class="col">
            <div class="card radius-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-secondary">Total Prize Amount</p>
                            <h4 class="my-1">38.15%</h4>
                            <p class="mb-0 font-13 text-success"><i class="bi bi-caret-up-fill"></i> 12.2% from last week</p>
                        </div>
                        <div class="widget-icon-large bg-gradient-info text-white ms-auto"><i class="bi bi-bar-chart-line-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-lg-9 col-xl-9 d-flex">
                <div class="card radius-10 w-100">
                    <div class="card-body" style="position: relative;">
                        <div class="row row-cols-1 row-cols-lg-2 g-3 align-items-center">
                            <div class="col">
                                <h5 class="mb-0">Announce Number for {{ $game->title }}</h5>
                            </div>
                            <div class="col">
                                <div class="d-flex align-items-center justify-content-sm-end gap-3 cursor-pointer">
                                    <div class="font-13"><i class="bi bi-circle-fill text-primary"></i><span class="ms-2">Sales</span></div>
                                    <div class="font-13"><i class="bi bi-circle-fill text-success"></i><span class="ms-2">Orders</span></div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="mt-4">
                            <form wire:submit.prevent="announceNumber">
                                <div class="row gy-3">
                                    <div class="col-md-6">
                                        <select wire:model="selectedNumber" id="number" class="form-control">
                                            <option value="">Select Number</option>
                                            @for ($i = 1; $i <= 90; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="col-md-6 text-end d-grid">
                                        <button type="submit"  class="btn btn-primary">Announce</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <hr>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <h5 class="mt-4 mb-3 font-semibold">Announced Numbers ({{ \App\Models\Announcement::where('game_id', $game->id)->count() }}/90)</h5>
                                <div class="overflow-auto">
                                    <table class="w-full border-collapse">
                                        <tbody>
                                            @php
                                                $announcedNumbers = \App\Models\Announcement::where('game_id', $game->id)
                                                    ->orderBy('created_at', 'asc')
                                                    ->pluck('number')
                                                    ->toArray();

                                                $rows = array_chunk($announcedNumbers, 9);
                                                $emptyCells = array_fill(0, 9 - (count($announcedNumbers) % 9), null);

                                                if(count($announcedNumbers) % 9 != 0) {
                                                    $rows[count($rows)-1] = array_merge($rows[count($rows)-1], $emptyCells);
                                                }

                                                // Pad rows to make exactly 10 rows
                                                while(count($rows) < 10) {
                                                    $rows[] = array_fill(0, 9, null);
                                                }
                                            @endphp

                                            @foreach($rows as $row)
                                                <tr>
                                                    @foreach($row as $number)
                                                        <td class="p-1 text-center align-middle">
                                                            @if($number)
                                                                <span class="inline-block badge rounded-pill bg-primary text-white px-2 py-1 text-sm min-w-[32px]">
                                                                    {{ $number }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mt-4">
                                    @if (session()->has('success'))
                                        <div class="card-body">
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
                                    @endif
                                    @if (session()->has('error'))
                                            <div class="card-body">
                                                <div class="alert border-0 bg-danger  alert-dismissible fade show py-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="fs-3 text-white"><i class="bi bi-x-circle-fill"></i>
                                                        </div>
                                                        <div class="ms-3">
                                                            <div class="text-white">{{ session('error') }}</div>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                                </div>
                                            </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-3 col-xl-3">
                <div class="card radius-10">
                    <div class="card-body">
                        <div class="row g-3 align-items-center">
                            <div class="col-9">
                            <h5 class="mb-0">Participants</h5>
                            </div>
                            <div class="col-3">
                            <div class="d-flex align-items-center justify-content-end gap-3 cursor-pointer">
                                <div class="dropdown">
                                <a class="dropdown-toggle dropdown-toggle-nocaret" href="#" data-bs-toggle="dropdown" aria-expanded="false"><i class="bx bx-dots-horizontal-rounded font-22 text-option"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="javascript:;">Action</a>
                                    </li>
                                    <li><a class="dropdown-item" href="javascript:;">Another action</a>
                                    </li>
                                    <li>
                                    <hr class="dropdown-divider">
                                    </li>
                                    <li><a class="dropdown-item" href="javascript:;">Something else here</a>
                                    </li>
                                </ul>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                    <div class="client-message ps ps--active-y">
                        {{-- forloop start --}}
                            <div class="d-flex align-items-center gap-3 client-messages-list border-bottom border-top p-3">
                                <img src="assets/images/avatars/avatar-1.png" class="rounded-circle" width="50" height="50" alt="">
                                <div>
                                    <h6 class="mb-0">Thomas Hardy <span class="text-secondary mb-0 float-end font-13">21 July</span></h6>
                                    <p class="mb-0 font-13">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                                </div>
                            </div>
                        {{-- for loop end --}}
                        <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
                            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
                        </div>
                        <div class="ps__rail-y" style="top: 0px; height: 565px; right: 0px;">
                            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 349px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- <section class="py-4">
            <div class="container">
                <div class="row g-4">

                    <div class="col-12">

                        <div class="row g-4">


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


                        </div>
                    </div>
                </div>
            </div>
        </section> --}}
        {{-- <section class="py-4">
            <div class="container">
                    @if (session()->has('success'))
                        <div class="card-body">
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
                    @endif

                    @if (session()->has('error'))
                            <div class="card-body">
                                <div class="alert border-0 bg-danger  alert-dismissible fade show py-2">
                                    <div class="d-flex align-items-center">
                                        <div class="fs-3 text-white"><i class="bi bi-x-circle-fill"></i>
                                        </div>
                                        <div class="ms-3">
                                            <div class="text-white">{{ session('error') }}</div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            </div>
                    @endif
                <div class="card">
					<div class="card-body">
						<h4 class="mb-0">Announce Number for {{ $game->title }}</h4>
						<hr>
                        <form wire:submit.prevent="announceNumber">
						    <div class="row gy-3">
                                <div class="col-md-2">
                                    <select wire:model="selectedNumber" id="number" class="form-control">
                                        <option value="">Select Number</option>
                                        @for ($i = 1; $i <= 90; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2 text-end d-grid">
                                    <button type="submit"  class="btn btn-primary">Announce</button>
                                </div>
						    </div>
                        </form>
						<div class="form-row mt-3">
							<div class="col-9">
								<h5 class="mt-4 mb-3 font-semibold">Announced Numbers ({{ \App\Models\Announcement::where('game_id', $game->id)->count() }}/90)</h5>

                                <div class="overflow-auto">
                                    <table class="w-full border-collapse">
                                        <tbody>
                                            @php
                                                $announcedNumbers = \App\Models\Announcement::where('game_id', $game->id)
                                                    ->orderBy('created_at', 'asc')
                                                    ->pluck('number')
                                                    ->toArray();

                                                $rows = array_chunk($announcedNumbers, 9);
                                                $emptyCells = array_fill(0, 9 - (count($announcedNumbers) % 9), null);

                                                if(count($announcedNumbers) % 9 != 0) {
                                                    $rows[count($rows)-1] = array_merge($rows[count($rows)-1], $emptyCells);
                                                }

                                                // Pad rows to make exactly 10 rows
                                                while(count($rows) < 10) {
                                                    $rows[] = array_fill(0, 9, null);
                                                }
                                            @endphp

                                            @foreach($rows as $row)
                                                <tr>
                                                    @foreach($row as $number)
                                                        <td class="p-1 text-center align-middle">
                                                            @if($number)
                                                                <span class="inline-block badge rounded-pill bg-primary text-white px-2 py-1 text-sm min-w-[32px]">
                                                                    {{ $number }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
							</div>
						</div>
					</div>
				</div>
            </div>
        </section> --}}
        {{-- <section>
                <div class="container py-4">
                    <h2 class="text-lg font-bold mb-4">Number Announcer Panel</h2>

                    <div class="mb-4">
                        <label for="manualNumber" class="block text-sm font-medium text-gray-700 mb-1">Manually Announce Number</label>
                        <div class="flex space-x-2">
                            <input type="number" wire:model="manualNumber" id="manualNumber" min="1" max="90" class="border rounded px-3 py-1 w-24">
                            <button wire:click="announceNumber" class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700">Announce</button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <button wire:click="autoAnnounce" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Auto Announce Next Number</button>
                    </div>

                    <div class="mb-4">
                        <h3 class="text-md font-semibold mb-2">Announced Numbers</h3>
                        <div class="grid grid-cols-9 gap-2 text-center">
                            @foreach ($announcedNumbers as $number)
                                <div class="bg-yellow-400 text-black font-bold py-1 rounded">
                                    {{ $number }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if ($lastAnnounced)
                        <div class="mt-6 text-lg font-bold text-green-600">
                            Last Announced: {{ $lastAnnounced }}
                        </div>
                    @endif
                </div>
        </section> --}}
    </main>



    @section('JS')
         @include('livewire.layout.backend.inc.js')
         <script src="{{ asset('backend/assets/js/index4.js') }}"></script>
    @endsection
</main>

