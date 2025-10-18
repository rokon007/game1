<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['key' => 'jackpot_limit', 'value' => '100000'],
            ['key' => 'small_reward_rate', 'value' => '0.02'],
            ['key' => 'win_chance_percent', 'value' => '20'],
            ['key' => 'min_bet', 'value' => '10'],
            ['key' => 'max_bet', 'value' => '10000']
        ];


        foreach ($defaults as $d) {
            SystemSetting::updateOrCreate(['key' => $d['key']], $d);
        }
    }
}
