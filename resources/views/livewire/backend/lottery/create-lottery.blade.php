<main>
    @section('title')
        <title>Admin | লটারি তৈরি</title>
    @endsection
    
    @section('css')
        @include('livewire.layout.backend.inc.css')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"
              integrity="sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ=="
              crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
            .form-section {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 0 15px rgba(0,0,0,0.05);
                margin-bottom: 20px;
                padding: 20px;
            }
            .form-section-header {
                border-bottom: 1px solid #eee;
                padding-bottom: 15px;
                margin-bottom: 20px;
            }
            .ticket-item, .prize-item {
                background: #f9f9f9;
                border-radius: 6px;
                padding: 15px;
                margin-bottom: 15px;
            }
        </style>
    @endsection

    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-4">
            <div class="breadcrumb-title pe-3">লটারি ব্যবস্থাপনা</div>
            <div class="ps-3">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 p-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}"><i class="bx bx-home-alt"></i></a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.lottery.index') }}">লটারি তালিকা</a></li>
                        <li class="breadcrumb-item active" aria-current="page">নতুন লটারি</li>
                    </ol>
                </nav>
            </div>
            <div class="ms-auto">
                <a href="{{ route('admin.lottery.index') }}" class="btn btn-sm btn-secondary">
                    <i class="bx bx-arrow-back"></i> ফিরে যান
                </a>
            </div>
        </div>
        <!--end breadcrumb-->
        
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">নতুন লটারি তৈরি করুন</h4>
                </div>
                
                <form wire:submit.prevent="save">
                    <!-- Basic Information Section -->
                    <div class="form-section">
                        <div class="form-section-header">
                            <h5 class="mb-0">প্রাথমিক তথ্য</h5>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">লটারির নাম <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="name" placeholder="লটারির নাম লিখুন" required>
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">টিকিটের মূল্য <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control" wire:model="price" min="1" step="0.01" placeholder="টিকিটের মূল্য" required>
                                    <span class="input-group-text">৳</span>
                                </div>
                                @error('price') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ড্র এর তারিখ <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model="draw_date" required>
                                @error('draw_date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ড্র এর সময় <span class="text-danger">*</span></label>
                                <input type="time" class="form-control" wire:model="draw_time" required>
                                @error('draw_time') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="auto_draw" id="auto_draw">
                                    <label class="form-check-label" for="auto_draw">স্বয়ংক্রিয় ড্র</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pre-selected Winners Section -->
                    <div class="form-section">
                        <div class="form-section-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">পূর্ব-নির্ধারিত বিজয়ী</h5>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model.live="enablePreSelection" id="enablePreSelection">
                                <label class="form-check-label" for="enablePreSelection">সক্রিয় করুন</label>
                            </div>
                        </div>
                        
                        @if($enablePreSelection)
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <strong>নোট:</strong> যদি টিকিট বিক্রি কম হয় এবং লোকসানের সম্ভাবনা থাকে, তাহলে এই পূর্ব-নির্ধারিত বিজয়ীরা প্রাইজ পাবে।
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">পূর্ব-নির্ধারিত বিজয়ী তালিকা</h6>
                                <button type="button" class="btn btn-sm btn-primary" wire:click="addPreSelectedTicket">
                                    <i class="bx bx-plus"></i> বিজয়ী যোগ করুন
                                </button>
                            </div>

                            @foreach($preSelectedTickets as $index => $ticket)
                                <div class="ticket-item">
                                    <div class="row g-3">
                                        <div class="col-md-5">
                                            <label class="form-label">প্রাইজ পজিশন</label>
                                            <select class="form-select" wire:model="preSelectedTickets.{{ $index }}.prize_position">
                                                <option value="">প্রাইজ নির্বাচন করুন</option>
                                                @foreach($prizes as $prizeIndex => $prize)
                                                    @if(!empty($prize['position']))
                                                        <option value="{{ $prize['position'] }}">{{ $prize['position'] }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            @error("preSelectedTickets.{$index}.prize_position") 
                                                <small class="text-danger">{{ $message }}</small> 
                                            @enderror
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">টিকিট নম্বর</label>
                                            <input type="text" class="form-control" 
                                                   wire:model="preSelectedTickets.{{ $index }}.ticket_number" 
                                                   placeholder="12345678" maxlength="8">
                                            @error("preSelectedTickets.{$index}.ticket_number") 
                                                <small class="text-danger">{{ $message }}</small> 
                                            @enderror
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end">
                                            <button type="button" class="btn btn-sm btn-danger w-100" 
                                                    wire:click="removePreSelectedTicket({{ $index }})">
                                                <i class="bx bx-trash"></i> মুছুন
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <!-- Prize Setup Section -->
                    <div class="form-section">
                        <div class="form-section-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">প্রাইজ সেটআপ</h5>
                            <button type="button" class="btn btn-sm btn-primary" wire:click="addPrize">
                                <i class="bx bx-plus"></i> প্রাইজ যোগ করুন
                            </button>
                        </div>
                        
                        @foreach($prizes as $index => $prize)
                            <div class="prize-item">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label">পজিশন</label>
                                        <input type="text" class="form-control" 
                                               wire:model="prizes.{{ $index }}.position" 
                                               placeholder="১ম, ২য়, ৩য়">
                                        @error("prizes.{$index}.position") 
                                            <small class="text-danger">{{ $message }}</small> 
                                        @enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">প্রাইজের পরিমাণ</label>
                                        <div class="input-group">
                                            <input type="number" class="form-control" 
                                                   wire:model="prizes.{{ $index }}.amount" 
                                                   min="1" step="0.01">
                                            <span class="input-group-text">৳</span>
                                        </div>
                                        @error("prizes.{$index}.amount") 
                                            <small class="text-danger">{{ $message }}</small> 
                                        @enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">র‍্যাঙ্ক</label>
                                        <input type="number" class="form-control" 
                                               wire:model="prizes.{{ $index }}.rank" 
                                               min="1">
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        @if(count($prizes) > 1)
                                            <button type="button" class="btn btn-sm btn-danger w-100" 
                                                    wire:click="removePrize({{ $index }})">
                                                <i class="bx bx-trash"></i> মুছুন
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <button type="reset" class="btn btn-secondary">রিসেট</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-save"></i> লটারি তৈরি করুন
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @section('JS')
        @include('livewire.layout.backend.inc.js')
        <script src="{{ asset('backend/assets/js/index4.js') }}"></script>
    @endsection
</main>