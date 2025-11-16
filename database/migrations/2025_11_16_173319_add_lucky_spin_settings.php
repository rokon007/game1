<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\SystemSetting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new system settings for Lucky Spin
        $settings = [
            [
                'key' => 'jackpot_chance_percent',
                'value' => '0.1', // 0.1% chance when pool >= jackpot_limit
            ],
            [
                'key' => 'minimum_pool_reserve',
                'value' => '10000', // Minimum pool balance to keep
            ],
            [
                'key' => 'max_win_percentage',
                'value' => '50', // Max win = 50% of available pool
            ],
            [
                'key' => 'house_edge',
                'value' => '30', // 30% house edge (informational)
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value']]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'jackpot_chance_percent',
            'minimum_pool_reserve',
            'max_win_percentage',
            'house_edge',
        ];

        SystemSetting::whereIn('key', $keys)->delete();
    }
};
