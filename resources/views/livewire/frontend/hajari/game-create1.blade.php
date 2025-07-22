<div>
    @section('meta_description')
      <meta name="description" content="Housieblitz - Create Professional Hajari Game">
    @endsection
    @section('title')
        <title>Housieblitz | Create Hajari Game</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

        <style>
            :root {
                --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                --success-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
                --danger-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
                --dark-gradient: linear-gradient(135deg, #434343 0%, #000000 100%);

                --primary-color: #667eea;
                --secondary-color: #764ba2;
                --accent-color: #4facfe;
                --success-color: #00d4aa;
                --warning-color: #ffb300;
                --danger-color: #f44336;

                --text-primary: #2d3748;
                --text-secondary: #718096;
                --text-muted: #a0aec0;

                --bg-primary: #ffffff;
                --bg-secondary: #f8fafc;
                --bg-accent: #edf2f7;

                --border-color: #e2e8f0;
                --border-radius: 16px;
                --border-radius-sm: 12px;
                --border-radius-lg: 20px;

                --shadow-sm: 0 2px 4px rgba(0,0,0,0.06);
                --shadow-md: 0 8px 25px rgba(0,0,0,0.08);
                --shadow-lg: 0 20px 40px rgba(0,0,0,0.12);
                --shadow-xl: 0 25px 50px rgba(0,0,0,0.15);

                --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                --transition-fast: all 0.2s ease;

                --spacing-xs: 8px;
                --spacing-sm: 16px;
                --spacing-md: 24px;
                --spacing-lg: 32px;
                --spacing-xl: 48px;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                min-height: 100vh;
                color: var(--text-primary);
                line-height: 1.6;
            }

            .page-content-wrapper {
                padding: var(--spacing-md);
                min-height: 100vh;
            }

            .container {
                max-width: 1000px;
                margin: 0 auto;
            }

            .game-create-container {
                background: var(--bg-primary);
                border-radius: var(--border-radius-lg);
                box-shadow: var(--shadow-xl);
                overflow: hidden;
                position: relative;
                backdrop-filter: blur(10px);
            }

            .game-create-container::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 6px;
                background: var(--primary-gradient);
                z-index: 2;
            }

            /* Header Section */
            .game-header {
                background: var(--primary-gradient);
                color: white;
                padding: var(--spacing-xl) var(--spacing-lg);
                text-align: center;
                position: relative;
                overflow: hidden;
            }

            .game-header::before {
                content: '';
                position: absolute;
                top: -50%;
                right: -20%;
                width: 200px;
                height: 200px;
                background: rgba(255,255,255,0.1);
                border-radius: 50%;
                animation: float 6s ease-in-out infinite;
            }

            .game-header::after {
                content: '';
                position: absolute;
                bottom: -30%;
                left: -10%;
                width: 150px;
                height: 150px;
                background: rgba(255,255,255,0.08);
                border-radius: 50%;
                animation: float 8s ease-in-out infinite reverse;
            }

            @keyframes float {
                0%, 100% { transform: translateY(0px) rotate(0deg); }
                50% { transform: translateY(-20px) rotate(180deg); }
            }

            .header-content {
                position: relative;
                z-index: 2;
            }

            .game-title {
                font-size: 2.5rem;
                font-weight: 800;
                margin-bottom: var(--spacing-sm);
                display: flex;
                align-items: center;
                justify-content: center;
                gap: var(--spacing-sm);
                animation: slideInDown 0.8s ease-out;
            }

            .game-title i {
                font-size: 2.2rem;
                animation: pulse 2s ease-in-out infinite;
            }

            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.1); }
            }

            .game-subtitle {
                font-size: 1.1rem;
                opacity: 0.95;
                font-weight: 400;
                animation: slideInUp 0.8s ease-out 0.2s both;
            }

            @keyframes slideInDown {
                from { opacity: 0; transform: translateY(-30px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @keyframes slideInUp {
                from { opacity: 0; transform: translateY(30px); }
                to { opacity: 1; transform: translateY(0); }
            }

            /* Content Section */
            .game-content {
                padding: var(--spacing-xl) var(--spacing-lg);
            }

            /* Pro Tip Card */
            .pro-tip-card {
                background: linear-gradient(135deg, #667eea1a 0%, #764ba21a 100%);
                border: 1px solid rgba(102, 126, 234, 0.2);
                border-radius: var(--border-radius);
                padding: var(--spacing-md);
                margin-bottom: var(--spacing-lg);
                display: flex;
                align-items: flex-start;
                gap: var(--spacing-sm);
                transition: var(--transition);
                position: relative;
                overflow: hidden;
            }

            .pro-tip-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                width: 4px;
                height: 100%;
                background: var(--primary-gradient);
            }

            .pro-tip-card:hover {
                transform: translateY(-2px);
                box-shadow: var(--shadow-lg);
            }

            .tip-icon {
                width: 50px;
                height: 50px;
                background: var(--primary-gradient);
                border-radius: var(--border-radius-sm);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.4rem;
                flex-shrink: 0;
                animation: bounce 2s ease-in-out infinite;
            }

            @keyframes bounce {
                0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                40% { transform: translateY(-10px); }
                60% { transform: translateY(-5px); }
            }

            .tip-content h4 {
                font-size: 1.2rem;
                font-weight: 600;
                color: var(--primary-color);
                margin-bottom: var(--spacing-xs);
            }

            .tip-content p {
                color: var(--text-secondary);
                font-size: 0.95rem;
                line-height: 1.5;
            }

            /* Form Cards */
            .form-card {
                background: var(--bg-primary);
                border-radius: var(--border-radius);
                padding: var(--spacing-lg);
                margin-bottom: var(--spacing-lg);
                box-shadow: var(--shadow-md);
                border: 1px solid var(--border-color);
                transition: var(--transition);
                position: relative;
                overflow: hidden;
            }

            .form-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 3px;
                background: var(--primary-gradient);
                transform: translateX(-100%);
                transition: var(--transition);
            }

            .form-card:hover::before {
                transform: translateX(0);
            }

            .form-card:hover {
                transform: translateY(-4px);
                box-shadow: var(--shadow-lg);
            }

            .form-card-header {
                display: flex;
                align-items: center;
                gap: var(--spacing-sm);
                margin-bottom: var(--spacing-md);
                padding-bottom: var(--spacing-sm);
                border-bottom: 2px solid var(--bg-accent);
            }

            .form-card-icon {
                width: 40px;
                height: 40px;
                background: var(--primary-gradient);
                border-radius: var(--border-radius-sm);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.2rem;
            }

            .form-card-title {
                font-size: 1.4rem;
                font-weight: 700;
                color: var(--text-primary);
                margin: 0;
            }

            /* Form Elements */
            .form-group {
                margin-bottom: var(--spacing-md);
            }

            .form-label {
                display: block;
                font-weight: 600;
                color: var(--text-primary);
                margin-bottom: var(--spacing-xs);
                font-size: 0.95rem;
                position: relative;
            }

            .required::after {
                content: ' *';
                color: var(--danger-color);
                font-weight: bold;
            }

            .form-control {
                width: 100%;
                padding: 16px 20px;
                border: 2px solid var(--border-color);
                border-radius: var(--border-radius-sm);
                font-size: 1rem;
                font-weight: 500;
                color: var(--text-primary);
                background: var(--bg-secondary);
                transition: var(--transition);
                position: relative;
            }

            .form-control:focus {
                outline: none;
                border-color: var(--primary-color);
                background: var(--bg-primary);
                box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.15);
                transform: translateY(-2px);
            }

            .form-control:hover {
                border-color: var(--primary-color);
                background: var(--bg-primary);
            }

            .form-control::placeholder {
                color: var(--text-muted);
                font-weight: 400;
            }

            .form-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: var(--spacing-md);
            }

            .input-group {
                position: relative;
                display: flex;
                align-items: center;
            }

            .input-group-text {
                position: absolute;
                right: 16px;
                background: var(--primary-gradient);
                color: white;
                padding: 6px 12px;
                border-radius: var(--spacing-xs);
                font-weight: 600;
                font-size: 0.9rem;
                z-index: 2;
                pointer-events: none;
            }

            .form-help-text {
                font-size: 0.85rem;
                color: var(--text-muted);
                margin-top: var(--spacing-xs);
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .form-help-text i {
                color: var(--accent-color);
            }

            /* Search Section */
            .search-container {
                position: relative;
                margin-bottom: var(--spacing-md);
            }

            .search-input {
                padding-left: 50px;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%23667eea' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-position: 16px center;
                background-size: 20px;
            }

            .search-results {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--bg-primary);
                border-radius: var(--border-radius-sm);
                box-shadow: var(--shadow-lg);
                z-index: 100;
                max-height: 320px;
                overflow-y: auto;
                margin-top: var(--spacing-xs);
                border: 1px solid var(--border-color);
                opacity: 0;
                visibility: hidden;
                transform: translateY(-10px);
                transition: var(--transition);
            }

            .search-results.active {
                opacity: 1;
                visibility: visible;
                /* transform: translateY(0); */
            }

            .user-item {
                display: flex;
                align-items: center;
                gap: var(--spacing-sm);
                padding: var(--spacing-sm) var(--spacing-md);
                cursor: pointer;
                transition: var(--transition-fast);
                border-bottom: 1px solid var(--bg-accent);
                position: relative;
            }

            .user-item:last-child {
                border-bottom: none;
            }

            .user-item:hover {
                background: var(--bg-secondary);
                transform: translateX(4px);
            }

            .user-item::before {
                content: '';
                position: absolute;
                left: 0;
                top: 0;
                bottom: 0;
                width: 0;
                background: var(--primary-gradient);
                transition: var(--transition-fast);
            }

            .user-item:hover::before {
                width: 4px;
            }

            .user-avatar {
                width: 48px;
                height: 48px;
                border-radius: 50%;
                background: var(--primary-gradient);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 700;
                font-size: 1.2rem;
                flex-shrink: 0;
                position: relative;
                overflow: hidden;
            }

            .user-avatar img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 50%;
            }

            .user-info {
                flex: 1;
            }

            .user-name {
                font-weight: 600;
                color: var(--text-primary);
                font-size: 1rem;
                margin-bottom: 2px;
            }

            .user-email {
                font-size: 0.85rem;
                color: var(--text-muted);
            }

            .online-indicator {
                width: 12px;
                height: 12px;
                background: var(--success-color);
                border-radius: 50%;
                border: 2px solid white;
                position: absolute;
                bottom: 0;
                right: 0;
                animation: pulse-online 2s ease-in-out infinite;
            }

            @keyframes pulse-online {
                0% { box-shadow: 0 0 0 0 rgba(0, 212, 170, 0.7); }
                70% { box-shadow: 0 0 0 10px rgba(0, 212, 170, 0); }
                100% { box-shadow: 0 0 0 0 rgba(0, 212, 170, 0); }
            }

            /* Invited Players */
            .invited-players {
                display: flex;
                flex-wrap: wrap;
                gap: var(--spacing-sm);
                min-height: 60px;
                padding: var(--spacing-sm);
                background: var(--bg-secondary);
                border-radius: var(--border-radius-sm);
                border: 2px dashed var(--border-color);
                transition: var(--transition);
            }

            .invited-players.has-players {
                border-style: solid;
                background: var(--bg-primary);
            }

            .player-badge {
                display: flex;
                align-items: center;
                gap: var(--spacing-xs);
                background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%);
                border: 1px solid rgba(102, 126, 234, 0.3);
                border-radius: 25px;
                padding: 8px 16px;
                font-weight: 600;
                color: var(--primary-color);
                position: relative;
                animation: fadeInScale 0.3s ease-out;
                transition: var(--transition-fast);
            }

            @keyframes fadeInScale {
                from { opacity: 0; transform: scale(0.8); }
                to { opacity: 1; transform: scale(1); }
            }

            .player-badge:hover {
                background: linear-gradient(135deg, #667eea25 0%, #764ba225 100%);
                transform: translateY(-2px);
                box-shadow: var(--shadow-sm);
            }

            .player-avatar-small {
                width: 32px;
                height: 32px;
                border-radius: 50%;
                background: var(--primary-gradient);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 700;
                font-size: 0.85rem;
            }

            .player-avatar-small img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 50%;
            }

            .remove-player {
                cursor: pointer;
                color: var(--danger-color);
                font-size: 1.1rem;
                padding: 4px;
                border-radius: 50%;
                transition: var(--transition-fast);
            }

            .remove-player:hover {
                background: rgba(244, 67, 54, 0.1);
                transform: scale(1.2);
            }

            .empty-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: var(--spacing-md);
                color: var(--text-muted);
                font-style: italic;
                min-height: 60px;
            }

            .empty-state i {
                font-size: 1.5rem;
                margin-bottom: var(--spacing-xs);
                opacity: 0.5;
            }

            /* Buttons */
            .btn-container {
                display: flex;
                gap: var(--spacing-md);
                justify-content: center;
                margin-top: var(--spacing-xl);
                padding-top: var(--spacing-md);
                border-top: 1px solid var(--border-color);
            }

            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: var(--spacing-xs);
                padding: 16px 32px;
                border-radius: var(--border-radius-sm);
                font-weight: 600;
                font-size: 1rem;
                text-decoration: none;
                border: none;
                cursor: pointer;
                transition: var(--transition);
                position: relative;
                overflow: hidden;
                min-width: 160px;
            }

            .btn::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
                transition: var(--transition);
            }

            .btn:hover::before {
                left: 100%;
            }

            .btn-primary {
                background: var(--primary-gradient);
                color: white;
                box-shadow: var(--shadow-md);
            }

            .btn-primary:hover {
                transform: translateY(-3px);
                box-shadow: var(--shadow-lg);
            }

            .btn-secondary {
                background: var(--bg-secondary);
                color: var(--text-secondary);
                border: 2px solid var(--border-color);
            }

            .btn-secondary:hover {
                background: var(--bg-accent);
                border-color: var(--primary-color);
                color: var(--primary-color);
                transform: translateY(-2px);
            }

            .btn:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none !important;
            }

            .loading {
                position: relative;
            }

            .loading i {
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .page-content-wrapper {
                    padding: var(--spacing-sm);
                }

                .game-title {
                    font-size: 2rem;
                    flex-direction: column;
                    gap: var(--spacing-xs);
                }

                .game-header {
                    padding: var(--spacing-lg) var(--spacing-md);
                }

                .game-content {
                    padding: var(--spacing-lg) var(--spacing-md);
                }

                .form-row {
                    grid-template-columns: 1fr;
                }

                .btn-container {
                    flex-direction: column;
                }

                .btn {
                    width: 100%;
                }

                .pro-tip-card {
                    flex-direction: column;
                    text-align: center;
                }

                .form-card {
                    padding: var(--spacing-md);
                }
            }

            @media (max-width: 480px) {
                .game-title {
                    font-size: 1.6rem;
                }

                .form-card-title {
                    font-size: 1.2rem;
                }
            }

            /* Animation Classes */
            .slide-in {
                animation: slideIn 0.6s ease-out;
            }

            @keyframes slideIn {
                from { opacity: 0; transform: translateX(-30px); }
                to { opacity: 1; transform: translateX(0); }
            }

            .fade-in {
                animation: fadeIn 0.8s ease-out;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            /* Custom Scrollbar */
            .search-results::-webkit-scrollbar {
                width: 6px;
            }

            .search-results::-webkit-scrollbar-track {
                background: var(--bg-secondary);
            }

            .search-results::-webkit-scrollbar-thumb {
                background: var(--primary-color);
                border-radius: 3px;
            }

            .search-results::-webkit-scrollbar-thumb:hover {
                background: var(--secondary-color);
            }
        </style>
    @endsection

    @section('preloader')
        {{-- <livewire:layout.frontend.preloader /> --}}
    @endsection

    @section('header')
        <livewire:layout.frontend.header />
    @endsection

    @section('offcanvas')
        <livewire:layout.frontend.offcanvas />
    @endsection

    @section('pwa_alart')
        <livewire:layout.frontend.pwa_alart />
    @endsection

    <div class="page-content-wrapper">
        <div class="container">
            <div class="game-create-container" data-aos="fade-up" data-aos-duration="800">

                <!-- Header Section -->
                <div class="game-header">
                    <div class="header-content">
                        <h1 class="game-title">
                            <i class="fas fa-dice-d20"></i>
                            Create New Hajari Game
                        </h1>
                        <p class="game-subtitle">Set up your game, invite friends, and start playing!</p>
                    </div>
                </div>

                <!-- Content Section -->
                <div class="game-content">

                    <!-- Pro Tip Card -->
                    <div class="pro-tip-card" data-aos="fade-right" data-aos-delay="200">
                        <div class="tip-icon">
                            <i class="fas fa-lightbulb"></i>
                        </div>
                        <div class="tip-content">
                            <h4>Pro Gaming Tip</h4>
                            <p>Choose a competitive bid amount and schedule your game when most players are online. Evening hours (7-10 PM) typically have the highest participation rates!</p>
                        </div>
                    </div>

                    <form wire:submit.prevent="createGame">

                        <!-- Game Information Card -->
                        <div class="form-card" data-aos="fade-up" data-aos-delay="300">
                            <div class="form-card-header">
                                <div class="form-card-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <h3 class="form-card-title">Game Information</h3>
                            </div>

                            <div class="form-group">
                                <label class="form-label required" for="title">Game Title</label>
                                <input
                                    type="text"
                                    id="title"
                                    class="form-control"
                                    wire:model.defer="title"
                                    placeholder="Enter an exciting game title (e.g., 'Friday Night Champions')"
                                    maxlength="255"
                                    required
                                >
                                @error('title') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="description">Game Description</label>
                                <textarea
                                    id="description"
                                    class="form-control"
                                    wire:model.defer="description"
                                    rows="4"
                                    placeholder="Add special rules, bonus rounds, or any instructions for players..."
                                    maxlength="1000"
                                ></textarea>
                                <div class="form-help-text">
                                    <i class="fas fa-info-circle"></i>
                                    Optional: Add game rules or special instructions
                                </div>
                            </div>
                        </div>

                        <!-- Game Settings Card -->
                        <div class="form-card" data-aos="fade-up" data-aos-delay="400">
                            <div class="form-card-header">
                                <div class="form-card-icon">
                                    <i class="fas fa-cogs"></i>
                                </div>
                                <h3 class="form-card-title">Game Configuration</h3>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label required" for="bid_amount">Entry Fee (৳)</label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            id="bid_amount"
                                            class="form-control"
                                            wire:model.defer="bid_amount"
                                            min="1"
                                            max="10000"
                                            placeholder="20"
                                            required
                                        >
                                        <span class="input-group-text">৳</span>
                                    </div>
                                    <div class="form-help-text">
                                        <i class="fas fa-coins"></i>
                                        Recommended: ৳20-৳500 for competitive games
                                    </div>
                                    @error('bid_amount') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>

                                <div class="form-group">
                                    <label class="form-label required" for="scheduled_at">Game Start Time</label>
                                    <input
                                        type="datetime-local"
                                        id="scheduled_at"
                                        class="form-control"
                                        wire:model.defer="scheduled_at"
                                        required
                                    >
                                    <div class="form-help-text">
                                        <i class="fas fa-clock"></i>
                                        Schedule at least 1 hour in advance
                                    </div>
                                    @error('scheduled_at') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Players Invitation Card -->
                        <div class="form-card" >
                            <div class="form-card-header">
                                <div class="form-card-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <h3 class="form-card-title">Invite Players</h3>
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="search_users">Search & Add Players</label>
                                <div class="search-container">
                                    <input
                                        type="text"
                                        id="search_users"
                                        class="form-control search-input"
                                        wire:model.live="search_users"
                                        placeholder="Search by name, email or username..."
                                        autocomplete="off"
                                    >

                                    @if(!empty($available_users))
                                    <div class="search-results ">
                                        @foreach($available_users as $user)
                                        <div class="user-item" wire:click="addUser({{ $user['id'] }})">
                                            <div class="user-avatar">
                                                @if(isset($user['avatar']))
                                                    <img src="{{ $user['avatar'] }}" alt="{{ $user['name'] }}">
                                                @else
                                                    {{ strtoupper(substr($user['name'], 0, 1)) }}
                                                @endif
                                                <div class="online-indicator"></div>
                                            </div>
                                            <div class="user-info">
                                                <div class="user-name">{{ $user['name'] }}</div>
                                                <div class="user-email">{{ $user['email'] }}</div>
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                <div class="form-help-text">
                                    <i class="fas fa-search"></i>
                                    Type at least 2 characters to search for players
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Invited Players ({{ count($invited_users) }}/3)</label>
                                <div class="invited-players @if(count($invited_users) > 0) has-players @endif">
                                    @if(count($invited_users) > 0)
                                        @foreach($invited_users as $userId)
                                            @php
                                                $user = \App\Models\User::find($userId);
                                            @endphp
                                            @if($user)
                                            <div class="player-badge">
                                                <div class="player-avatar-small">
                                                    @if($user->avatar)
                                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}">
                                                    @else
                                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                                    @endif
                                                </div>
                                                <span>{{ $user->name }}</span>
                                                <i class="fas fa-times remove-player" wire:click="removeUser({{ $userId }})"></i>
                                            </div>
                                            @endif
                                        @endforeach
                                    @else
                                        <div class="empty-state">
                                            <i class="fas fa-user-plus"></i>
                                            <span>No players invited yet. Search and add players above!</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-help-text">
                                    <i class="fas fa-info-circle"></i>
                                    You can invite up to 3 players. More players = bigger prize pool!
                                </div>
                                @error('invited_users') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="btn-container" data-aos="fade-up" data-aos-delay="600">
                            <a href="{{ route('games.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" @if($loading ?? false) disabled @endif>
                                @if($loading ?? false)
                                    <i class="fas fa-spinner loading"></i>
                                    Creating...
                                @else
                                    <i class="fas fa-rocket"></i>
                                    Create Game
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @section('footer')
        <livewire:layout.frontend.footer />
    @endsection

    @section('JS')
        @include('livewire.layout.frontend.js')

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Initialize AOS (Animate On Scroll)
                AOS.init({
                    duration: 800,
                    once: true,
                    offset: 50
                });

                // Auto-hide search results when clicking outside
                document.addEventListener('click', function(e) {
                    const searchContainer = document.querySelector('.search-container');
                    const searchResults = document.querySelector('.search-results');

                    if (searchContainer && !searchContainer.contains(e.target)) {
                        if (searchResults) {
                            searchResults.classList.remove('active');
                        }
                    }
                });

                // Show search results when focusing on search input
                const searchInput = document.querySelector('#search_users');
                if (searchInput) {
                    searchInput.addEventListener('focus', function() {
                        const searchResults = document.querySelector('.search-results');
                        if (searchResults && searchResults.children.length > 0) {
                            searchResults.classList.add('active');
                        }
                    });
                }

                // Enhanced form validation
                const form = document.querySelector('form');
                const titleInput = document.querySelector('#title');
                const bidAmountInput = document.querySelector('#bid_amount');
                const scheduledAtInput = document.querySelector('#scheduled_at');

                if (form) {
                    form.addEventListener('submit', function(e) {
                        let isValid = true;
                        let errors = [];

                        // Title validation
                        if (!titleInput.value.trim()) {
                            errors.push('Game title is required');
                            isValid = false;
                        } else if (titleInput.value.length < 3) {
                            errors.push('Game title must be at least 3 characters long');
                            isValid = false;
                        }

                        // Bid amount validation
                        const bidAmount = parseFloat(bidAmountInput.value);
                        if (!bidAmount || bidAmount < 1) {
                            errors.push('Entry fee must be at least ৳1');
                            isValid = false;
                        } else if (bidAmount > 10000) {
                            errors.push('Entry fee cannot exceed ৳10,000');
                            isValid = false;
                        }

                        // Schedule validation
                        if (!scheduledAtInput.value) {
                            errors.push('Please select a game start time');
                            isValid = false;
                        } else {
                            const scheduledTime = new Date(scheduledAtInput.value);
                            const now = new Date();
                            const oneHourFromNow = new Date(now.getTime() + 60 * 60 * 1000);

                            if (scheduledTime <= oneHourFromNow) {
                                errors.push('Game must be scheduled at least 1 hour in advance');
                                isValid = false;
                            }
                        }

                        if (!isValid) {
                            e.preventDefault();
                            Swal.fire({
                                title: 'Validation Error',
                                html: errors.join('<br>'),
                                icon: 'error',
                                confirmButtonText: 'Fix Issues',
                                confirmButtonColor: '#667eea',
                                customClass: {
                                    popup: 'animated shake'
                                }
                            });
                        }
                    });
                }

                // Real-time bid amount formatting
                if (bidAmountInput) {
                    bidAmountInput.addEventListener('input', function() {
                        let value = this.value.replace(/[^\d]/g, '');
                        if (value) {
                            // Add comma separators for thousands
                            value = parseInt(value).toLocaleString('en-BD');
                            this.dataset.display = value;
                        }
                    });
                }

                // Enhanced datetime input experience
                if (scheduledAtInput) {
                    // Set minimum date to 1 hour from now
                    const now = new Date();
                    const oneHourFromNow = new Date(now.getTime() + 60 * 60 * 1000);
                    const minDateTime = oneHourFromNow.toISOString().slice(0, 16);
                    scheduledAtInput.min = minDateTime;

                    // Set default value if empty
                    if (!scheduledAtInput.value) {
                        scheduledAtInput.value = minDateTime;
                    }
                }

                // Smooth scroll to form sections on validation errors
                const errorElements = document.querySelectorAll('.text-danger');
                if (errorElements.length > 0) {
                    errorElements[0].closest('.form-card').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                }

                // Auto-save form data to prevent loss
                const formInputs = form?.querySelectorAll('input, textarea, select');
                if (formInputs) {
                    formInputs.forEach(input => {
                        // Load saved data
                        const savedValue = localStorage.getItem(`game_create_${input.id}`);
                        if (savedValue && !input.value) {
                            input.value = savedValue;
                        }

                        // Save on change
                        input.addEventListener('input', function() {
                            localStorage.setItem(`game_create_${input.id}`, input.value);
                        });
                    });
                }

                // Clear saved data on successful submission
                window.addEventListener('beforeunload', function() {
                    // Only clear if form was successfully submitted
                    if (form && form.classList.contains('submitted')) {
                        formInputs?.forEach(input => {
                            localStorage.removeItem(`game_create_${input.id}`);
                        });
                    }
                });
            });

            // Livewire event listeners
            document.addEventListener('livewire:load', function () {
                // Success message
                Livewire.on('gameCreated', function () {
                    Swal.fire({
                        title: '🎉 Game Created Successfully!',
                        text: 'Your Hajari game has been created and invitations sent to players.',
                        icon: 'success',
                        confirmButtonText: 'View Game',
                        confirmButtonColor: '#667eea',
                        customClass: {
                            popup: 'animated bounceIn'
                        },
                        showClass: {
                            popup: 'animate__animated animate__bounceIn'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__bounceOut'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Clear saved form data
                            const formInputs = document.querySelectorAll('form input, form textarea, form select');
                            formInputs.forEach(input => {
                                localStorage.removeItem(`game_create_${input.id}`);
                            });
                        }
                    });
                });

                // Error message
                Livewire.on('gameCreationError', function (message) {
                    Swal.fire({
                        title: 'Oops! Something went wrong',
                        text: message,
                        icon: 'error',
                        confirmButtonText: 'Try Again',
                        confirmButtonColor: '#667eea',
                        customClass: {
                            popup: 'animated shake'
                        }
                    });
                });

                // Insufficient balance error
                Livewire.on('insufficientBalance', function () {
                    Swal.fire({
                        title: 'Insufficient Balance',
                        text: 'You don\'t have enough credits to create this game. Please add funds to your account.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Add Funds',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#667eea',
                        cancelButtonColor: '#6c757d'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/wallet/recharge';
                        }
                    });
                });

                // Player added successfully
                Livewire.on('playerAdded', function (playerName) {
                    // Show a subtle toast notification
                    const toast = document.createElement('div');
                    toast.className = 'toast-notification';
                    toast.innerHTML = `
                        <i class="fas fa-user-check"></i>
                        <span>${playerName} added successfully!</span>
                    `;
                    document.body.appendChild(toast);

                    // Auto remove after 3 seconds
                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                });

                // Player removed
                Livewire.on('playerRemoved', function (playerName) {
                    const toast = document.createElement('div');
                    toast.className = 'toast-notification warning';
                    toast.innerHTML = `
                        <i class="fas fa-user-minus"></i>
                        <span>${playerName} removed from game</span>
                    `;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                });
            });

            // Enhanced user interaction effects
            document.addEventListener('DOMContentLoaded', function() {
                // Add ripple effect to buttons
                const buttons = document.querySelectorAll('.btn');
                buttons.forEach(button => {
                    button.addEventListener('click', function(e) {
                        const ripple = document.createElement('span');
                        const rect = button.getBoundingClientRect();
                        const size = Math.max(rect.width, rect.height);
                        const x = e.clientX - rect.left - size / 2;
                        const y = e.clientY - rect.top - size / 2;

                        ripple.style.cssText = `
                            position: absolute;
                            width: ${size}px;
                            height: ${size}px;
                            left: ${x}px;
                            top: ${y}px;
                            background: rgba(255,255,255,0.3);
                            border-radius: 50%;
                            transform: scale(0);
                            animation: ripple 0.6s linear;
                            pointer-events: none;
                        `;

                        button.style.position = 'relative';
                        button.style.overflow = 'hidden';
                        button.appendChild(ripple);

                        setTimeout(() => ripple.remove(), 600);
                    });
                });

                // Add CSS for ripple animation
                const style = document.createElement('style');
                style.textContent = `
                    @keyframes ripple {
                        to {
                            transform: scale(2);
                            opacity: 0;
                        }
                    }

                    .toast-notification {
                        position: fixed;
                        top: 20px;
                        right: 20px;
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 12px 20px;
                        border-radius: 8px;
                        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                        z-index: 1000;
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        animation: slideInRight 0.3s ease-out;
                    }

                    .toast-notification.warning {
                        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
                    }

                    @keyframes slideInRight {
                        from {
                            transform: translateX(100%);
                            opacity: 0;
                        }
                        to {
                            transform: translateX(0);
                            opacity: 1;
                        }
                    }
                `;
                document.head.appendChild(style);
            });
        </script>
    @endsection
</div>
