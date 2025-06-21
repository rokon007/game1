<div class="fixed top-4 right-4 z-50 max-w-sm w-full">
    <div class="border rounded-lg p-4 shadow-lg {{ $getClasses() }}" x-data="{ show: true }" x-show="show" x-transition>
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="{{ $getIcon() }} text-lg"></i>
            </div>
            <div class="ml-3 flex-1">
                <p class="text-sm font-medium">{{ $message }}</p>
            </div>
            <div class="ml-4 flex-shrink-0">
                <button @click="show = false" class="inline-flex text-gray-400 hover:text-gray-600 focus:outline-none">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-hide notification after 5 seconds
    setTimeout(() => {
        document.querySelector('[x-data]').__x.$data.show = false;
    }, 5000);
</script>
