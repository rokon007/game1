<?php
// app/Livewire/Backend/CrashGame/CrashGameSettings.php - UPDATED

namespace App\Livewire\Backend\CrashGame;

use App\Models\CrashGameSetting;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;

class CrashGameSettings extends Component
{
    public $settings;

    // Existing fields
    public $house_edge;
    public $min_multiplier;
    public $max_multiplier;
    public $bet_waiting_time;
    public $min_bet_amount;
    public $max_bet_amount;
    public $is_active;
    public $multiplier_increment;
    public $multiplier_interval_ms;
    public $max_speed_multiplier;
    public $enable_auto_acceleration;
    public $speed_profile;

    // ✅ NEW: Bet Pool & Commission Settings
    public $admin_commission_rate;
    public $commission_type;
    public $fixed_commission_amount;
    public $min_pool_amount;
    public $max_payout_ratio;
    public $enable_dynamic_crash;
    public $crash_increase_per_cashout;

    // Game process control (existing)
    public $is_running = false;
    public $process_status = 'Unknown';
    public $process_id = null;
    public $process_started_at = null;
    public $is_production = false;
    public $environment_info = [];
    public $can_execute = false;
    public $server_info = [];

    public $enable_pool_rollover;
    public $rollover_percentage;
    public $min_rollover_amount;
    public $max_rollover_amount;
    public $rollover_includes_commission;

    public function mount()
    {
        $this->settings = CrashGameSetting::first();
        $this->loadSettings();
        $this->checkGameStatus();
        $this->checkServerEnvironment();
    }

    public function loadSettings()
    {
        // Existing settings
        $this->house_edge = $this->settings->house_edge * 100;
        $this->min_multiplier = $this->settings->min_multiplier;
        $this->max_multiplier = $this->settings->max_multiplier;
        $this->bet_waiting_time = $this->settings->bet_waiting_time;
        $this->min_bet_amount = $this->settings->min_bet_amount;
        $this->max_bet_amount = $this->settings->max_bet_amount;
        $this->is_active = $this->settings->is_active;
        $this->multiplier_increment = $this->settings->multiplier_increment;
        $this->multiplier_interval_ms = $this->settings->multiplier_interval_ms;
        $this->max_speed_multiplier = $this->settings->max_speed_multiplier;
        $this->enable_auto_acceleration = $this->settings->enable_auto_acceleration;
        $this->speed_profile = $this->settings->speed_profile;

        // ✅ NEW: Bet Pool Settings
        $this->admin_commission_rate = $this->settings->admin_commission_rate ?? 10.00;
        $this->commission_type = $this->settings->commission_type ?? 'percentage';
        $this->fixed_commission_amount = $this->settings->fixed_commission_amount ?? 0;
        $this->min_pool_amount = $this->settings->min_pool_amount ?? 100.00;
        $this->max_payout_ratio = $this->settings->max_payout_ratio ?? 0.90;
        $this->enable_dynamic_crash = $this->settings->enable_dynamic_crash ?? true;
        $this->crash_increase_per_cashout = $this->settings->crash_increase_per_cashout ?? 0.50;

        $this->enable_pool_rollover = $this->settings->enable_pool_rollover ?? true;
        $this->rollover_percentage = $this->settings->rollover_percentage ?? 100.00;
        $this->min_rollover_amount = $this->settings->min_rollover_amount ?? 10.00;
        $this->max_rollover_amount = $this->settings->max_rollover_amount ?? 10000.00;
        $this->rollover_includes_commission = $this->settings->rollover_includes_commission ?? false;
    }

    public function updated($property, $value)
    {
        if ($property === 'speed_profile') {
            if ($value !== 'custom') {
                $this->loadSpeedProfile($value);
            }
        }
    }

