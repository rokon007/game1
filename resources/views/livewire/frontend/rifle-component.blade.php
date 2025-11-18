<div>
    @section('meta_description')
        <meta name="title" content="Housieblitz Refile your Account">
        <meta name="description" content="Join the ultimate multiplayer Housieblitz Game! Buy tickets, play real-time, and win exciting rewards. Register now!">
        <meta name="keywords" content="Housieblitz game, multiplayer bingo, play online game, win prizes, real-time game, ticket based game">
        <meta name="author" content="Housieblitz">
    @endsection
    @section('title')
        <title>{{ config('app.name', 'Laravel') }} | Refile</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
        <style>
            .custom-badge {
                position: absolute;
                top: 10px;
                right: 10px;
                background-color: #ffc107;
                color: #fff;
                padding: 5px 10px;
                font-size: 12px;
                border-radius: 50px;
            }
            .currency-icon {
                display: inline-block;
                vertical-align: middle;
                margin-right: 1px;
            }
            /* Number copy styles - Simple and Safe */
            .number-item {
                margin-bottom: 8px;
                padding: 5px 0;
            }
            .copy-btn {
                cursor: pointer;
                font-size: 0.7rem;
                padding: 2px 8px;
                margin-left: 8px;
                background: #007bff;
                color: white;
                border: none;
                border-radius: 3px;
            }
            .copy-btn:hover {
                background: #0056b3;
            }
            .copy-btn.copied {
                background: #28a745;
            }
            .toast-message {
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #28a745;
                color: white;
                padding: 10px 15px;
                border-radius: 4px;
                z-index: 9999;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            }
        </style>
        <style>
            /* Previous styles... */

            /* Submit Section Textbox Styles */
            .custom-input {
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                padding: 12px 15px;
                font-size: 16px;
                transition: all 0.3s ease;
                background-color: #f9f9f9;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }

            .custom-input:focus {
                border-color: #007bff;
                background-color: #ffffff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
                outline: none;
            }

            .custom-input:hover {
                border-color: #b3b3b3;
            }

            .coupon-form {
                margin-bottom: 15px;
            }

            .coupon-form p {
                font-weight: 500;
                color: #333;
                margin-bottom: 8px;
            }

            /* File input custom styling */
            .custom-input[type="file"] {
                padding: 10px;
                background-color: #f8f9fa;
            }

            .custom-input[type="file"]:focus {
                background-color: #ffffff;
            }
        </style>
    @endsection

    @section('preloader')
        {{-- <livewire:layout.frontend.preloader /> --}}
    @endsection

    @section('header')
        <livewire:layout.frontend.header />
    @endsection

    @section('offcanvas')
        <livewire:layout.frontend.offcanvas />
    @endsection

    @section('pwa_alart')
        <livewire:layout.frontend.pwa_alart />
    @endsection

    <div class="page-content-wrapper">
        <!-- ruleSection অংশ -->
        @if($ruleSection)
            <div class="container">
                <div class="profile-wrapper-area py-3">
                    <!-- User Information-->
                    <div class="card user-info-card">
                        <div class="card-body p-4 ">
                            <div class="text-center">
                                <h5 class="text-white text-center">ব্যালেন্স রিফিল করার নিয়ম</h5>
                            </div>
                        </div>
                    </div>
                    <!-- User Meta Data-->
                    <div class="card user-data-card">
                        <div class="card-body">
                            <div class="balance-refill-instruction card p-4">
                                <h4 class="mb-3 text-primary">How to Refill Your Balance</h4>
                                <ol class="list-group list-group-numbered mb-3">
                                    <li class="list-group-item">
                                        <!-- যদি আলাদা আলাদা নাম্বার থাকে -->
                                        @if($refillSettings && ($bikash_number !== $nagad_number || $bikash_number !== $rocket_number || $bikash_number !== $upay_number))
                                            <div class="mt-2">
                                                <strong>Send money to this numbers:</strong><br>
                                                @if($bikash_number)
                                                    <div class="number-item">
                                                        <span class="badge bg-danger text-dark">bKash</span>: {{ $bikash_number }}
                                                        <button class="copy-btn" onclick="copyToClipboard('{{ $bikash_number }}', this)">
                                                            <i class="fas fa-copy"></i> Copy
                                                        </button>
                                                    </div>
                                                @endif
                                                @if($nagad_number)
                                                    <div class="number-item">
                                                        <span class="badge" style="background-color:#FF8C00;color:black;">Nagad</span>: {{ $nagad_number }}
                                                        <button class="copy-btn" onclick="copyToClipboard('{{ $nagad_number }}', this)">
                                                            <i class="fas fa-copy"></i> Copy
                                                        </button>
                                                    </div>
                                                @endif
                                                @if($rocket_number)
                                                    <div class="number-item">
                                                        <span class="badge" style="background-color:#6495ED">Rocket</span>: {{ $rocket_number }}
                                                        <button class="copy-btn" onclick="copyToClipboard('{{ $rocket_number }}', this)">
                                                            <i class="fas fa-copy"></i> Copy
                                                        </button>
                                                    </div>
                                                @endif
                                                @if($upay_number)
                                                    <div class="number-item">
                                                        <span class="badge bg-warning">Upay</span>: {{ $upay_number }}
                                                        <button class="copy-btn" onclick="copyToClipboard('{{ $upay_number }}', this)">
                                                            <i class="fas fa-copy"></i> Copy
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        @else
                                            <div class="mt-2">
                                                Send money to this number: <strong>{{ $bikash_number ?? '017XXXXXXXX' }}</strong>
                                                <button class="copy-btn" onclick="copyToClipboard('{{ $bikash_number ?? '017XXXXXXXX' }}', this)">
                                                    <i class="fas fa-copy"></i> Copy
                                                </button>
                                                using
                                                <span class="badge bg-danger text-dark">bKash</span>,
                                                <span class="badge" style="background-color:#FF8C00;color:black;">Nagad</span>,
                                                <span class="badge" style="background-color:#6495ED">Rocket</span>, or
                                                <span class="badge bg-warning">Upay</span>.
                                            </div>
                                        @endif
                                    </li>
                                    <li class="list-group-item">
                                        After the transaction is successful, take a screenshot of the confirmation message.
                                    </li>
                                    <li class="list-group-item">
                                        Copy the <strong>Transaction ID</strong> from the confirmation message.
                                    </li>
                                    <li class="list-group-item">
                                        Click the <strong>"Next"</strong> button to proceed to the next step.
                                    </li>
                                </ol>
                                @if($refillSettings && $refillSettings->instructions)
                                    <div class="alert alert-info">
                                        <strong>Note:</strong> {{ $refillSettings->instructions }}
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <strong>Note:</strong> Please double-check the transaction number before proceeding.
                                    </div>
                                @endif
                            </div>
                            <button class="btn btn-primary btn-lg w-100" wire:click='nextToPaymentMethod'>Next</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Payment Method Section -->
        @if($paymentMethodSection)
            <div class="container">
                <div class="profile-wrapper-area py-3">
                    <div class="card user-info-card">
                        <div class="card-body p-4 ">
                            <div class="text-center">
                                <h5 class=" text-white text-center">Please select the payment method you used.</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="container">
                <!-- Checkout Wrapper-->
                <div class="checkout-wrapper-area py-3">
                <!-- Choose Payment Method-->
                <div class="choose-payment-method">
                    <div class="row g-2 justify-content-center rtl-flex-d-row-r">
                    <!-- Single Payment Method-->
                        <div class="col-6 col-md-5">
                            <div class="single-payment-method">
                                <a class="cash" wire:click='paymentBikash'>
                                    <img src="{{asset('assets/frontend/img/paymentmethod/bikash.png') }}" alt="Image" width="150" />
                                </a>
                            </div>
                        </div>
                        <!-- Single Payment Method-->
                        <div class="col-6 col-md-5">
                            <div class="single-payment-method">
                                <a class="cash" wire:click='paymentNagad'>
                                    <img src="{{asset('assets/frontend/img/paymentmethod/nagad.png') }}" alt="Image" width="150" />
                                </a>
                            </div>
                        </div>
                        <!-- Single Payment Method-->
                        <div class="col-6 col-md-5">
                            <div class="single-payment-method">
                                <a class="cash" wire:click='paymentRoket'>
                                    <img src="{{asset('assets/frontend/img/paymentmethod/roket.png') }}" alt="Image" height="50" width="150" />
                                </a>
                            </div>
                        </div>
                    <!-- Single Payment Method-->
                        <div class="col-6 col-md-5">
                            <div class="single-payment-method">
                                <a class="cash" wire:click='paymentUpay'>
                                    <img src="{{asset('assets/frontend/img/paymentmethod/upay.png') }}" alt="Image" width="100" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
        @endif

        <!-- Submit Section -->
        @if($submitSection)
            <div class="container">
            <!-- Cart Wrapper-->
            <div class="cart-wrapper-area py-3">
                @if ($data_id)
                    <form wire:submit.prevent="updateRifleRequests">
                @else
                    <form wire:submit.prevent="saveRifleRequests">
                @endif
                    <div class="card mb-3">
                    <div class="card-body">
                        <!-- Show loading spinner during photo upload -->
                        <div wire:loading.delay.short wire:target="photo1">
                            <center>
                                <iframe
                                    src="https://giphy.com/embed/aqd1tYU4WvlO3FiYvo"
                                    width="200"
                                    height="200"
                                    frameborder="0"
                                    class="giphy-embed"
                                    allowfullscreen>
                                </iframe>
                            </center>
                        </div>

                        <!-- Image Preview Section -->
                        <div wire:loading.remove wire:target="photo1">
                            <center>
                                @if ($photo1)
                                    <img src="{{ $photo1->temporaryUrl() }}" height="200" width="200" alt="Uploaded Image Preview">
                                @elseif ($data_id)
                                    <img src="{{ Storage::url($screenshot) }}" height="200" width="200" alt="Uploaded Image Preview">
                                @else
                                    <img src="{{ asset('backend/upload/image/upload.png') }}" alt="Default Image" height="200" width="200">
                                @endif
                            </center>
                        </div>

                        <div class="apply-coupon">
                            <p class="mb-2">Upload your screen shot here</p>
                            <div class="coupon-form">
                                <input class="form-control custom-input" wire:model="photo1" type="file">
                                @error('photo1')
                                    <small class="text-danger mb-2">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>
                    </div>
                    <!-- Coupon Area-->
                    <div class="card coupon-card mb-3">
                    <div class="card-body">
                        <div class="apply-coupon">
                            <p class="mb-2">Sending method</p>
                        <div class="coupon-form">
                            <input class="form-control custom-input" wire:model='sending_method' type="text" placeholder="Enter sending method (bKash, Nagad, etc.)">
                            @error('sending_method')
                                    <small class="text-danger mb-2">{{ $message }}</small>
                                @enderror
                        </div>
                        <p class="mb-2 mt-3">Sender mobile</p>
                        <div class="coupon-form">
                            <input class="form-control custom-input" inputmode="numeric" pattern="[0-9]*" wire:model='sending_mobile' type="text" placeholder="Enter sender mobile number">
                            @error('sending_mobile')
                                    <small class="text-danger mb-2">{{ $message }}</small>
                                @enderror
                        </div>

                        <p class="mb-2 mt-3">Transaction id</p>
                        <div class="coupon-form">
                            <input class="form-control custom-input" wire:model='transaction_id' type="text" placeholder="Enter transaction ID">
                            @error('transaction_id')
                                    <small class="text-danger mb-2">{{ $message }}</small>
                                @enderror
                        </div>
                        <p class="mb-2 mt-3">Amount</p>
                        <div class="coupon-form">
                            <input class="form-control custom-input" wire:model='amount_rifle' type="number" step="0.01" inputmode="decimal" id="amount"  placeholder="Enter amount">
                            @error('amount_rifle')
                                    <small class="text-danger mb-2">{{ $message }}</small>
                                @enderror
                        </div>
                        </div>
                    </div>
                    </div>
                    <!-- Cart Amount Area-->
                    <div class="card cart-amount-area">
                    <div class="card-body ">
                        <center>
                            @if ($data_id)
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.delay.long wire:target="updateRifleRequests" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                Resubmit
                            </button>
                            @else
                                <button type="submit" class="btn btn-primary">
                                    <span wire:loading.delay.long wire:target="saveRifleRequests" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                                    Submit
                                </button>
                            @endif
                        </center>
                    </div>
                    </div>
                </form>
            </div>
            </div>
        @endif

        <!-- Request Status Section -->
        @if($requestStatus)
            <div class="container">
                <!-- Cart Wrapper-->
                <div class="cart-wrapper-area py-3">
                <div class="cart-table card mb-3">
                    <div class="table-responsive card-body">
                    <table class="table mb-0">
                        <tbody>
                            @if($rifleStatus)
                                @foreach($rifleStatus as $item)
                                    <tr>
                                        <th scope="row">
                                            <a class="product-title d-flex align-items-center gap-2">
                                                @if($item->status === 'Pending')
                                                    <i style="font-size: 30px" class="ti ti-clock text-warning"></i>
                                                    <span class="mt-1">Pending</span>
                                                @elseif($item->status === 'Cancelled')
                                                    <i style="font-size: 30px" class="ti ti-circle-x text-danger"></i>
                                                    <span class="mt-1 text-danger">Cancelled</span>
                                                @endif
                                            </a>
                                            @if ($item->status === 'Cancelled')
                                                <div class="mt-1 d-flex align-items-center gap-2">
                                                    <button class="btn btn-sm btn-warning" wire:click='resubmit({{$item->id}})'>Resubmit</button>
                                                    <button class="btn btn-sm btn-danger" wire:click='delet({{$item->id}})'>Delet</button>
                                                </div>
                                            @endif
                                        </th>
                                        <td>
                                            <img class="rounded" src="{{ Storage::url($item->screenshot) }}" alt="">
                                        </td>
                                        <td>
                                            <a class="product-title">
                                                {{$item->sending_method}}
                                                <span class="mt-1">Amount {{$item->amount_rifle}} Tk</span>
                                                <span class="mt-1">Transaction id : {{$item->transaction_id}} </span>
                                            </a>
                                        </td>
                                        <td>
                                        <div class="quantity">
                                            <a class="product-title">
                                                Sender mobile
                                                <span class="mt-1"> {{$item->sending_mobile}} </span>
                                            </a>
                                        </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                    </div>
                </div>

                <!-- Cart Amount Area-->
                <div class="card cart-amount-area">
                    <div class="card-body">
                        <center>
                            <a class="btn btn-primary" style="cursor: pointer" wire:click='newRequest'>Create a new rifle request </a>
                        </center>
                    </div>
                </div>
                </div>
            </div>
        @endif

        <!-- Delete Modal -->
        @if($deletModal)
            <div class="modal fade show d-block" tabindex="-1" role="dialog" style="background-color: rgba(0,0,0,0.5)">
                <div class="modal-dialog" role="document">
                    <div class="modal-content border-danger">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title">🗑️ Delete Confirmation Message:</h5>
                        </div>
                        <div class="modal-body">
                                <p>This action cannot be undone. Are you sure you want to delete this item?</p>
                        </div>
                        <div class="modal-footer">
                            <button wire:click="deletData" class="btn btn-danger">OK</button>
                            <button class="btn btn-secondary" wire:click="$set('deletModal', false)">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    @section('footer')
        <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')

        <!-- Simple and Safe Copy Function -->
        <script>
            function copyToClipboard(text, button) {
                // Create a temporary textarea element
                const textArea = document.createElement('textarea');
                textArea.value = text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    // Try to copy using modern API
                    if (navigator.clipboard && window.isSecureContext) {
                        navigator.clipboard.writeText(text).then(function() {
                            showCopySuccess(button);
                        }).catch(function(err) {
                            // Fallback for older browsers
                            fallbackCopy(text, button);
                        });
                    } else {
                        // Fallback for older browsers
                        fallbackCopy(text, button);
                    }
                } catch (err) {
                    // Final fallback
                    fallbackCopy(text, button);
                } finally {
                    // Clean up
                    document.body.removeChild(textArea);
                }
            }

            function fallbackCopy(text, button) {
                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        showCopySuccess(button);
                    } else {
                        showCopyError();
                    }
                } catch (err) {
                    showCopyError();
                }
            }

            function showCopySuccess(button) {
                // Change button appearance
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-check"></i> Copied!';
                button.classList.add('copied');

                // Show simple toast message
                showToast('Number copied successfully!');

                // Reset button after 2 seconds
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('copied');
                }, 2000);
            }

            function showCopyError() {
                showToast('Failed to copy. Please copy manually.', 'error');
            }

            function showToast(message, type = 'success') {
                // Remove existing toast
                const existingToast = document.querySelector('.toast-message');
                if (existingToast) {
                    existingToast.remove();
                }

                // Create new toast
                const toast = document.createElement('div');
                toast.className = 'toast-message';
                toast.style.background = type === 'success' ? '#28a745' : '#dc3545';
                toast.textContent = message;

                document.body.appendChild(toast);

                // Remove toast after 3 seconds
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 3000);
            }

            // Initialize when page loads
            document.addEventListener('DOMContentLoaded', function() {
                console.log('Copy functionality loaded successfully');
            });
        </script>
    @endsection
</div>
