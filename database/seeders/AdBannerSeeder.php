<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ad_banners')->insert([
            [
                'title' => '.',
                'text' => '.',
                'button_name' => 'Play Now',
                'image_path' => 'public/banner/67faa90a7f748.jpeg',
                'url' => null,
                'is_active' => 1,
                'created_at' => Carbon::parse('2025-04-12 11:55:23'),
                'updated_at' => Carbon::parse('2025-04-12 11:55:23'),
            ],
            [
                'title' => '.',
                'text' => '.',
                'button_name' => 'Play Now',
                'image_path' => 'public/banner/67faa92a469d8.png',
                'url' => null,
                'is_active' => 1,
                'created_at' => Carbon::parse('2025-04-12 11:55:54'),
                'updated_at' => Carbon::parse('2025-04-12 11:55:54'),
            ],
            [
                'title' => '.',
                'text' => '.',
                'button_name' => 'Play Now',
                'image_path' => 'public/banner/67faa951a7b5d.png',
                'url' => null,
                'is_active' => 1,
                'created_at' => Carbon::parse('2025-04-12 11:56:34'),
                'updated_at' => Carbon::parse('2025-04-12 11:56:34'),
            ],
        ]);
    }
}

