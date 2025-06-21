<div class="{{ $getCardClasses() }}"
     @if($clickable)
        style="cursor: pointer;"
     @endif>
    <div class="rank">{{ $getDisplayRank() }}</div>
    <div class="suit">{{ $getSuitSymbol() }}</div>

    <!-- Corner indicators for face cards -->
    @if(in_array($rank, ['K', 'Q', 'J']))
        <div class="absolute top-1 left-1 text-xs opacity-60">
            {{ $getDisplayRank() }}
        </div>
        <div class="absolute bottom-1 right-1 text-xs opacity-60 transform rotate-180">
            {{ $getDisplayRank() }}
        </div>
    @endif
</div>
