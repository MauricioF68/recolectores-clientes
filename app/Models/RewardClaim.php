<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RewardClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_id',
        'points_spent',
        'status',
        'shipping_address',
        'shipping_latitude',
        'shipping_longitude',
        'shipping_department', 
        'shipping_province',   
        'shipping_district',
    ];
}
