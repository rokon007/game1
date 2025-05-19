// Updated number-modal.js with more robust modal handling
document.addEventListener("DOMContentLoaded", () => {
  console.log("DOM fully loaded, setting up modal handlers");
  
  // Setup Livewire event listeners when Livewire is initialized
  document.addEventListener("livewire:initialized", () => {
    console.log("Livewire initialized, setting up event listeners");
    
    // Get the Livewire instance
    if (!window.Livewire) {
      console.error("Livewire not found! Make sure Livewire is properly installed.");
      return;
    }
    
    // Listen for the numberAnnounced event
    window.Livewire.on("numberAnnounced", (data) => {
      console.log("Number announced event received:", data);
      const number = data.number;
      showNumberModal(number);
    });

    // Listen for winner event
    window.Livewire.on("winner-alert", (data) => {
      console.log("Winner alert event received:", data);
      const { title, message, pattern } = data;
      showWinnerModal(title, message, pattern);
    });
    
    console.log("Livewire event listeners set up successfully");
  });
  
  // Function to create or get modal element
  function ensureModalExists(id, title) {
    let modal = document.getElementById(id);
    
    if (!modal) {
      console.log(`Modal ${id} not found, creating it dynamically`);
      
      // Create modal structure
      modal = document.createElement('div');
      modal.id = id;
      modal.className = 'modal';
      modal.style.display = 'none';
      modal.style.position = 'fixed';
      modal.style.top = '0';
      modal.style.left = '0';
      modal.style.width = '100%';
      modal.style.height = '100%';
      modal.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';
      modal.style.zIndex = '9999';
      
      const modalDialog = document.createElement('div');
      modalDialog.className = 'modal-dialog';
      modalDialog.style.position = 'relative';
      modalDialog.style.width = 'auto';
      modalDialog.style.margin = '1.75rem auto';
      modalDialog.style.maxWidth = '500px';
      
      const modalContent = document.createElement('div');
      modalContent.className = 'modal-content';
      modalContent.style.backgroundColor = '#fff';
      modalContent.style.borderRadius = '0.3rem';
      modalContent.style.boxShadow = '0 0.5rem 1rem rgba(0, 0, 0, 0.15)';
      
      const modalHeader = document.createElement('div');
      modalHeader.className = 'modal-header';
      modalHeader.style.display = 'flex';
      modalHeader.style.alignItems = 'center';
      modalHeader.style.justifyContent = 'space-between';
      modalHeader.style.padding = '1rem';
      modalHeader.style.borderBottom = '1px solid #dee2e6';
      
      const modalTitle = document.createElement('h5');
      modalTitle.className = 'modal-title';
      modalTitle.textContent = title;
      
      const closeButton = document.createElement('button');
      closeButton.type = 'button';
      closeButton.className = 'close';
      closeButton.innerHTML = '&times;';
      closeButton.style.cursor = 'pointer';
      closeButton.style.background = 'none';
      closeButton.style.border = 'none';
      closeButton.style.fontSize = '1.5rem';
      closeButton.onclick = function() {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
      };
      
      const modalBody = document.createElement('div');
      modalBody.className = 'modal-body';
      modalBody.style.padding = '1rem';
      
      // Assemble the modal
      modalHeader.appendChild(modalTitle);
      modalHeader.appendChild(closeButton);
      modalContent.appendChild(modalHeader);
      modalContent.appendChild(modalBody);
      modalDialog.appendChild(modalContent);
      modal.appendChild(modalDialog);
      
      // Add to document
      document.body.appendChild(modal);
      
      console.log(`Modal ${id} created dynamically`);
    }
    
    return modal;
  }

  // Function to show the number announcement modal
  function showNumberModal(number) {
    console.log("Showing number modal for:", number);
    
    // Ensure modal exists
    const modal = ensureModalExists("number-announcement-modal", "Number Announcement");
    const modalBody = modal.querySelector('.modal-body');
    
    // Clear previous content
    modalBody.innerHTML = '';
    
    // Create spinner
    const spinnerDiv = document.createElement('div');
    spinnerDiv.id = 'number-spinner';
    spinnerDiv.style.display = 'flex';
    spinnerDiv.style.justifyContent = 'center';
    spinnerDiv.style.alignItems = 'center';
    spinnerDiv.style.height = '200px';
    
    const spinner = document.createElement('div');
    spinner.className = 'spinner-border text-primary';
    spinner.style.width = '5rem';
    spinner.style.height = '5rem';
    spinner.style.borderWidth = '0.5rem';
    spinner.style.borderStyle = 'solid';
    spinner.style.borderColor = '#007bff transparent #007bff transparent';
    spinner.style.borderRadius = '50%';
    spinner.style.animation = 'spin 1s linear infinite';
    
    // Create number display (hidden initially)
    const numberDiv = document.createElement('div');
    numberDiv.id = 'announced-number';
    numberDiv.style.fontSize = '8rem';
    numberDiv.style.fontWeight = 'bold';
    numberDiv.style.textAlign = 'center';
    numberDiv.style.color = '#007bff';
    numberDiv.style.display = 'none';
    
    // Add spinner animation style if not already present
    if (!document.getElementById('modal-animations')) {
      const style = document.createElement('style');
      style.id = 'modal-animations';
      style.textContent = `
        @keyframes spin {
          0% { transform: rotate(0deg); }
          100% { transform: rotate(360deg); }
        }
        @keyframes numberReveal {
          0% { transform: scale(0); opacity: 0; }
          50% { transform: scale(1.2); opacity: 0.5; }
          100% { transform: scale(1); opacity: 1; }
        }
      `;
      document.head.appendChild(style);
    }
    
    // Assemble the modal content
    spinnerDiv.appendChild(spinner);
    modalBody.appendChild(spinnerDiv);
    modalBody.appendChild(numberDiv);
    
    // Force modal to be visible with !important
    modal.style.cssText = 'display: block !important; z-index: 9999 !important;';
    document.body.classList.add('modal-open');
    document.body.style.overflow = 'hidden';
    
    console.log("Modal should be visible now");
    
    // After 10 seconds, show the number and hide spinner
    setTimeout(() => {
      spinnerDiv.style.display = 'none';
      numberDiv.textContent = number;
      numberDiv.style.display = 'block';
      numberDiv.style.animation = 'numberReveal 1s ease-in-out';
      
      // Close modal after 3 more seconds
      setTimeout(() => {
        modal.style.display = 'none';
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
      }, 3000);
    }, 10000);
  }

  // Function to show winner modal
  function showWinnerModal(title, message, pattern) {
    console.log("Showing winner modal:", { title, message, pattern });
    
    // Ensure modal exists
    const modal = ensureModalExists("winner-modal", "Congratulations!");
    const modalBody = modal.querySelector('.modal-body');
    
    // Clear previous content
    modalBody.innerHTML = '';
    modalBody.style.textAlign = 'center';
    modalBody.style.padding = '2rem';
    
    // Create trophy icon
    const trophyDiv = document.createElement('div');
    trophyDiv.className = 'trophy-icon';
    trophyDiv.innerHTML = '<i class="fas fa-trophy"></i>';
    trophyDiv.style.fontSize = '4rem';
    trophyDiv.style.color = 'gold';
    trophyDiv.style.marginBottom = '1rem';
    trophyDiv.style.animation = 'trophyBounce 1s infinite alternate';
    
    // Create title
    const titleDiv = document.createElement('h2');
    titleDiv.id = 'winner-title';
    titleDiv.className = 'winner-title';
    titleDiv.textContent = title;
    titleDiv.style.fontSize = '2rem';
    titleDiv.style.marginBottom = '1rem';
    titleDiv.style.color = getPatternColor(pattern);
    
    // Create message
    const messageDiv = document.createElement('p');
    messageDiv.id = 'winner-message';
    messageDiv.className = 'winner-message';
    messageDiv.textContent = message;
    messageDiv.style.fontSize = '1.5rem';
    messageDiv.style.marginBottom = '1rem';
    
    // Create confetti container
    const confettiContainer = document.createElement('div');
    confettiContainer.className = 'confetti-container';
    confettiContainer.style.position = 'absolute';
    confettiContainer.style.top = '0';
    confettiContainer.style.left = '0';
    confettiContainer.style.width = '100%';
    confettiContainer.style.height = '100%';
    confettiContainer.style.overflow = 'hidden';
    confettiContainer.style.pointerEvents = 'none';
    
    // Add trophy bounce animation if not already present
    if (!document.getElementById('trophy-animation')) {
      const style = document.createElement('style');
      style.id = 'trophy-animation';
      style.textContent = `
        @keyframes trophyBounce {
          from { transform: scale(1); }
          to { transform: scale(1.2); }
        }
        @keyframes confetti-fall {
          0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
          100% { transform: translateY(500px) rotate(360deg); opacity: 0; }
        }
      `;
      document.head.appendChild(style);
    }
    
    // Assemble the modal content
    modalBody.appendChild(trophyDiv);
    modalBody.appendChild(titleDiv);
    modalBody.appendChild(messageDiv);
    modalBody.appendChild(confettiContainer);
    
    // Force modal to be visible with !important
    modal.style.cssText = 'display: block !important; z-index: 9999 !important;';
    document.body.classList.add('modal-open');
    document.body.style.overflow = 'hidden';
    
    // Create confetti
    createConfetti(confettiContainer);
    
    console.log("Winner modal should be visible now");
    
    // Auto close after 5 seconds
    setTimeout(() => {
      modal.style.display = 'none';
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
    }, 5000);
  }
  
  // Helper function to get color based on pattern
  function getPatternColor(pattern) {
    const colors = {
      'corner': '#17a2b8',
      'top_line': '#007bff',
      'middle_line': '#28a745',
      'bottom_line': '#ffc107',
      'full_house': '#dc3545'
    };
    return colors[pattern] || '#28a745';
  }

  // Function to create confetti effect
  function createConfetti(container) {
    // Clear previous confetti
    container.innerHTML = '';
    
    const colors = ['#f00', '#0f0', '#00f', '#ff0', '#0ff', '#f0f'];
    
    for (let i = 0; i < 50; i++) {
      const confetti = document.createElement('div');
      confetti.style.position = 'absolute';
      confetti.style.width = '10px';
      confetti.style.height = '10px';
      confetti.style.left = Math.random() * 100 + '%';
      confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
      confetti.style.animation = `confetti-fall ${Math.random() * 3 + 2}s linear infinite`;
      confetti.style.animationDelay = Math.random() * 5 + 's';
      container.appendChild(confetti);
    }
  }
  
  // Close modals when clicking outside
  window.addEventListener("click", (event) => {
    if (event.target.classList.contains("modal")) {
      event.target.style.display = 'none';
      document.body.classList.remove('modal-open');
      document.body.style.overflow = '';
    }
  });
  
  console.log("Modal handlers initialized");
});