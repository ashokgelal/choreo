<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'description' => $this->faker->sentence,
            'status' => $this->faker->randomElement(['todo', 'in_progress', 'done']),
            'progress_started_at' => $this->faker->boolean(50) ? $this->faker->dateTimeBetween('-1 year') : null,
        ];
    }
}
