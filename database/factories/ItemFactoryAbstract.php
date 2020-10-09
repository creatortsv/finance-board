<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

abstract class ItemFactoryAbstract extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'date' => $this->faker->dateTime(),
            'comment' => $this->faker->name,
            'quantity' => $this->faker->randomFloat(2, 0, 30000),
        ];
    }
}
