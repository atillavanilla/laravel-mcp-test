<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Moses',   'email' => 'mosesmoradeke@gmail.com',   'password' => 'password'],
            ['name' => 'Bob',     'email' => 'bob@example.com',     'password' => 'password123'],
            ['name' => 'Charlie', 'email' => 'charlie@example.com', 'password' => 'password123'],
        ];

        foreach ($users as $userData) {
            User::factory()
                ->has(Post::factory()->count(10))
                ->create($userData);
        }
    }
}
