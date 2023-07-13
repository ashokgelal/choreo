<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(['todo', 'in progress', 'done']);
        return [
            'description' => $this->faker->sentence,
            'status' => $status,
            'progress_started_at' => $status === 'in progress' ? $this->faker->dateTimeBetween('-1 year') : null,
        ];
    }
}
