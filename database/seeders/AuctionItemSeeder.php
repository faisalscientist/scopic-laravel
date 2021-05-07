<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AuctionItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\AuctionItem::factory(20)->create();
    }
}
