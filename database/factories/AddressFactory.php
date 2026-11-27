<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    public function definition(): array
    {
        $city = fake()->randomElement(['Amman', 'Irbid', 'Zarqa', 'Aqaba', 'Salt', 'Madaba', 'Jerash']);

        return [
            'user_id' => User::factory(),
            'address_line' => fake()->buildingNumber().' '.fake()->streetName().', '.$city,
            'postal_code' => fake()->numerify('1####'),
            'country' => 'Jordan',
        ];
    }
}
