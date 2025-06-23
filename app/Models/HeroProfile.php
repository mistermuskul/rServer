<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroProfile extends Model
{
    protected $fillable = [
        'avatar', 'full_name', 'birth_date', 'city', 'education',
        'specialization', 'stack', 'experience_start', 'bio'
    ];
} 