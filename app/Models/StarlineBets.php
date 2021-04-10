<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StarlineBets extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $fillable = [
        'user_id',
        'bet_type',
        'bet_digit',
        'bet_amount',
        'market_id',
        'bet_date',
        'bet_rate'
    ];
}
