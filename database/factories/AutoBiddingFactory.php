<?php

namespace Database\Factories;

use App\Models\AutoBidding;
use Illuminate\Database\Eloquent\Factories\Factory;

class AutoBiddingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AutoBidding::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user' => $this->faker->randomElement(['user1', 'user2']),
            'maximum_amount' => $this->faker->randomFloat(2, 1000, 2000)
        ];
    }
}
