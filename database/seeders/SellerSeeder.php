<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;

class SellerSeeder extends Seeder
{
    public function run(): void
    {
        User::where('is_admin', false)->orderBy('id')->take(10)->get()->each(
            fn (User $user) => Seller::factory()->create(['user_id' => $user->id])
        );
    }
}
