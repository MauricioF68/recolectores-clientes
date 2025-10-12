<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectorMasterList extends Model
{
    use HasFactory;

    protected $table = 'collectors_master_list';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'second_last_name',
        'dni',
        'email',
        'department',
        'province',
        'district',
        'address',
        'latitude',
        'longitude',
    ];
}