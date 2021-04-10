<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GaliBet extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $fillable = [
        'user_id',
        'bet_date',
        'bet_amount',
        'bet_digit',
        'gali_id',
        'is_win',
        'bet_rate'
    ];
}
