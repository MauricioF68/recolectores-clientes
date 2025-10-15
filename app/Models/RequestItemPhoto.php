<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestItemPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_item_id',
        'path',
    ];

    /**
     * Una foto pertenece a un item de recojo.
     */
    public function requestItem()
    {
        return $this->belongsTo(RequestItem::class);
    }
}