<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'mobile' => '01700000000',
            'password' => Hash::make('12345678'), // নিরাপত্তার জন্য অবশ্যই হ্যাশ করতে হবে
            'role' => 'admin',
            'status' => 'active',
            'is_online' => false,
            'email_verified_at' => now(),
            'avatar' => 'public/avatars/admin.png',
        ]);
    }
}
