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

<!-- Winner Modal -->
<div class="modal fade" id="winner-modal" tabindex="-1" aria-labelledby="winnerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="winnerModalLabel">Congratulations!</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body winner-modal-content">
                <div class="trophy-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h2 id="winner-title" class="winner-title">Congratulations!</h2>
                <p id="winner-message" class="winner-message">You've won!</p>
                <div class="confetti-container">
                    <!-- Confetti elements will be added via JS -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add this to your game-room.blade.php file -->

