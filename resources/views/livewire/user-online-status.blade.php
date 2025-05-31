<div>
    @if($isOnline)
        <span class="badge bg-success">Online</span>
    @else
        <span class="badge bg-secondary">
            Last seen: {{ $user->last_seen->diffForHumans() }}
        </span>
    @endif
</div>
