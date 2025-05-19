document.addEventListener("DOMContentLoaded", function() {
    // Bootstrap মোডাল ইনিশিয়ালাইজেশন
    if (typeof $ !== 'undefined') {
        console.log("jQuery is available, initializing Bootstrap modals");
        
        // Livewire ইভেন্ট লিসেনার সেটআপ
        document.addEventListener('livewire:initialized', function() {
            console.log("Setting up Livewire event listeners for modals");
            
            if (window.Livewire) {
                // নাম্বার এনাউন্স ইভেন্ট লিসেনার
                window.Livewire.on('numberAnnounced', function(data) {
                    console.log("Number announced event received:", data);
                    showNumberModal(data.number);
                });
                
                // বিজয়ী ইভেন্ট লিসেনার
                window.Livewire.on('winner-alert', function(data) {
                    console.log("Winner alert event received:", data);
                    showWinnerModal(data.title, data.message, data.pattern);
                });
            }
        });
    } else {
        console.error("jQuery is not available! Bootstrap modals require jQuery.");
    }
    
    // নাম্বার মোডাল দেখানোর ফাংশন
    function showNumberModal(number) {
        console.log("Showing number modal for:", number);
        
        try {
            // Bootstrap মোডাল ইনিশিয়ালাইজেশন
            const modal = $('#number-announcement-modal');
            
            if (modal.length === 0) {
                console.error("Modal element not found!");
                return;
            }
            
            // মোডাল কন্টেন্ট সেট করুন
            const spinnerElement = document.getElementById("number-spinner");
            const numberElement = document.getElementById("announced-number");
            
            if (!spinnerElement || !numberElement) {
                console.error("Modal content elements not found!");
                return;
            }
            
            // রিসেট এবং মোডাল দেখান
            numberElement.textContent = "";
            spinnerElement.classList.remove("d-none");
            numberElement.classList.add("d-none");
            
            // Bootstrap মোডাল দেখান
            modal.modal('show');
            
            console.log("Modal should be visible now");
            
            // ১০ সেকেন্ড পর, নাম্বার দেখান এবং স্পিনার লুকান
            setTimeout(() => {
                spinnerElement.classList.add("d-none");
                numberElement.textContent = number;
                numberElement.classList.remove("d-none");
                
                // আরও ৩ সেকেন্ড পর মোডাল বন্ধ করুন
                setTimeout(() => {
                    modal.modal('hide');
                }, 3000);
            }, 10000);
        } catch (error) {
            console.error("Error showing number modal:", error);
        }
    }
    
    // বিজয়ী মোডাল দেখানোর ফাংশন
    function showWinnerModal(title, message, pattern) {
        console.log("Showing winner modal:", { title, message, pattern });
        
        try {
            // Bootstrap মোডাল ইনিশিয়ালাইজেশন
            const modal = $('#winner-modal');
            
            if (modal.length === 0) {
                console.error("Winner modal element not found!");
                return;
            }
            
            // মোডাল কন্টেন্ট সেট করুন
            const titleElement = document.getElementById("winner-title");
            const messageElement = document.getElementById("winner-message");
            
            if (!titleElement || !messageElement) {
                console.error("Winner modal content elements not found!");
                return;
            }
            
            titleElement.textContent = title;
            messageElement.textContent = message;
            
            // Bootstrap মোডাল দেখান
            modal.modal('show');
            
            // কনফেটি এফেক্ট তৈরি করুন
            createConfetti();
            
            console.log("Winner modal should be visible now");
            
            // ৫ সেকেন্ড পর মোডাল বন্ধ করুন
            setTimeout(() => {
                modal.modal('hide');
            }, 5000);
        } catch (error) {
            console.error("Error showing winner modal:", error);
        }
    }
    
    // কনফেটি এফেক্ট তৈরি করার ফাংশন
    function createConfetti() {
        const confettiContainer = document.querySelector('.confetti-container');
        if (!confettiContainer) {
            console.error("Confetti container not found!");
            return;
        }
        
        // আগের কনফেটি পরিষ্কার করুন
        confettiContainer.innerHTML = '';
        
        const colors = ['#f00', '#0f0', '#00f', '#ff0', '#0ff', '#f0f'];
        
        for (let i = 0; i < 50; i++) {
            const confetti = document.createElement('div');
            confetti.className = 'confetti';
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.animationDelay = Math.random() * 5 + 's';
            confettiContainer.appendChild(confetti);
        }
    }
});