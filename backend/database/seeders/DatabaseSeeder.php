<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email_encrypted' => 'test@example.com',
            'email_hash' => hash('sha256', 'test@example.com'),
        ]);

        $this->call(MockDataSeeder::class);
    }
}
