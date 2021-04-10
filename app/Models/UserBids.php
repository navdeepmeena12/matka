<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBids extends Model
{
    use HasFactory;


    protected $fillable = [
        'user_id',
        'bet_type',
        'bet_amount',
        'bet_date',
        'market_id',
        'market_session',
        'bet_rate',
        'bet_digit'
    ];


    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
