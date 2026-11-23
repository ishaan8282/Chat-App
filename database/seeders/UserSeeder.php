<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = [
            ['name' => 'Alice Johnson', 'email' => 'alice@example.com'],
            ['name' => 'Bob Smith', 'email' => 'bob@example.com'],
            ['name' => 'Charlie Davis', 'email' => 'charlie@example.com'],
            ['name' => 'Diana Prince', 'email' => 'diana@example.com'],
            ['name' => 'Ethan Hunt', 'email' => 'ethan@example.com'],
            ['name' => 'Fiona Gallagher', 'email' => 'fiona@example.com'],
            ['name' => 'George Clark', 'email' => 'george@example.com'],
            ['name' => 'Hannah Abbott', 'email' => 'hannah@example.com'],
            ['name' => 'Ian Wright', 'email' => 'ian@example.com'],
            ['name' => 'Julia Roberts', 'email' => 'julia@example.com'],
        ];

        foreach ($contacts as $contact) {
            User::firstOrCreate(
                ['email' => $contact['email']],
                [
                    'name' => $contact['name'],
                    'password' => Hash::make('developer'),
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
