<!-- Number Announcement Modal -->
<div class="modal fade" id="number-announcement-modal" tabindex="-1" aria-labelledby="numberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="numberModalLabel">Number Announcement</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">
                <div id="number-spinner" class="number-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <div id="announced-number" class="announced-number d-none"></div>
            </div>
        </div>
    </div>
</div>

<!-- Add this to your number-announcer.blade.php file -->
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/number-announcer-modal.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/number-announcer-modal.js') }}"></script>
@endpush
