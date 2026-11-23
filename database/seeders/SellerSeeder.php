<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;

class SellerSeeder extends Seeder
{
    /** 10 sellers: each of the 10 users gets a seller profile (sellers.user_id is the PK). */
    public function run(): void
    {
        User::orderBy('id')->get()->each(
            fn (User $user) => Seller::factory()->create(['user_id' => $user->id])
        );
    }
}
