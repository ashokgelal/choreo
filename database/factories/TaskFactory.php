<?php

namespace Database\Factories;

use App\Models\TaskStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        $status = $this->faker->randomElement(TaskStatus::class);
        return [
            'description' => $this->faker->sentence,
            'status' => $status,
            'progress_started_at' => $status === 'in progress' ? $this->faker->dateTimeBetween('-1 year') : null,
        ];
    }
}
