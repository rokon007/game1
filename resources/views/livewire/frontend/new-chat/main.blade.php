<div>
    @section('meta_description')
      <meta name="description" content="Housieblitz Chat">
    @endsection
    @section('title')
        <title>Housieblitz|Chat</title>
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
            /* w-full - Sets width to 100% of parent */
                .w-full {
                width: 100%;
                }

                /* h-full - Sets height to 100% of parent */
                .h-full {
                height: 100%;
                }

                /* flex - Enables flexbox layout */
                .flex {
                display: flex;
                }

                /* items-center - Aligns flex items vertically center */
                .items-center {
                align-items: center;
                }

                /* justify-center - Centers flex items horizontally */
                .justify-center {
                justify-content: center;
                }

                /* text-lg - Sets font size to large (typically 1.125rem or 18px) */
                .text-lg {
                font-size: 1.125rem;
                line-height: 1.75rem;
                }

                /* font-bold - Sets font weight to bold (700) */
                .font-bold {
                font-weight: 700;
                }

                /* object-cover - For images to cover their container */
                .object-cover {
                object-fit: cover;
                }
        </style>
    @endsection

    @section('preloader')

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
            <div class="container">
                 @if(auth()->user())

                 <div style="display :{{$searchMode ? 'block' : 'none'}};">
                    <div class="col-md-12 border-end  overflow-auto" style="min-width: 300px;">
                        <!-- Search -->
                        <div class="p-3 border-bottom">
                            <input
                                type="search"
                                wire:model.live.debounce.500ms="searchUser"
                                placeholder="Search users..."
                                class="form-control w-100 py-2 ps-5 bg-light rounded border-0 outline-none"
                            >
                        </div>
                        <!-- New Users -->
                        @if(!empty($searchUser))
                            <div class="mt-1">
                                <div class="p-3">
                                    <h6 class="fw-semibold mb-2">Start a new conversation</h6>


                                        @foreach($result as $results)
                                            <div
                                                wire:click="createConversation({{$results->id}})"
                                                class="d-flex align-items-center gap-3 p-2 rounded hover-shadow bg-white mt-2 mb-2"
                                                style="cursor: pointer;"
                                                >
                                                <!-- Avatar -->
                                                <div class="position-relative">
                                                    @if($results->avatar)
                                                        <img src="{{ $results->avatar }}" class="rounded-circle" width="40" height="40" alt="{{ $results->name }}">
                                                    @else
                                                        <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <strong>{{ strtoupper(substr($results->name, 0, 1)) }}</strong>
                                                        </div>
                                                    @endif


                                                </div>

                                                <!-- Info -->
                                                <div class="flex-grow-1">
                                                    <div class="fw-medium">{{ $results->name }}</div>

                                                </div>
                                            </div>
                                        @endforeach
                                        @if(count($result) <= 0)
                                            <div class="d-flex align-items-center gap-3 p-2 rounded hover-shadow bg-white mt-2 mb-2" >
                                                <!-- Avatar -->
                                                <div class="position-relative">

                                                        <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                            <strong>X</strong>
                                                        </div>



                                                </div>

                                                <!-- Info -->
                                                <div class="flex-grow-1">
                                                    <div class="fw-medium">No Users found!</div>

                                                </div>
                                            </div>
                                        @endif

                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                    @if($chatList)
                        <div class="" id="chat-list-container">
                            @livewire('frontend.new-chat.chatlist')
                        </div>
                    @endif

                        <div style="display: {{$chatBox ? 'block' : 'none'}}" id="chat-box-container">
                            @livewire('frontend.new-chat.chatbox')
                            @livewire('frontend.new-chat.sendmessage')
                        </div>

                @else
                    <div class="d-flex justify-content-center py-60">
                    <strong>Housieblitz|Chat!<br>
                        <a class="text-primary" href='/login' LOG IN>LOG IN </a> TO THE Housieblitz TO START CHATTING.</strong>
                    </div>
                @endif
            </div>
        </div>



    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>












{{-- <div>
 @if(auth()->user())
  <br>
    <div class="container mx-auto">
      <div class="w-100 border rounded d-lg-flex" >
        <div class="border-end border-gray-300 col-lg-4">
          <div class="m-3">
            <div class="position-relative">


              <form>
                <input wire:model.live.debounce.500ms="searchUser" type="search" class="w-100 py-2 ps-5 bg-light rounded border-0 outline-none" name="search"
                    placeholder="Search Users" required />
              </form>

             @if(!empty($searchUser))
                @foreach($result as $results)
                    <div class="dropdown-menu d-block py-1 show">
                        <div class="px-2 py-1 border-bottom">
                            <div class="d-flex align-items-center ms-3">
                                <img class="h-26px" src="{{ asset('storage/'. $results->image) }}">
                                <span class="ms-2"> {{$results->name}} <button class="bg-primary text-white fw-bold py-0 px-2 rounded-pill" wire:click="createConversation({{$results->id}})">chat</button></span>
                                <small></small>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if(count($result) <= 0)
                    <div class="dropdown-menu d-block py-1 show">
                        <div class="px-3 py-2 border-bottom">
                            <div class="d-flex flex-column ms-3">
                                <span>No Users found!</span>
                            </div>
                        </div>
                    </div>
                @endif
             @endif
          </div>
            </div>
          </div>
          @livewire('frontend.new-chat.chatlist')
        </div>
            <div class="d-none d-lg-block col-lg-8">
            <div class="w-100">
                @livewire('frontend.new-chat.chatbox')
                @livewire('frontend.new-chat.sendmessage')
            </div>
            </div>
      </div>


    @else
    <div class="d-flex justify-content-center py-60">
    <strong>chatappLARAVEL IS A CHAT APPLICATION BUILT WITH LARAVEL & LIVEWIRE. BUILT WITH REALTIME COMMUNICATION USING PUSHER CHANNEL!<br>
        <a class="text-primary" href='/login' LOG IN>LOG IN </a> TO THE APPLICATION TO START CHATTING.</strong>
    </div>

    @endif

</div> --}}
