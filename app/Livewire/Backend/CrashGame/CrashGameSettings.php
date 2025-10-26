<?php
// app/Livewire/Backend/CrashGame/CrashGameSettings.php

namespace App\Livewire\Backend\CrashGame;

use App\Models\CrashGameSetting;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CrashGameSettings extends Component
{
    public $settings;

    // Form fields
    public $house_edge;
    public $min_multiplier;
    public $max_multiplier;
    public $bet_waiting_time;
    public $min_bet_amount;
    public $max_bet_amount;
    public $is_active;

    // Speed control fields
    public $multiplier_increment;
    public $multiplier_interval_ms;
    public $max_speed_multiplier;
    public $enable_auto_acceleration;
    public $speed_profile;

    // Game process control
    public $is_running = false;
    public $process_status = 'Unknown';
    public $process_id = null;
    public $process_started_at = null;

    // Environment variables - INITIALIZE THEM
    public $is_production = false;
    public $environment_info = [];
    public $can_execute = false;
    public $server_info = [];

    public function mount()
    {
        $this->settings = CrashGameSetting::first();
        $this->loadSettings();
        $this->checkGameStatus();
        $this->checkServerEnvironment(); // এই method টি call করুন
    }

    /**
     * Check server environment and permissions
     */
    public function checkServerEnvironment()
    {
        $this->is_production = app()->environment('production');

        $this->server_info = [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'server_addr' => $_SERVER['SERVER_ADDR'] ?? 'Unknown',
            'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
            'user' => function_exists('get_current_user') ? get_current_user() : 'Unknown',
            'environment' => app()->environment(),
        ];

        // Check if we can execute shell commands
        $this->can_execute = $this->checkShellExecution();

        // Check if artisan path is correct
        $artisanPath = base_path('artisan');
        $this->server_info['artisan_exists'] = file_exists($artisanPath);
        $this->server_info['artisan_path'] = $artisanPath;

        $this->environment_info = [
            'environment' => app()->environment(),
            'can_shell_exec' => $this->can_execute,
            'is_windows' => strtoupper(substr(PHP_OS, 0, 3)) === 'WIN',
            'php_version' => PHP_VERSION,
        ];
    }

    /**
     * Check if shell commands can be executed
     */
    private function checkShellExecution(): bool
    {
        // Check if shell_exec is disabled
        if (!function_exists('shell_exec')) {
            return false;
        }

        // Check safe mode
        if (ini_get('safe_mode')) {
            return false;
        }

        // Check disabled functions
        $disabled = ini_get('disable_functions');
        if ($disabled && in_array('shell_exec', explode(',', $disabled))) {
            return false;
        }

        // Test with a simple command
        try {
            $test = shell_exec('echo "test"');
            return trim($test) === 'test';
        } catch (\Exception $e) {
            return false;
        }
    }

    public function loadSettings()
    {
        $this->house_edge = $this->settings->house_edge * 100;
        $this->min_multiplier = $this->settings->min_multiplier;
        $this->max_multiplier = $this->settings->max_multiplier;
        $this->bet_waiting_time = $this->settings->bet_waiting_time;
        $this->min_bet_amount = $this->settings->min_bet_amount;
        $this->max_bet_amount = $this->settings->max_bet_amount;
        $this->is_active = $this->settings->is_active;

        // Speed control
        $this->multiplier_increment = $this->settings->multiplier_increment;
        $this->multiplier_interval_ms = $this->settings->multiplier_interval_ms;
        $this->max_speed_multiplier = $this->settings->max_speed_multiplier;
        $this->enable_auto_acceleration = $this->settings->enable_auto_acceleration;
        $this->speed_profile = $this->settings->speed_profile;
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
            'enable_auto_acceleration' => 'boolean'
        ]);

        $this->settings->update([
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
        ]);

        Cache::forget('crash_game_settings');
        session()->flash('message', 'Settings updated successfully!');
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

        if ($this->enable_auto_acceleration) {
            $totalTime = 0;
            $currentMultiplier = $from;

            while ($currentMultiplier < $to) {
                $currentIncrement = $this->calculateDynamicIncrement($currentMultiplier);
                $currentMultiplier += $currentIncrement;
                $totalTime += $interval;

                if ($totalTime > 600) break;
            }

            $seconds = round($totalTime);
        } else {
            $steps = ($to - $from) / $increment;
            $seconds = round($steps * $interval);
        }

        return $seconds < 60 ? "~{$seconds} seconds" : "~" . round($seconds / 60) . " minutes";
    }

    private function calculateDynamicIncrement(float $currentMultiplier): float
    {
        $baseIncrement = $this->multiplier_increment;
        $maxSpeedMultiplier = $this->max_speed_multiplier;

        if ($currentMultiplier >= $maxSpeedMultiplier) return $baseIncrement;

        if ($currentMultiplier > 10.00) return $baseIncrement * 3;
        elseif ($currentMultiplier > 5.00) return $baseIncrement * 2;
        elseif ($currentMultiplier > 3.00) return $baseIncrement * 1.5;
        elseif ($currentMultiplier > 2.00) return $baseIncrement * 1.2;

        return $baseIncrement;
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

            // Production vs Local command
            if ($this->is_production) {
                $logFile = storage_path('logs/crash-game.log');
                $command = "nohup php {$artisanPath} crash:run >> {$logFile} 2>&1 & echo $!";
            } else {
                $command = "php {$artisanPath} crash:run > /dev/null 2>&1 & echo $!";
            }

            $pid = shell_exec($command);
            $pid = trim($pid);

            if (empty($pid) || !is_numeric($pid)) {
                throw new \Exception('No PID returned. Server may not support background processes.');
            }

            sleep(2);

            if (!$this->isProcessRunning($pid)) {
                throw new \Exception('Process started but not running. Check server permissions.');
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
        $this->checkServerEnvironment(); // Environment infoও refresh করুন
        session()->flash('info', 'Status refreshed!');
    }

    public function render()
    {
        return view('livewire.backend.crash-game.crash-game-settings')->layout('livewire.backend.base');
    }
}
