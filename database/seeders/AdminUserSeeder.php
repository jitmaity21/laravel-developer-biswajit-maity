<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456'),
            'user_name' => 'admin',
            'user_role' => 'admin',
            'is_email_verified' => 'Y',
            'user_pin' => '123456',
        ]);
    }
}
