<?php

namespace App\Http\Controllers;

use App\Http\Requests\AutoBiddingRequest;
use App\Http\Requests\BidRequest;
use App\Models\AuctionItem;
use App\Models\AutoBidding;
use App\Models\Bid;

class AuctionsController extends Controller
{
    public function index()
    {
        $items = AuctionItem::with('bids');
        if (request()->has('searchKey') && request()->searchKey !== '') {
            $items = $items->where('name', 'LIKE', "%" . strtolower(request()->searchKey) . "%")
                ->orWhere('description', 'LIKE', "%" . strtolower(request()->searchKey) . "%");
        }
        if (request()->has('orderByPrice') && request()->orderByPrice !== '') {
            $items =  $items->orderBy('price', request()->orderByPrice);
        }
        $items = $items->paginate(request()->limit ?? 10);
        return response()->json($items);
    }

    public function getAuctionItem($id)
    {
        $item = AuctionItem::with('bids')->find($id);
        return response()->json($item);
    }

    public function maximumBid($id)
    {
        $bid = Bid::where('auction_item_id', $id)->orderBy('amount', 'desc')->first();
        return response()->json($bid);
    }

    public function getAutoBidding($user)
    {
        $autoBidding = AutoBidding::where('user', $user)->first();
        return response()->json($autoBidding);
    }

    public function createAutoBidding(AutoBiddingRequest $request)
    {
        $autoBidding = AutoBidding::create(request()->all());
        return response()->json($autoBidding);
    }

    public function updateAutoBidding($id)
    {
        $autoBidding = AutoBidding::find($id);
        if ($autoBidding) {
            $autoBidding->update(request()->all());
        }
        return response()->json($autoBidding);
    }

    public function bidForItem(BidRequest $request)
    {
        // Get latest maximum bid
        $maximumBid = $this->maximumBid(request()->auction_item_id)->getData();
        // Check if bidder has the current highest bid
        if (count((array) $maximumBid) > 0 && request()->bidder === $maximumBid->bidder) {
            // Check if bid amount is greater than or equal to the highest bid amount, then return a 400 message that bidder is the highest bidder
            if (request()->amount >= $maximumBid->amount) {
                return response()->json(['message' => 'You are currently the highest bidder'], 400);
            }
            // Record bid and return json response
            $bid = Bid::updateOrCreate(['auction_item_id' => request()->auction_item_id, 'bidder' => request()->bidder], request()->all());
            return response()->json($bid);
        } else {
            // Check if bid amount is less than or equal to the highest bidded amount, then return a 400 message that bid amount has to be greater than the current highest bid amount
            if (count((array) $maximumBid) > 0 && request()->amount <= $maximumBid->amount) {
                return response()->json(['message' => 'The current highest bid amount is $' . $maximumBid->amount . '. You need to make a bid of at least $' . ($maximumBid->amount + 1)], 400);
            }
            // Record bid
            $bid = Bid::updateOrCreate(['auction_item_id' => request()->auction_item_id, 'bidder' => request()->bidder], request()->all());
            // Check if any user has set auto-bidding on item, then better the bid
            $this->runAutoBiddingBot($bid, $bid->auction_item_id, request()->bidder);
            return response()->json($bid);
        }
    }

    private function runAutoBiddingBot($bid, $auction_item_id, $bidder)
    {
        // Get all bids with item id and their corresponding auto bids
        $bidWithAutoBiddingBot = Bid::with('auto_bid')->where(['auction_item_id' => $auction_item_id, 'autobid' => 'yes'])->where('bidder', '!=', $bidder)->get();
        // Loop through users who have bidded for the item
        foreach ($bidWithAutoBiddingBot as $bidding) {
            if ($bidding->auto_bid) {
                // Check if user has enough auto-bid money to bid for +1 of the current bidder
                if ($bidding->auto_bid->maximum_amount > ($bid->amount + 1)) {
                    // Create bid record
                    Bid::updateOrCreate(['auction_item_id' => request()->auction_item_id, 'bidder' => $bidding->bidder], ['bidder' => $bidding->bidder, 'amount' => ($bid->amount + 1), 'auction_item_id' => $auction_item_id]);
                    // Update auto-bidding bot
                    $bidding->auto_bid->update(['maximum_amount' => ($bidding->auto_bid->maximum_amount - ($bid->amount + 1))]);
                }
            }
        }
    }
}
