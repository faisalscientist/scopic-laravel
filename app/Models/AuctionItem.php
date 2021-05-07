<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuctionItem extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'deadline'];

    public function bids()
    {
        return $this->hasMany(Bid::class, 'auction_item_id', 'id')->orderBy('amount', 'desc');
    }
}
