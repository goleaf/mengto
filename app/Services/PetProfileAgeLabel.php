<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PetProfile;

final class PetProfileAgeLabel
{
    public function for(PetProfile $profile): ?string
    {
        if ($profile->birth_date === null) {
            return null;
        }

        $years = (int) floor($profile->birth_date->diffInYears(now()));
        $prefix = $profile->birth_date_precision === 'exact'
            ? ''
            : __('pet_profiles.public.approximately').' ';

        return $prefix.trans_choice('pet_profiles.public.age_years', $years, [
            'count' => $years,
        ]);
    }
}
