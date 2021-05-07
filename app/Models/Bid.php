<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    use HasFactory;

    protected $fillable = ['bidder', 'amount', 'auction_item_id', 'autobid'];

    public function auto_bid()
    {
        return $this->hasOne(AutoBidding::class, 'user', 'bidder');
    }
}
