<span>{{ $unreadCount }}</span>
@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    Livewire.on('play-message-sound', () => {
        // শুধু অন্য ইউজারের মেসেজের জন্য সাউন্ড প্লে করবে
        const audio = new Audio('{{ asset('sounds/notification.mp3') }}');
        audio.play().catch(e => console.log('Audio play error:', e));
    });
});
</script>
@endpush
