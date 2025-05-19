// Number Announcer Modal Script
document.addEventListener("livewire:initialized", () => {
  // Listen for Livewire events
  window.Livewire.on("numberAnnounced", (data) => {
    const number = data.number
    showNumberModal(number)
  })

  // Listen for winner event
  window.Livewire.on("winner-alert", (data) => {
    const { title, message, pattern } = data
    showWinnerModal(title, message, pattern)
  })

  // Function to show the number announcement modal
  function showNumberModal(number) {
    const modal = document.getElementById("number-announcement-modal")
    const spinnerElement = document.getElementById("number-spinner")
    const numberElement = document.getElementById("announced-number")

    // Reset and show modal
    numberElement.textContent = ""
    spinnerElement.classList.remove("d-none")
    numberElement.classList.add("d-none")
    modal.classList.add("show")
    document.body.classList.add("modal-open")

    // After 10 seconds, show the number and hide spinner
    setTimeout(() => {
      spinnerElement.classList.add("d-none")
      numberElement.textContent = number
      numberElement.classList.remove("d-none")

      // Close modal after 3 more seconds
      setTimeout(() => {
        modal.classList.remove("show")
        document.body.classList.remove("modal-open")
      }, 3000)
    }, 10000)
  }

  // Function to show winner modal
  function showWinnerModal(title, message, pattern) {
    const modal = document.getElementById("winner-modal")
    const titleElement = document.getElementById("winner-title")
    const messageElement = document.getElementById("winner-message")

    // Set content and show modal
    titleElement.textContent = title
    messageElement.textContent = message

    // Add pattern-specific class for styling
    modal.className = "modal fade show"
    modal.classList.add(`pattern-${pattern}`)

    document.body.classList.add("modal-open")

    // Auto close after 5 seconds
    setTimeout(() => {
      modal.classList.remove("show")
      document.body.classList.remove("modal-open")
    }, 5000)
  }

  // Close modals when clicking outside or on close button
  document.querySelectorAll(".modal .close").forEach((button) => {
    button.addEventListener("click", () => {
      document.querySelectorAll(".modal").forEach((modal) => {
        modal.classList.remove("show")
      })
      document.body.classList.remove("modal-open")
    })
  })
})
