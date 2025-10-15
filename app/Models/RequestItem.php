<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'pickup_request_id',
        'waste_type',
        'bag_quantity',
        'note',
    ];

    public function photos()
    {
        return $this->hasMany(RequestItemPhoto::class);
    }
}