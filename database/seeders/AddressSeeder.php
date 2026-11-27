<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::buyersOnly()->orderBy('id')->pluck('id')->all();

        Address::factory()
            ->count(10)
            ->state(new Sequence(fn (Sequence $s) => ['user_id' => $userIds[$s->index % count($userIds)]]))
            ->create();
    }
}
