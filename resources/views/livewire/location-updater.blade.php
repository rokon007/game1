<div>
    <h5>📍 My Location Info</h5>
    <p><strong>Latitude:</strong> {{ $latitude ?? 'Unknown' }}</p>
    <p><strong>Longitude:</strong> {{ $longitude ?? 'Unknown' }}</p>

    {{-- এখানে আর বাটন নেই --}}

    <script>
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    window.Livewire.dispatch('updateLocation', {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    });
                }, function(error) {
                    console.warn("Location access denied or failed.");
                });
            } else {
                console.warn("Geolocation is not supported by this browser.");
            }
        }

        // পেজ লোডের পর অটো কল হবে
        document.addEventListener("DOMContentLoaded", function () {
            getLocation();
        });
    </script>
</div>
