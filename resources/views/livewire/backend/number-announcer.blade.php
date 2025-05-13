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
        <section class="py-4">
            <div class="container">
                <div class="row g-4">

                    <div class="col-12">
                        <!-- Counter START -->
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
        </section>
        <section class="py-4">
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









                {{-- <div class="space-y-4">
                    <h4 class="text-xl font-bold">Announce Number for {{ $game->title }}</h4>

                    <form wire:submit.prevent="announceNumber">
                        <label for="number">Select Number:</label>
                        <select wire:model="selectedNumber" id="number" class="border p-1">
                            <option value="">-- Select --</option>
                            @for ($i = 1; $i <= 90; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>

                        <button type="submit" class="btn btn-primary px-3 py-1 rounded">Announce</button>
                    </form>

                    @if (session()->has('success'))
                        <div class="text-green-600">{{ session('success') }}</div>
                    @endif

                    @if (session()->has('error'))
                        <div class="text-red-600">{{ session('error') }}</div>
                    @endif

                    <h5 class="mt-4 font-semibold">Announced Numbers:</h5>
                    <div class="flex flex-wrap gap-2">
                        @foreach (\App\Models\Announcement::where('game_id', $game->id)->pluck('number') as $num)
                            <span class="badge rounded-pill bg-primary px-2 py-1">{{ $num }}</span>
                        @endforeach
                    </div>
                </div> --}}
            </div>
        </section>
        <section>
                {{-- <div class="container py-4">
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
                </div> --}}
        </section>
    </main>



    @section('JS')
         @include('livewire.layout.backend.inc.js')
    @endsection
</main>