    public function updateSettings()
    {
        $this->validate([
            // Existing validations
            'house_edge' => 'required|numeric|min:1|max:20',
            'min_multiplier' => 'required|numeric|min:1.01|max:5.00',
            'max_multiplier' => 'required|numeric|min:10.00|max:1000.00',
            'bet_waiting_time' => 'required|integer|min:5|max:60',
            'min_bet_amount' => 'required|numeric|min:1',
            'max_bet_amount' => 'required|numeric|min:10',
            'is_active' => 'boolean',
            'speed_profile' => 'required|in:slow,medium,fast,custom',
            'multiplier_increment' => 'required_if:speed_profile,custom|numeric|min:0.001|max:0.1',
            'multiplier_interval_ms' => 'required_if:speed_profile,custom|integer|min:10|max:1000',
            'max_speed_multiplier' => 'required_if:speed_profile,custom|numeric|min:2|max:100',
            'enable_auto_acceleration' => 'boolean',

            // ✅ NEW: Bet Pool Validations
            'admin_commission_rate' => 'required|numeric|min:0|max:50',
            'commission_type' => 'required|in:percentage,fixed',
            'fixed_commission_amount' => 'required_if:commission_type,fixed|numeric|min:0',
            'min_pool_amount' => 'required|numeric|min:0',
            'max_payout_ratio' => 'required|numeric|min:0.5|max:1.0',
            'enable_dynamic_crash' => 'boolean',
            'crash_increase_per_cashout' => 'required|numeric|min:0.1|max:5.0',

            'enable_pool_rollover' => 'boolean',
            'rollover_percentage' => 'required|numeric|min:0|max:100',
            'min_rollover_amount' => 'required|numeric|min:0',
            'max_rollover_amount' => 'required|numeric|min:0',
            'rollover_includes_commission' => 'boolean',
        ]);

        $this->settings->update([
            // Existing updates
            'house_edge' => $this->house_edge / 100,
            'min_multiplier' => $this->min_multiplier,
            'max_multiplier' => $this->max_multiplier,
            'bet_waiting_time' => $this->bet_waiting_time,
            'min_bet_amount' => $this->min_bet_amount,
            'max_bet_amount' => $this->max_bet_amount,
            'is_active' => $this->is_active,
            'multiplier_increment' => $this->multiplier_increment,
            'multiplier_interval_ms' => $this->multiplier_interval_ms,
            'max_speed_multiplier' => $this->max_speed_multiplier,
            'enable_auto_acceleration' => $this->enable_auto_acceleration,
            'speed_profile' => $this->speed_profile,

            // ✅ NEW: Bet Pool Updates
            'admin_commission_rate' => $this->admin_commission_rate,
            'commission_type' => $this->commission_type,
            'fixed_commission_amount' => $this->fixed_commission_amount,
            'min_pool_amount' => $this->min_pool_amount,
            'max_payout_ratio' => $this->max_payout_ratio,
            'enable_dynamic_crash' => $this->enable_dynamic_crash,
            'crash_increase_per_cashout' => $this->crash_increase_per_cashout,

            'enable_pool_rollover' => $this->enable_pool_rollover,
            'rollover_percentage' => $this->rollover_percentage,
            'min_rollover_amount' => $this->min_rollover_amount,
            'max_rollover_amount' => $this->max_rollover_amount,
            'rollover_includes_commission' => $this->rollover_includes_commission,
        ]);

        Cache::forget('crash_game_settings');
        session()->flash('message', 'Settings updated successfully! New settings will apply to next game.');
    }

    // ✅ NEW: Calculate estimated commission
    public function getEstimatedCommission(): float
    {
        if ($this->commission_type === 'fixed') {
            return $this->fixed_commission_amount;
        }

        $sampleBetPool = 1000; // Sample calculation for 1000 BDT
        return ($sampleBetPool * $this->admin_commission_rate) / 100;
    }

    // ✅ NEW: Calculate max safe payout
    public function getMaxSafePayout(): float
    {
        $samplePool = 1000;
        $commission = $this->getEstimatedCommission();
        $availablePool = $samplePool - $commission;
        return $availablePool * $this->max_payout_ratio;
    }

