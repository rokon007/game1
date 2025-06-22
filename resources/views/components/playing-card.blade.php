<div class="{{ $getCardClasses() }}"
     @if($clickable)
        style="cursor: pointer;"
     @endif>

    <div class="card-content">
        <!-- Top left corner -->
        <div class="card-corner-top">
            <div class="rank">{{ $getDisplayRank() }}</div>
            <div class="suit">{{ $getSuitSymbol() }}</div>
        </div>

        <!-- Center symbol -->
        <div class="card-center">
            <div class="center-suit">{{ $getSuitSymbol() }}</div>
        </div>

        <!-- Bottom right corner (rotated) -->
        <div class="card-corner-bottom">
            <div class="rank">{{ $getDisplayRank() }}</div>
            <div class="suit">{{ $getSuitSymbol() }}</div>
        </div>
    </div>
</div>
