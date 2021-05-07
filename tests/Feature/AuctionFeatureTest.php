<?php

namespace Tests\Feature;

use App\Models\AuctionItem;
use App\Models\AutoBidding;
use App\Models\Bid;
use Database\Seeders\AuctionItemSeeder;
use Database\Seeders\AutoBiddingSeeder;
use Database\Seeders\BidSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AuctionFeatureTest extends TestCase
{
  use RefreshDatabase;
  private $items;
  private $bids;
  private $bid;
  private $auto_bid;
  public function setUp(): void
  {
    parent::setUp();
    Artisan::call('migrate:fresh');
    $this->seed(AuctionItemSeeder::class);
    $this->items = AuctionItem::all();
    $this->seed(BidSeeder::class);
    $this->bids = Bid::orderBy('amount', 'desc')->get();
  }

  /** @test */
  public function get_the_list_of_all_auctions_and_check_200_json_is_returned()
  {
    $response = $this->get('/api/auction-items');
    $response->assertStatus(200)
      ->assertJson(
        function (AssertableJson $json) {
          $json->has('data', 10)
            ->has(
              'data.0',
              function ($json) {
                $json->where('name', $this->items[0]->name)
                  ->where('description', $this->items[0]->description)
                  ->where('deadline', $this->items[0]->deadline)
                  ->etc();
              }
            )->etc();
        }
      );
  }

  /** @test */
  public function get_one_auction_item_and_check_200_json_is_returned()
  {
    $response = $this->get('/api/auction-items/' . $this->items[0]->id);
    $response->assertStatus(200)
      ->assertJson(
        function (AssertableJson $json) {
          $json->has(8)
            ->where('name', $this->items[0]->name)
            ->where('description', $this->items[0]->description)
            ->where('deadline', $this->items[0]->deadline)
            ->etc();
        }
      );
  }

  /** @test */
  public function get_maximum_bid_and_check_if_200_json_is_returned()
  {
    $response = $this->get('/api/maximum-bid/' . $this->bids[0]->auction_item_id);
    $response->assertStatus(200)
      ->assertJson(
        function (AssertableJson $json) {
          $json->has(7)
            ->where('auction_item_id', $this->bids[0]->auction_item_id)
            ->where('bidder', $this->bids[0]->bidder)
            ->where('amount', $this->bids[0]->amount)
            ->etc();
        }
      );
  }

  /** @test */
  public function create_auto_bidding_amount_and_check_if_200_json_is_returned()
  {
    $response = $this->post('/api/auto-bidding', ['user' => 'user1', 'maximum_amount' => 1000.00]);
    $response->assertStatus(200)
      ->assertJson(
        function (AssertableJson $json) {
          $json->has(5)
            ->where('maximum_amount', 1000)
            ->where('user', 'user1')
            ->etc();
        }
      );
  }

  /** @test */
  public function update_auto_bidding_amount_and_check_if_200_json_is_returned()
  {
    $this->seed(AutoBiddingSeeder::class);
    $autoBiddingConfig = AutoBidding::where('user', 'user1')->first();
    $response = $this->put('/api/auto-bidding/' . $autoBiddingConfig->id, ['user' => 'user1', 'maximum_amount' => 10000.00]);
    $response->assertStatus(200)
      ->assertJson(
        function (AssertableJson $json) {
          $json->has(5)
            ->where('maximum_amount', 10000)
            ->where('user', 'user1')
            ->etc();
        }
      );
  }

  /** @test */
  public function bid_for_item_and_check_for_auto_bidding_then_return_the_bid_entered()
  {
    Bid::create(['bidder' => 'user1', 'amount' => 10000.00, 'autobid' => 'no', 'auction_item_id' => $this->items[0]->id]);
    // Make a bid as user1 (could be less or more than highest bid. Expected the system to inform user that they are the highest bidder)
    $response = $this->post('/api/bid', ['bidder' => 'user1', 'autobid' => 'no', 'amount' => 10001.00, 'auction_item_id' => $this->items[0]->id]);
    $response->assertStatus(400)
      ->assertJson(
        function (AssertableJson $json) {
          $json->has(1)
            ->where('message', 'You are currently the highest bidder')
            ->etc();
        }
      );
    // Make multiple bid as user1
    $response = $this->post('/api/bid', ['bidder' => 'user1', 'autobid' => 'no', 'amount' => 9999, 'auction_item_id' => $this->items[0]->id]);
    $this->bid = Bid::where('auction_item_id', $this->items[0]->id)->first();
    $response->assertStatus(200)
      ->assertJson(
        function (AssertableJson $json) {
          $json->has(7)
            ->where('auction_item_id', $this->bid->auction_item_id)
            ->where('bidder', $this->bid->bidder)
            ->where('amount', 9999)
            ->etc();
        }
      );
    // Make a lower bid as user2. Expect 400 message about bid being smaller than previous bid
    $response = $this->post('/api/bid', ['bidder' => 'user2', 'amount' => 100, 'autobid' => 'no', 'auction_item_id' => $this->items[0]->id]);
    $response->assertStatus(400)
      ->assertJson(
        function (AssertableJson $json) {
          $json->has(1)
            ->where('message', 'The current highest bid amount is $9999. You need to make a bid of at least $10000')
            ->etc();
        }
      );
    // Make a higher bid as user2.
    AutoBidding::create(['user' => 'user1', 'maximum_amount' => 20000]);
    $response = $this->post('/api/bid', ['bidder' => 'user2', 'amount' => 10500, 'autobid' => 'yes', 'auction_item_id' => $this->items[0]->id]);
    $response->assertStatus(200)
      ->assertJson(
        function (AssertableJson $json) {
          $json->has(7)
            ->where('auction_item_id', $this->items[0]->id)
            ->where('bidder', 'user2')
            ->where('amount', 10500)
            ->etc();
        }
      );
    // Check to see if there's been an improved bid by another users auto-bidding bot
    $latestBid = Bid::where('auction_item_id', $this->items[0]->id)->orderBy('amount', 'desc')->first();
    $response->getData()->autobid === 'yes' ?? $this->assertGreaterThan($response->getData()->amount, $latestBid->amount);
  }
}
