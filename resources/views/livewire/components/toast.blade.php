<div>
    <style>
    .toast-container {
    position: fixed;
    top: 1.25rem;
    right: 1.25rem;
    z-index: 9999;
    width: 100%;
    max-width: 384px;
    pointer-events: none;
}

.toast-box {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    pointer-events: auto;
    /* background-color: #d1fae5; */
}

/* .toast-success {
    background-color: #d1fae5;
    color: #065f46;
    border-color: #a7f3d0;
}
.toast-error {
    background-color: #fee2e2;
    color: #991b1b;
    border-color: #fecaca;
}
.toast-info {
    background-color: #dbeafe;
    color: #1e40af;
    border-color: #bfdbfe;
}
.toast-warning {
    background-color: #fef9c3;
    color: #92400e;
    border-color: #fef08a;
} */

.toast-message {
    font-size: 14px;
    font-weight: 500;
}

.toast-icon {
    font-size: 20px;
    margin-top: 4px;
}

.toast-close-btn {
    background: none;
    border: none;
    color: #9ca3af;
    cursor: pointer;
    outline: none;
    transition: color 0.2s ease;
}
.toast-close-btn:hover {
    color: #4b5563;
}
.toast-success {
    background-color: #d1fae5;
    color: #047857;
    border: 1px solid #34d399;
}

.toast-error {
    background-color: #fee2e2;
    color: #b91c1c;
    border: 1px solid #f87171;
}

.toast-info {
    background-color: #dbeafe;
    color: #1d4ed8;
    border: 1px solid #60a5fa;
}

.toast-warning {
    background-color: #fef9c3;
    color: #b45309;
    border: 1px solid #facc15;
}

.toast-default {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #9ca3af;
}

    </style>

    <div
        {{-- class="fixed top-5 right-5 z-50 w-full max-w-sm pointer-events-none" --}}
        {{-- style="position: fixed; top: 1.25rem; right: 1.25rem; z-index: 50; width: 100%; max-width: 384px; pointer-events: none;" --}}
        class="toast-container"
        x-data="{ show: @entangle('visible') }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        x-init="$wire.on('auto-hide-toast', () => { setTimeout(() => show = false, 5000); })"
    >
        <div class="toast-box {{ $this->getClasses() }} ">
            <div class="">
                <i class="{{ $this->getIcon() }} text-xl mt-1"></i>
            </div>
            <div class="toast-message">
                {{ $message }}
            </div>
            <div class="">
                <button @click="show = false" class="toast-close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>


    {{-- <div class="toast-container" x-data="{ show: true }" x-show="show">
        <div class="toast-box toast-success">
            <div><i class="fas fa-check-circle toast-icon"></i></div>
            <div class="toast-message">Successfully saved!</div>
            <div>
                <button class="toast-close-btn" @click="show = false">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div> --}}
</div>
