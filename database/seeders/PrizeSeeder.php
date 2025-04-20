<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PrizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('prizes')->insert([
            [
                'name' => 'Full House',
                'amount' => 6000,
                'description' => 'Full House Winner',
                'is_active' => 1,
                'image_path' => 'public/prize/67faa22300818.png',
                'created_at' => Carbon::parse('2025-04-12 11:25:59'),
                'updated_at' => Carbon::parse('2025-04-12 12:28:13'),
            ],
            [
                'name' => 'Top Line',
                'amount' => 5000,
                'description' => 'Top Line Winner',
                'is_active' => 1,
                'image_path' => 'public/prize/67faa6156d954.png',
                'created_at' => Carbon::parse('2025-04-12 11:42:46'),
                'updated_at' => Carbon::parse('2025-04-12 12:28:23'),
            ],
            [
                'name' => 'Middle Line',
                'amount' => 4000,
                'description' => 'Middle Line Winner',
                'is_active' => 1,
                'image_path' => 'public/prize/67faa743650c7.png',
                'created_at' => Carbon::parse('2025-04-12 11:47:48'),
                'updated_at' => Carbon::parse('2025-04-12 12:28:32'),
            ],
            [
                'name' => 'Bottom',
                'amount' => 3000,
                'description' => 'Bottom Line Winner',
                'is_active' => 1,
                'image_path' => 'public/prize/67faa778e67f7.png',
                'created_at' => Carbon::parse('2025-04-12 11:48:41'),
                'updated_at' => Carbon::parse('2025-04-12 12:33:45'),
            ],
            [
                'name' => 'First Five',
                'amount' => 2000,
                'description' => 'First Five Winner',
                'is_active' => 1,
                'image_path' => 'public/prize/67faa7b862cc8.png',
                'created_at' => Carbon::parse('2025-04-12 11:49:44'),
                'updated_at' => Carbon::parse('2025-04-12 12:28:55'),
            ],
            [
                'name' => 'Corner',
                'amount' => 1000,
                'description' => 'Corner Numbers',
                'is_active' => 1,
                'image_path' => 'public/prize/67faa7fbba935.png',
                'created_at' => Carbon::parse('2025-04-12 11:50:52'),
                'updated_at' => Carbon::parse('2025-04-12 12:29:06'),
            ],
        ]);
    }
}
