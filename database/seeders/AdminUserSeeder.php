<?php

namespace Database\Seeders;

use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::query()->create([
            "name" => "Admin",
            "username" => "admin",
            "password" => Hash::make("admin"),
            "level" => User::LEVEL_ADMIN,
        ]);
    }
}
