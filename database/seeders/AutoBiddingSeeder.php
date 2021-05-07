<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AutoBiddingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\AutoBidding::factory(20)->create();
    }
}
