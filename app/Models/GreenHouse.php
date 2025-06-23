<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GreenHouse extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image_url'
    ];
} 