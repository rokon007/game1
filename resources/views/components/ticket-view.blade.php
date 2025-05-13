@props(['numbers', 'announced'])

<div class="ticket grid grid-cols-5 gap-2 p-3 border rounded mb-4">
    @foreach($numbers as $cell)
        @php
            // নিশ্চিতভাবে সেল থেকে নাম্বার বের করি
            if (is_array($cell)) {
                if (array_key_exists('number', $cell)) {
                    $number = $cell['number'];
                } elseif (isset($cell[0])) {
                    $number = $cell[0];
                } else {
                    $number = null;
                }
            } else {
                $number = $cell;
            }

            // আবারও নিশ্চিত হই যে এটা scalar value
            if (is_array($number)) {
                $number = null;
            }
        @endphp

        <div class="text-center p-2 border rounded
            @if(in_array($number, $announced)) bg-green-300 @else bg-white @endif">
            {{ is_null($number) ? '-' : $number }}
        </div>
    @endforeach
</div>
