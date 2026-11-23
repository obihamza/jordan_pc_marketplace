<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * 21 users: 1 admin + 10 future sellers + 10 buyers.
     * (The role is derived: sellers are the users that get a `sellers` row in
     * SellerSeeder; buyers are everyone else.) Every password is "password".
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@masterpiece.test',
        ]);

        User::factory()->count(20)->create();
    }
}
