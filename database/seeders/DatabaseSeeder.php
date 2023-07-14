<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'email' => 'demo@example.com'
        ]);
         $this->call([
             TaskSeeder::class,
        ]);
    }
}
