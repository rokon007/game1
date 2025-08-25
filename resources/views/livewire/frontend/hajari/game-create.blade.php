<div>
    @section('meta_description')
      <meta name="description" content="Housieblitz">
    @endsection
    @section('title')
        <title>Housieblitz|Hajari</title>
    @endsection

    @section('css')
        @include('livewire.layout.frontend.css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.all.min.js"></script>
        <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.4/dist/sweetalert2.min.css" rel="stylesheet">
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
            --spacing-sm: 12px;
            --spacing-md: 16px;
            --spacing-lg: 24px;
            --spacing-xl: 32px;
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
            padding: var(--spacing-sm);
        }

        .page-content-wrapper {
            max-width: 100%;
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
            padding: var(--spacing-lg) var(--spacing-md);
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .game-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 120px;
            height: 120px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .game-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 100px;
            height: 100px;
            background: rgba(255,255,255,0.08);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(180deg); }
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .game-title {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: var(--spacing-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-sm);
            animation: slideInDown 0.8s ease-out;
            color: white;
        }

        .game-title i {
            font-size: 1.8rem;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .game-subtitle {
            font-size: 0.9rem;
            opacity: 0.95;
            font-weight: 400;
            color:white;
            animation: slideInUp 0.8s ease-out 0.2s both;
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Content Section */
        .game-content {
            padding: var(--spacing-lg) var(--spacing-md);
        }

        /* Pro Tip Card */
        .pro-tip-card {
            background: linear-gradient(135deg, #667eea1a 0%, #764ba21a 100%);
            border: 1px solid rgba(102, 126, 234, 0.2);
            border-radius: var(--border-radius);
            padding: var(--spacing-md);
            margin-bottom: var(--spacing-lg);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--spacing-sm);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            text-align: center;
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
            width: 40px;
            height: 40px;
            background: var(--primary-gradient);
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            flex-shrink: 0;
            animation: bounce 2s ease-in-out infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-6px); }
            60% { transform: translateY(-3px); }
        }

        .tip-content h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: var(--spacing-xs);
        }

        .tip-content p {
            color: var(--text-secondary);
            font-size: 0.9rem;
            line-height: 1.5;
        }

        /* Form Cards */
        .form-card {
            background: var(--bg-primary);
            border-radius: var(--border-radius);
            padding: var(--spacing-md);
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

        .form-card-header {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            margin-bottom: var(--spacing-md);
            padding-bottom: var(--spacing-sm);
            border-bottom: 2px solid var(--bg-accent);
        }

        .form-card-icon {
            width: 36px;
            height: 36px;
            background: var(--primary-gradient);
            border-radius: var(--border-radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.1rem;
        }

        .form-card-title {
            font-size: 1.2rem;
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
            font-size: 0.9rem;
            position: relative;
        }

        .required::after {
            content: ' *';
            color: var(--danger-color);
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid var(--border-color);
            border-radius: var(--border-radius-sm);
            font-size: 0.95rem;
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
            display: flex;
            flex-direction: column;
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
            padding: 5px 10px;
            border-radius: var(--spacing-xs);
            font-weight: 600;
            font-size: 0.85rem;
            z-index: 2;
            pointer-events: none;
        }

        .form-help-text {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: var(--spacing-xs);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .form-help-text i {
            color: var(--accent-color);
            font-size: 0.9rem;
        }

        /* Search Section */
        .search-container {
            position: relative;
            margin-bottom: var(--spacing-md);
        }

        .search-input {
            padding-left: 50px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%23667eea' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 16px center;
            background-size: 18px;
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
            max-height: 250px;
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
        }

        .user-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: var(--spacing-sm);
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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
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
            font-size: 0.95rem;
            margin-bottom: 2px;
        }

        .user-email {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .online-indicator {
            width: 10px;
            height: 10px;
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
            70% { box-shadow: 0 0 0 8px rgba(0, 212, 170, 0); }
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
            padding: 6px 12px;
            font-weight: 600;
            color: var(--primary-color);
            position: relative;
            animation: fadeInScale 0.3s ease-out;
            transition: var(--transition-fast);
            font-size: 0.85rem;
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
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.8rem;
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
            font-size: 0.9rem;
            padding: 2px;
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
            padding: var(--spacing-sm);
            color: var(--text-muted);
            font-style: italic;
            min-height: 60px;
            font-size: 0.9rem;
            text-align: center;
        }

        .empty-state i {
            font-size: 1.2rem;
            margin-bottom: var(--spacing-xs);
            opacity: 0.5;
        }

        /* Buttons */
        .btn-container {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-sm);
            margin-top: var(--spacing-lg);
            padding-top: var(--spacing-md);
            border-top: 1px solid var(--border-color);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--spacing-xs);
            padding: 14px 20px;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
            width: 100%;
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

        /* Footer */
        .footer {
            text-align: center;
            padding: var(--spacing-md) 0;
            color: var(--text-muted);
            font-size: 0.85rem;
            margin-top: var(--spacing-lg);
        }

        /* Animation Classes */
        .slide-in {
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
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

        /* Media Queries for Larger Screens */
        @media (min-width: 768px) {
            body {
                padding: var(--spacing-md);
            }

            .page-content-wrapper {
                max-width: 700px;
                margin: 0 auto;
            }

            .game-title {
                font-size: 1.5rem;
            }

            .game-subtitle {
                font-size: 1rem;
            }

            .pro-tip-card {
                flex-direction: row;
                text-align: left;
            }

            .form-row {
                flex-direction: row;
            }

            .form-card {
                padding: var(--spacing-lg);
            }

            .btn-container {
                flex-direction: row;
            }

            .btn {
                width: auto;
            }
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
            <div class="game-create-container" style="margin-top: 40px; margin-bottom:40px;" data-aos="fade-up" data-aos-duration="800">

                <!-- Header Section -->
                <div class="game-header">
                    <div class="header-content">
                        <h6 class="game-title">
                            <i class="fas fa-dice-d20"></i>
                            Create New Hajari Game
                        </h6>
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

                    {{-- <form wire:submit.prevent="createGame"> --}}
                        <form wire:submit.prevent="confirmCreate">

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
                                    <label class="form-label required" for="bid_amount">Entry Fee (Credit)</label>
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
                                        <span class="input-group-text">Credit</span>
                                    </div>
                                    <div class="form-help-text">
                                        <i class="fas fa-coins"></i>
                                        Recommended: 20 Credit -500 Credit for competitive games
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
                                    <div class="search-results active">
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
            <!-- Add this modal code before the closing div tag -->
            <div class="modal-backdrop fade show" wire:click="$set('showConfirmationModal', false)" style="display: {{ $showConfirmationModal ? 'block' : 'none' }};"></div>

            <div class="modal fade show" tabindex="-1" style="display: {{ $showConfirmationModal ? 'block' : 'none' }}; background: rgba(0,0,0,0.5);">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content" style="border-radius: var(--border-radius-lg); overflow: hidden; box-shadow: var(--shadow-xl);">
                        <div class="modal-header" style="background: var(--primary-gradient); color: white; border-bottom: none;">
                            <h5 class="modal-title">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                Confirm Bid Deduction
                            </h5>
                        </div>
                        <div class="modal-body" style="padding: var(--spacing-lg);">
                            <p style="font-size: 1.1rem; margin-bottom: 1rem;">
                                {{ $bid_amount }} Credit has been deducted from your account.
                            </p>
                            <p style="color: #6c757d;">
                                This amount will be deposited into the Admin's account and will be transferred to the winner after the game ends.
                            </p>
                        </div>
                        <div class="modal-footer" style="border-top: none; padding: var(--spacing-md) var(--spacing-lg); background: var(--bg-secondary);">
                            <button type="button" class="btn btn-secondary" wire:click="$set('showConfirmationModal', false)">
                                <i class="fas fa-times me-2"></i>Cancel
                            </button>
                            <button type="button" class="btn btn-primary" wire:click="createGame">
                                <i class="fas fa-check me-2"></i>Confirm
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @section('footer')
    <livewire:layout.frontend.footer />
    @endsection


    @section('JS')
        @include('livewire.layout.frontend.js')
        <script>
            // Mobile-friendly datetime input setup
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours() + 1).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');

            document.getElementById('scheduled_at').min = `${year}-${month}-${day}T${hours}:${minutes}`;
            document.getElementById('scheduled_at').value = `${year}-${month}-${day}T${hours}:${minutes}`;
        </script>
    @endsection
</div>
