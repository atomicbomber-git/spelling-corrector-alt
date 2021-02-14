<?php

namespace Database\Factories;

use App\DokumenWord;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DokumenWordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DokumenWord::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "nama" => ucwords(implode(" ", $this->faker->words)),
            "user_id" => User::query()->inRandomOrder()->value("id") ?? User::factory()->create()->id,
        ];
    }
}
