/*!
 * Hajari Room central client logic
 * - Idempotent initialization across Livewire updates
 * - Desktop and mobile drag & drop reordering
 * - Timers, audio, notifications
 * - Listeners for server-dispatched events
 * Requirements in Blade:
 *   - window.__HAJARI_LW_ID must be set to the Livewire component id
 *   - Markup must include the same ids/classes used here
 */
;(() => {
  if (window.HajariRoom) return // singleton

  const HajariRoom = {
    // state
    _bound: false,
    _draggedEl: null,
    _draggedIndex: null,
    _isDragging: false,
    _dragOffset: { x: 0, y: 0 },
    _arrangementTimer: null,
    _soundEnabled: true,
    _dragThreshold: 15,
    _lastInitForId: null,

    init() {
      // Ensure we bind per Livewire component instance id
      const lwId = window.__HAJARI_LW_ID
      if (!lwId) {
        console.debug("[HajariRoom] Livewire ID not present yet")
        return
      }
      if (this._lastInitForId === lwId && this._bound) {
        // Already initialized for this component
        return
      }
      this._lastInitForId = lwId

      // Load sound preference once
      const savedSoundPref = localStorage.getItem("hajari_sound_enabled")
      if (savedSoundPref !== null) {
        this._soundEnabled = savedSoundPref === "true"
      }

      this.bindOnce()
      this.rebindDynamic() // bind features that need DOM present
    },

    bindOnce() {
      if (this._bound) return
      this._bound = true

      // Audio bootstrap (only once)
      this._initAudio()

      // Global listeners (Pusher/Livewire-dispatched custom events)
      this._bindServerEventListeners()

      // Auto re-init after Livewire DOM updates
      document.addEventListener("livewire:updated", () => this.rebindDynamic())
      document.addEventListener("livewire:navigated", () => this.rebindDynamic())

      // Also observe #cards-container subtree replacements
      const obsTarget = document.body
      if (obsTarget && !this._mutationObserver) {
        this._mutationObserver = new MutationObserver((mutations) => {
          for (const m of mutations) {
            if (m.type === "childList") {
              // Rebind when cards container is re-rendered
              if (
                [...m.addedNodes].some(
                  (n) =>
                    n.nodeType === 1 &&
                    n.querySelector &&
                    (n.querySelector("#cards-container") || n.id === "cards-container"),
                )
              ) {
                this._safe(() => this._bindDragAndDrop())
                this._safe(() => this._bindScrollIndicators())
              }
            }
          }
        })
        this._mutationObserver.observe(obsTarget, { childList: true, subtree: true })
      }
    },

    rebindDynamic() {
      this._safe(() => this._bindDragAndDrop())
      this._safe(() => this._bindScrollIndicators())
      this._safe(() => this._startArrangementTimer())
    },

    // ---------- Audio ----------
    _initAudio() {
      // Attempt to prime audio on first interaction (mobile autoplay policies)
      const enableAudioContext = () => {
        const audios = document.querySelectorAll("audio")
        audios.forEach((audio) => {
          if (audio.paused) {
            audio
              .play()
              .then(() => {
                audio.pause()
                audio.currentTime = 0
              })
              .catch(() => {})
          }
        })
      }
      document.addEventListener("click", enableAudioContext, { once: true })
      document.addEventListener("touchstart", enableAudioContext, { once: true })
    },

    _playSound(audioId, fallbackBeep = false) {
      try {
        if (!this._soundEnabled) return
        const audio = document.getElementById(audioId)
        if (audio) {
          audio.currentTime = 0
          const p = audio.play()
          if (p && typeof p.then === "function") {
            p.catch(() => {
              if (fallbackBeep) this._fallbackBeep()
            })
          }
        } else if (fallbackBeep) {
          this._fallbackBeep()
        }
      } catch {
        if (fallbackBeep) this._fallbackBeep()
      }
    },

    _fallbackBeep() {
      try {
        const Ctx = window.AudioContext || window.webkitAudioContext
        const ctx = new Ctx()
        const osc = ctx.createOscillator()
        const gain = ctx.createGain()
        osc.connect(gain)
        gain.connect(ctx.destination)
        osc.frequency.value = 800
        osc.type = "sine"
        gain.gain.setValueAtTime(0.3, ctx.currentTime)
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1)
        osc.start(ctx.currentTime)
        osc.stop(ctx.currentTime + 0.1)
      } catch {}
    },

    // ---------- Livewire / Server Events ----------
    _bindServerEventListeners() {
      // dedupe by marking on window
      if (window.__HAJARI_SERVER_EVENTS_BOUND) return
      window.__HAJARI_SERVER_EVENTS_BOUND = true

      window.addEventListener("gameUpdated", (event) => {
        this._notify(event.detail?.data?.message || "Game updated")
        this._callLivewire("loadGameState")
        if (event.detail?.type === "game_started") {
          this._playSound("dealSound", true)
          this._startArrangementTimer()
        }
      })

      window.addEventListener("cardPlayed", (event) => {
        const name = event.detail?.player_name || "A player"
        this._notify(`${name} played cards`)
        this._callLivewire("loadGameState")
        this._playSound("playCardSound", true)
      })

      window.addEventListener("roundWinner", (event) => {
        const winnerPos = event.detail?.winner_position
        const winnerName = event.detail?.winner_name || "Player"
        this._playSound("winRoundSound", true)
        this._notify(`${winnerName} wins the round!`)
        setTimeout(() => this._animateCardsToWinner(winnerPos), 1000)
      })

      window.addEventListener("clearCenterCards", () => this._clearCenterCards())

      window.addEventListener("hideScoreModal", () => {
        setTimeout(() => this._callLivewire("closeScoreModal"), 5000)
      })

      // periodic refresh (light)
      if (!window.__HAJARI_REFRESH_INTERVAL) {
        window.__HAJARI_REFRESH_INTERVAL = setInterval(() => {
          this._callLivewire("loadGameState")
        }, 3000)
      }
    },

    _callLivewire(method, ...args) {
      try {
        const id = window.__HAJARI_LW_ID
        if (!id || !window.Livewire?.find) return
        const comp = window.Livewire.find(id)
        if (!comp) return
        comp.call(method, ...args)
      } catch (e) {
        console.debug("[HajariRoom] Livewire call failed", method, e)
      }
    },

    // ---------- Timers ----------
    _startArrangementTimer() {
      // prevent duplicate intervals
      if (this._arrangementTimer) {
        clearInterval(this._arrangementTimer)
        this._arrangementTimer = null
      }
      const timerElement = document.getElementById("live-timer")
      const headerTimer = document.getElementById("arrangement-timer")
      if (!timerElement && !headerTimer) return

      this._arrangementTimer = setInterval(async () => {
        await this._callLivewire("loadGameState")
        // Livewire will re-render timer values; as a fallback we can read @this.arrangementTimeLeft,
        // but in external JS we don't have @this. We trust re-render + CSS urgent styles.
        // If you prefer client ticking, expose window.__ARRANGEMENT_TIME_LEFT and decrement here.
      }, 1000)
    },

    // ---------- Drag & Drop (desktop + mobile) ----------
    _bindDragAndDrop() {
      const container = document.getElementById("cards-container")
      if (!container) return
      if (container.dataset.bound === "1") return
      container.dataset.bound = "1"

      // Desktop drag
      container.addEventListener("dragstart", (e) => this._onDragStart(e))
      container.addEventListener("dragover", (e) => this._onDragOver(e))
      container.addEventListener("drop", (e) => this._onDrop(e))
      container.addEventListener("dragend", () => this._onDragEnd())

      // Mobile touch
      container.addEventListener("touchstart", (e) => this._onTouchStart(e), { passive: false })
      container.addEventListener("touchmove", (e) => this._onTouchMove(e), { passive: false })
      container.addEventListener("touchend", (e) => this._onTouchEnd(e), { passive: false })
    },

    _getCardElFromEvent(e) {
      const target = e.target instanceof Element ? e.target : null
      if (!target) return null
      return target.closest(".draggable-card")
    },

    _dragAllowedFor(el) {
      if (!el) return false
      if (el.classList.contains("locked")) return false
      return el.getAttribute("draggable") === "true"
    },

    _onDragStart(e) {
      const el = this._getCardElFromEvent(e)
      if (!this._dragAllowedFor(el)) {
        e.preventDefault()
        return
      }
      this._draggedEl = el
      this._draggedIndex = Number.parseInt(el.dataset.cardIndex)
      // delay adding class to let native drag begin
      setTimeout(() => el.classList.add("dragging"), 0)
      e.dataTransfer.effectAllowed = "move"
      // Firefox requires data
      e.dataTransfer.setData("text/plain", "")
    },

    _onDragOver(e) {
      e.preventDefault()
      if (e.dataTransfer) e.dataTransfer.dropEffect = "move"
      const dropTarget = this._getCardElFromEvent(e)
      document.querySelectorAll(".drag-over").forEach((x) => x.classList.remove("drag-over"))
      if (dropTarget && dropTarget !== this._draggedEl && !dropTarget.classList.contains("locked")) {
        dropTarget.classList.add("drag-over")
      }
    },

    _onDrop(e) {
      e.preventDefault()
      const dropTarget = this._getCardElFromEvent(e)
      document.querySelectorAll(".drag-over").forEach((x) => x.classList.remove("drag-over"))
      if (!dropTarget || dropTarget === this._draggedEl || dropTarget.classList.contains("locked")) return

      const dropIndex = Number.parseInt(dropTarget.dataset.cardIndex)
      if (Number.isInteger(this._draggedIndex) && Number.isInteger(dropIndex) && this._draggedIndex !== dropIndex) {
        this._callLivewire("reorderCards", this._draggedIndex, dropIndex)
      }
      this._onDragEnd()
    },

    _onDragEnd() {
      if (this._draggedEl) this._draggedEl.classList.remove("dragging")
      this._draggedEl = null
      this._draggedIndex = null
    },

    // Mobile
    _onTouchStart(e) {
      const el = this._getCardElFromEvent(e)
      if (!this._dragAllowedFor(el)) return
      // Do not prevent default yet to allow tap-selection for non-drags. We'll prevent on move after threshold.
      this._draggedEl = el
      this._draggedIndex = Number.parseInt(el.dataset.cardIndex)
      this._isDragging = false

      const rect = el.getBoundingClientRect()
      const t = e.touches[0]
      this._touchStartX = t.clientX
      this._touchStartY = t.clientY
      this._dragOffset.x = t.clientX - rect.left
      this._dragOffset.y = t.clientY - rect.top
    },

    _onTouchMove(e) {
      if (!this._draggedEl) return
      const t = e.touches[0]
      const dx = Math.abs(t.clientX - this._touchStartX)
      const dy = Math.abs(t.clientY - this._touchStartY)
      if (!this._isDragging && (dx > this._dragThreshold || dy > this._dragThreshold)) {
        this._isDragging = true
        e.preventDefault() // prevent scroll once dragging
        this._draggedEl.classList.add("dragging")
        this._createGhost(t.clientX, t.clientY)
        if (navigator.vibrate) navigator.vibrate(30)
      } else if (this._isDragging) {
        e.preventDefault()
        this._updateGhost(t.clientX, t.clientY)
        // highlight potential drop target
        const ghost = document.getElementById("drag-ghost")
        if (ghost) ghost.style.display = "none"
        const elBelow = document.elementFromPoint(t.clientX, t.clientY)
        if (ghost) ghost.style.display = ""
        document.querySelectorAll(".drag-over").forEach((x) => x.classList.remove("drag-over"))
        const dropTarget = elBelow ? elBelow.closest(".draggable-card:not(.dragging)") : null
        if (dropTarget) dropTarget.classList.add("drag-over")
      }
    },

    _onTouchEnd(e) {
      if (!this._draggedEl) return
      // If not dragging, let the tap event bubble (Livewire wire:click handles selection). No preventDefault.
      if (this._isDragging) {
        // finish drop
        this._removeGhost()
        const t = e.changedTouches[0]
        this._draggedEl.style.visibility = "hidden"
        const elBelow = document.elementFromPoint(t.clientX, t.clientY)
        this._draggedEl.style.visibility = "visible"
        document.querySelectorAll(".drag-over").forEach((x) => x.classList.remove("drag-over"))
        const dropTarget = elBelow ? elBelow.closest(".draggable-card") : null
        if (dropTarget && dropTarget !== this._draggedEl && !dropTarget.classList.contains("locked")) {
          const dropIndex = Number.parseInt(dropTarget.dataset.cardIndex)
          if (Number.isInteger(this._draggedIndex) && Number.isInteger(dropIndex) && this._draggedIndex !== dropIndex) {
            this._callLivewire("reorderCards", this._draggedIndex, dropIndex)
            if (navigator.vibrate) navigator.vibrate([40, 40])
          }
        }
        this._draggedEl.classList.remove("dragging")
      }
      this._draggedEl = null
      this._draggedIndex = null
      this._isDragging = false
    },

    _createGhost(x, y) {
      const el = this._draggedEl
      if (!el) return
      const ghost = el.cloneNode(true)
      ghost.id = "drag-ghost"
      Object.assign(ghost.style, {
        position: "fixed",
        left: `${x - this._dragOffset.x}px`,
        top: `${y - this._dragOffset.y}px`,
        zIndex: "9999",
        pointerEvents: "none",
        transform: "rotate(5deg) scale(1.1)",
        opacity: "0.8",
        boxShadow: "0 8px 16px rgba(0,0,0,0.3)",
      })
      document.body.appendChild(ghost)
    },

    _updateGhost(x, y) {
      const ghost = document.getElementById("drag-ghost")
      if (ghost) {
        ghost.style.left = `${x - this._dragOffset.x}px`
        ghost.style.top = `${y - this._dragOffset.y}px`
      }
    },

    _removeGhost() {
      const ghost = document.getElementById("drag-ghost")
      if (ghost) ghost.remove()
    },

    // ---------- Scroll indicators ----------
    _bindScrollIndicators() {
      const scroll = document.getElementById("cards-scroll")
      const left = document.getElementById("left-scroll")
      const right = document.getElementById("right-scroll")
      if (!scroll || !left || !right) return
      if (scroll.dataset.bound === "1") return
      scroll.dataset.bound = "1"

      const update = () => {
        const { scrollLeft, scrollWidth, clientWidth } = scroll
        left.style.opacity = scrollLeft > 1 ? "1" : "0.3"
        right.style.opacity = scrollLeft < scrollWidth - clientWidth - 1 ? "1" : "0.3"
      }
      scroll.addEventListener("scroll", update)
      left.addEventListener("click", () => scroll.scrollBy({ left: -100, behavior: "smooth" }))
      right.addEventListener("click", () => scroll.scrollBy({ left: 100, behavior: "smooth" }))
      update()
    },

    // ---------- UI helpers ----------
    _notify(message) {
      const container = document.getElementById("game-notifications")
      if (!container) return
      const n = document.createElement("div")
      n.className = "notification"
      n.textContent = message
      container.appendChild(n)
      setTimeout(() => {
        if (n.parentNode) n.remove()
      }, 3000)
    },

    _animateCardsToWinner(winnerPosition) {
      const stacks = document.querySelectorAll(".fan-player-stack")
      const winnerEl = document.getElementById(`player-position-${winnerPosition}`)
      if (!winnerEl) return
      const wRect = winnerEl.getBoundingClientRect()

      stacks.forEach((stack, i) => {
        const cards = stack.querySelectorAll(".fan-stacked-card")
        cards.forEach((card, j) => {
          setTimeout(
            () => {
              const cRect = card.getBoundingClientRect()
              card.style.transition = "all 1.8s ease-in-out"
              card.style.transform = `translate(${wRect.left - cRect.left}px, ${wRect.top - cRect.top}px) scale(0.15) rotate(${Math.random() * 60 - 30}deg)`
              card.style.opacity = "0.3"
              setTimeout(() => {
                card.style.display = "none"
              }, 1800)
            },
            i * 400 + j * 200,
          )
        })
      })

      setTimeout(() => this._clearCenterCards(), 3500)
    },

    _clearCenterCards() {
      const center = document.getElementById("center-cards")
      if (!center) return
      center.innerHTML =
        '<div class="center-placeholder"><div class="placeholder-icon"><i class="fas fa-layer-group"></i></div></div>'
    },

    _safe(fn) {
      try {
        fn()
      } catch (e) {
        console.debug("[HajariRoom] safe error", e)
      }
    },
  }

  window.HajariRoom = HajariRoom
})()
