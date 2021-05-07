<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoBidding extends Model
{
    use HasFactory;

    protected $fillable = ['user', 'maximum_amount'];
}
