{{-- <div class="message-input-container">
    <style>
        .message-input-container {
            position: fixed;
            bottom: 10;
            left: 0;
            right: 0;
            background-color: white;
            padding: 12px 16px;
            border-top: 1px solid #e9ecef;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        .message-form {
            display: flex;
            align-items: center;
        }
        .message-input {
            flex: 1;
            border-radius: 20px;
            border: 1px solid #e9ecef;
            padding: 10px 16px;
            margin-right: 12px;
        }
        .send-button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 576px) {
            .message-input-container {
                padding: 10px;
            }
            .message-input {
                margin-right: 8px;
            }
        }
    </style>

    @if($conversation)
    <form wire:submit.prevent="sendMessage()" class="message-form">
        <input
            wire:model='body'
            type="text"
            placeholder="Type a message..."
            class="message-input"
            name="message"
            required
        />
        <button type="submit" class="send-button">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>
    @endif
</div> --}}

<div class="type-text-form">
    <form wire:submit.prevent="sendMessage()"">
        <div class="form-group file-upload mb-0">
            <input type="file" id="attachment" wire:model="attachment"><i class="ti ti-plus"></i>
        </div>
        <textarea
            class="form-control"
            wire:model='body'
            wire:keydown.enter.prevent="sendMessage"
            name="message"
            cols="30"
            rows="10"
            placeholder="Type your message">
        </textarea>
        <button type="submit">
            <svg class="bi bi-arrow-right" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"></path>
            </svg>
        </button>
    </form>
</div>


