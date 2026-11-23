<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    /** 10 coupons (the last one is inactive). */
    public function run(): void
    {
        Coupon::factory()->count(9)->create();
        Coupon::factory()->inactive()->create();
    }
}
