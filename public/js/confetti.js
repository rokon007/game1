// Confetti animation for winner celebrations
class Confetti {
  constructor() {
    this.canvas = document.createElement("canvas")
    this.ctx = this.canvas.getContext("2d")
    this.particles = []
    this.colors = ["#f00", "#0f0", "#00f", "#ff0", "#0ff", "#f0f"]
    this.isActive = false

    this.init()
  }

  init() {
    this.canvas.width = window.innerWidth
    this.canvas.height = window.innerHeight
    this.canvas.style.position = "fixed"
    this.canvas.style.top = "0"
    this.canvas.style.left = "0"
    this.canvas.style.pointerEvents = "none"
    this.canvas.style.zIndex = "9999"
    document.body.appendChild(this.canvas)

    window.addEventListener("resize", () => {
      this.canvas.width = window.innerWidth
      this.canvas.height = window.innerHeight
    })
  }

  createParticles(count = 100) {
    this.particles = []
    for (let i = 0; i < count; i++) {
      this.particles.push({
        x: Math.random() * this.canvas.width,
        y: -20,
        size: Math.random() * 10 + 5,
        color: this.colors[Math.floor(Math.random() * this.colors.length)],
        speed: Math.random() * 3 + 2,
        rotation: Math.random() * 360,
        rotationSpeed: Math.random() * 10 - 5,
      })
    }
  }

  animate() {
    if (!this.isActive) return

    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height)

    for (let i = 0; i < this.particles.length; i++) {
      const p = this.particles[i]

      this.ctx.save()
      this.ctx.translate(p.x, p.y)
      this.ctx.rotate((p.rotation * Math.PI) / 180)

      this.ctx.fillStyle = p.color
      this.ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size)

      this.ctx.restore()

      p.y += p.speed
      p.rotation += p.rotationSpeed

      // Remove particles that have fallen off screen
      if (p.y > this.canvas.height + p.size) {
        this.particles.splice(i, 1)
        i--
      }
    }

    // Stop animation if all particles are gone
    if (this.particles.length === 0) {
      this.isActive = false
      return
    }

    requestAnimationFrame(() => this.animate())
  }

  start(count = 100) {
    this.isActive = true
    this.createParticles(count)
    this.animate()
  }

  stop() {
    this.isActive = false
    this.particles = []
  }
}

// Update the event listener to work with Livewire v3
document.addEventListener("livewire:initialized", () => {
  const confetti = new Confetti()
  const Livewire = window.Livewire // Declare the Livewire variable

  // Use Livewire.on instead of window.addEventListener
  Livewire.on("winner-alert", () => {
    confetti.start(200)

    // Stop confetti after 5 seconds
    setTimeout(() => {
      confetti.stop()
    }, 5000)
  })
})