    // Existing methods (checkServerEnvironment, startGame, stopGame, etc.)
    public function checkServerEnvironment()
    {
        $this->is_production = app()->environment('production');
        $this->server_info = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'environment' => app()->environment(),
        ];
        $this->can_execute = $this->checkShellExecution();
        $this->environment_info = [
            'environment' => app()->environment(),
            'can_shell_exec' => $this->can_execute,
            'is_windows' => strtoupper(substr(PHP_OS, 0, 3)) === 'WIN',
        ];
    }

    private function checkShellExecution(): bool
    {
        if (!function_exists('shell_exec')) return false;
        if (ini_get('safe_mode')) return false;

        $disabled = ini_get('disable_functions');
        if ($disabled && in_array('shell_exec', explode(',', $disabled))) {
            return false;
        }

        try {
            $test = shell_exec('echo "test"');
            return trim($test) === 'test';
        } catch (\Exception $e) {
            return false;
        }
    }

    public function checkGameStatus()
    {
        $this->is_running = Cache::get('crash_game_running', false);
        $this->process_id = Cache::get('crash_game_pid');
        $this->process_started_at = Cache::get('crash_game_started_at');

        if ($this->is_running) {
            $this->process_status = 'Running';
            if ($this->process_id && !$this->isProcessRunning($this->process_id)) {
                $this->is_running = false;
                $this->process_status = 'Stopped';
                Cache::forget('crash_game_running');
                Cache::forget('crash_game_pid');
            }
        } else {
            $this->process_status = 'Stopped';
        }
    }

    private function isProcessRunning($pid): bool
    {
        if (empty($pid)) return false;
        try {
            exec("ps -p {$pid} -o pid=", $output);
            return !empty($output);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function startGame()
    {
        if ($this->is_running) {
            session()->flash('error', 'Game is already running!');
            return;
        }

        try {
            Cache::forget('crash_game_stop');
            $artisanPath = base_path('artisan');

            if ($this->is_production) {
                $logFile = storage_path('logs/crash-game.log');
                $command = "nohup php {$artisanPath} crash:run >> {$logFile} 2>&1 & echo $!";
            } else {
                $command = "php {$artisanPath} crash:run > /dev/null 2>&1 & echo $!";
            }

            $pid = shell_exec($command);
            $pid = trim($pid);

            if (empty($pid) || !is_numeric($pid)) {
                throw new \Exception('No PID returned');
            }

            sleep(2);

            if (!$this->isProcessRunning($pid)) {
                throw new \Exception('Process started but not running');
            }

            Cache::put('crash_game_running', true, 3600);
            Cache::put('crash_game_pid', $pid, 3600);
            Cache::put('crash_game_started_at', now()->toDateTimeString(), 3600);

            $this->is_running = true;
            $this->process_status = 'Running';
            $this->process_id = $pid;

            session()->flash('message', "Game started successfully! PID: {$pid}");

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to start game: ' . $e->getMessage());
            Cache::forget('crash_game_running');
            Cache::forget('crash_game_pid');
        }
    }

    public function stopGame()
    {
        if (!$this->is_running) {
            session()->flash('error', 'Game is not running!');
            return;
        }

        try {
            Cache::put('crash_game_stop', true, 60);

            if ($this->process_id) {
                exec("kill {$this->process_id} 2>/dev/null");
                sleep(2);
                exec("kill -9 {$this->process_id} 2>/dev/null");
            }

            exec("pkill -f 'artisan crash:run' 2>/dev/null");

            Cache::forget('crash_game_running');
            Cache::forget('crash_game_pid');
            Cache::forget('crash_game_started_at');
            Cache::forget('crash_game_stop');

            $this->is_running = false;
            $this->process_status = 'Stopped';
            $this->process_id = null;

            session()->flash('message', 'Game stopped successfully!');

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to stop game: ' . $e->getMessage());
        }
    }

    public function restartGame()
    {
        $this->stopGame();
        sleep(3);
        $this->startGame();
        session()->flash('message', 'Game restarted successfully!');
    }

    public function forceStopGame()
    {
        exec("pkill -9 -f 'artisan crash:run' 2>/dev/null");
        Cache::forget('crash_game_running');
        Cache::forget('crash_game_pid');
        Cache::forget('crash_game_started_at');
        $this->is_running = false;
        $this->process_status = 'Stopped';
        session()->flash('message', 'Game force stopped successfully!');
    }

    public function refreshStatus()
    {
        $this->checkGameStatus();
        $this->checkServerEnvironment();
        session()->flash('info', 'Status refreshed!');
    }

    private function loadSpeedProfile($profile)
    {
        $profiles = [
            'slow' => [
                'multiplier_increment' => 0.005,
                'multiplier_interval_ms' => 200,
                'enable_auto_acceleration' => false
            ],
            'medium' => [
                'multiplier_increment' => 0.01,
                'multiplier_interval_ms' => 100,
                'enable_auto_acceleration' => true
            ],
            'fast' => [
                'multiplier_increment' => 0.02,
                'multiplier_interval_ms' => 50,
                'enable_auto_acceleration' => true
            ]
        ];

        if (isset($profiles[$profile])) {
            $this->multiplier_increment = $profiles[$profile]['multiplier_increment'];
            $this->multiplier_interval_ms = $profiles[$profile]['multiplier_interval_ms'];
            $this->enable_auto_acceleration = $profiles[$profile]['enable_auto_acceleration'];
        }
    }

    public function calculateTime($from, $to): string
    {
        $increment = $this->multiplier_increment;
        $interval = $this->multiplier_interval_ms / 1000;
        $steps = ($to - $from) / $increment;
        $seconds = round($steps * $interval);
        return $seconds < 60 ? "~{$seconds} seconds" : "~" . round($seconds / 60) . " minutes";
    }

    public function render()
    {
        return view('livewire.backend.crash-game.crash-game-settings')->layout('livewire.backend.base');
    }
}
