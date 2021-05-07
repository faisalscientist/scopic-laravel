<?php

namespace Database\Factories;

use App\Models\AuctionItem;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuctionItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AuctionItem::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->text(10),
            'description' => $this->faker->text(500),
            'price' => $this->faker->randomFloat(2, 100, 1000),
            'deadline' => Carbon::now()->addDays(90),
        ];
    }
}
