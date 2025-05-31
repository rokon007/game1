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
        {{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}
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
            <div class="container">
                <livewire:frontend.chat.online-status />

                    <div class="" id="chat-list-container">
                        <livewire:frontend.chat.chat-list />
                    </div>
                    <div class="" id="chat-box-container">
                        <livewire:frontend.chat.chat-box />
                    </div>

            </div>
        </div>

{{-- <script>
    // Create and preload sounds globally
    document.addEventListener('DOMContentLoaded', () => {
        console.log('DOM loaded, setting up sounds');

        // Create audio elements
        window.chatSounds = {
            messageSound: new Audio('/sounds/message-received.mp3'),
            typingSound: new Audio('/sounds/typing.mp3')
        };

        // Preload sounds
        window.chatSounds.messageSound.preload = 'auto';
        window.chatSounds.typingSound.preload = 'auto';

        // Test sound loading
        window.chatSounds.messageSound.addEventListener('canplaythrough', () => {
            console.log('Message sound loaded successfully');
        });

        window.chatSounds.typingSound.addEventListener('canplaythrough', () => {
            console.log('Typing sound loaded successfully');
        });

        // Test play (will be muted and reset immediately)
        try {
            window.chatSounds.messageSound.volume = 0;
            window.chatSounds.messageSound.play()
                .then(() => {
                    console.log('Sound test successful');
                    window.chatSounds.messageSound.pause();
                    window.chatSounds.messageSound.currentTime = 0;
                    window.chatSounds.messageSound.volume = 1;
                })
                .catch(e => {
                    console.error('Sound test failed:', e);
                });
        } catch (e) {
            console.error('Error testing sound:', e);
        }
    });

    document.addEventListener('livewire:init', () => {
        console.log('Livewire initialized, setting up Echo listeners');

        // Check if Echo is properly initialized
        if (typeof window.Echo !== 'undefined') {
            console.log('Echo is available in chat.blade.php');

            // Listen for online status updates
            window.Echo.channel('online-status')
                .listen('.UserOnlineStatus', (e) => {
                    console.log('UserOnlineStatus event received:', e);
                    Livewire.dispatch('echo:online-status,UserOnlineStatus', e);
                });

            // Debug available channels
            console.log('Available Echo channels:', window.Echo);
        } else {
            console.error('Echo is not defined. Make sure Laravel Echo is properly initialized.');
        }

        // Set up global event listeners
        window.addEventListener('beforeunload', () => {
            // This will trigger the dehydrate method in OnlineStatus component
            // which will set the user as offline
            console.log('Page unloading, setting user offline');
        });
    });
</script> --}}

    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
    @endsection
</div>
