<?php

namespace Tests\Unit;

use App\Models\AuctionItem;
use App\Models\AutoBidding;
use App\Models\Bid;
use Carbon\Carbon;
use Database\Seeders\AuctionItemSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class AuctionTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Artisan::call('migrate:fresh');
    }

    /** @test */
    public function get_the_initial_state_of_list_of_all_auction_items()
    {
        $items = AuctionItem::all();
        $this->assertCount(0, $items);
    }

    /** @test */
    public function add_auction_items_using_model_factory_and_get_list_of_all_items()
    {
        $this->seed(AuctionItemSeeder::class);
        $items = AuctionItem::all();
        $this->assertCount(20, $items);
    }

    /** @test */
    public function get_one_auction_item_and_return_the_details()
    {
        $actionItem = AuctionItem::create(['name' => 'Name',  'price' => 100, 'description' => 'Description', 'deadline' => Carbon::now()->addDays(90)]);
        $item = AuctionItem::where('id', $actionItem->id)->first();
        $this->assertEquals($item->name, 'Name');
        $this->assertEquals($item->price, 100);
        $this->assertEquals($item->description, 'Description');
        $this->assertEquals($item->deadline, Carbon::now()->addDays(90));
    }

    /** @test */
    public function create_auto_bidding_and_return_details()
    {
        AutoBidding::create(['user' => 'user1', 'maximum_amount' => 100]);
        $item = AutoBidding::first();
        $this->assertEquals($item->user, 'user1');
        $this->assertEquals($item->maximum_amount, 100.00);
    }

    /** @test */
    public function create_bid_and_return_details()
    {
        $auction_item = AuctionItem::create(['name' => 'Name', 'description' => 'Description', 'price' => 100, 'deadline' => Carbon::now()->addDays(90)]);
        Bid::create(['bidder' => 'user1', 'amount' => 1000, 'auction_item_id' => $auction_item->id]);
        $bid = Bid::first();
        $this->assertEquals($bid->bidder, 'user1');
        $this->assertEquals($bid->amount, 1000);
        $this->assertEquals($bid->auction_item_id, $auction_item->id);
    }
}
