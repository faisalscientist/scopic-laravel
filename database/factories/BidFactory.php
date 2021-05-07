<?php

namespace Database\Factories;

use App\Models\AuctionItem;
use App\Models\Bid;
use Illuminate\Database\Eloquent\Factories\Factory;

class BidFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Bid::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'auction_item_id' => AuctionItem::factory(),
            'bidder' => $this->faker->randomElement(['user1', 'user2']),
            'autobid' => 'yes',
            'amount' => $this->faker->randomFloat(2, 1000, 9000)
        ];
    }
}
