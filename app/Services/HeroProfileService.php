<?php

namespace App\Services;

use App\Models\HeroProfile;

class HeroProfileService
{
    public function getProfile()
    {
        return HeroProfile::first();
    }
    public function updateProfile(array $data)
    {
        $profile = HeroProfile::first() ?? new HeroProfile();
        $profile->fill($data);
        $profile->save();
        return $profile;
    }
} 