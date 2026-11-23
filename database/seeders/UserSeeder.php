<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /** 10 users: 1 admin + 9 regular. Every account's password is "password". */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@masterpiece.test',
        ]);

        User::factory()->count(9)->create();
    }
}
