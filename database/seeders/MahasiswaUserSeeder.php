<?php

namespace Database\Seeders;

use App\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MahasiswaUserSeeder extends Seeder
{
    const PREFIX = "mahasiswa_";
    const COUNT = 20;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::beginTransaction();

        User::query()
            ->where("level", User::LEVEL_MAHASISWA)
            ->delete();

        foreach (range(1, self::COUNT + 1) as $counter) {
            UserFactory::new()
                ->mahasiswa()
                ->create([
                    "username" => self::PREFIX . $counter,
                    "password" => Hash::make(self::PREFIX . $counter),
                ]);
        }

        DB::commit();
    }
}
