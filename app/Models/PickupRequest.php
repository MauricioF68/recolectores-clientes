<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'address',
        'latitude',
        'longitude',
        'notes',
        'status',
        'user_id',
        'collector_id',
        'department', // <-- Añadido
        'province',   // <-- Añadido
        'district',
    ];

    public function items()
    {
        return $this->hasMany(RequestItem::class);
    }
}