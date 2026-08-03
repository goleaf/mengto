<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PreparePetProfileMediaResponse;
use App\Models\PetProfile;
use App\Models\PetProfileMedia;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PetProfileMediaController extends Controller
{
    public function __invoke(
        PetProfile $petProfile,
        PetProfileMedia $petProfileMedia,
        PreparePetProfileMediaResponse $response,
    ): StreamedResponse {
        return $response->handle($petProfile, $petProfileMedia);
    }
}
