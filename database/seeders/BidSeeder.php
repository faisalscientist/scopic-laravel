<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class BidSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Bid::factory(20)->create();
    }
}
