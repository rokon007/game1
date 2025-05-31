<div wire:init="loadOnlineUsers">
    <!-- This component doesn't have a visible UI, it just manages online status -->
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        if (typeof Echo !== 'undefined') {
            Echo.channel('online-status')
                .listen('.UserOnlineStatus', (e) => {
                    console.log('UserOnlineStatus event received in component:', e);
                });
        }
        console.log('OnlineStatus component initialized');
    });
</script>
