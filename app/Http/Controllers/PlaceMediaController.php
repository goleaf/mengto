<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\PreparePlaceMediaResponse;
use App\Enums\PlaceMediaVariant;
use App\Models\Place;
use App\Models\PlaceMedia;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Symfony\Component\HttpFoundation\StreamedResponse;

final readonly class PlaceMediaController
{
    public function __construct(
        private AuthFactory $auth,
        private PreparePlaceMediaResponse $response,
    ) {}

    public function __invoke(
        Place $place,
        PlaceMedia $placeMedia,
        PlaceMediaVariant $variant,
    ): StreamedResponse {
        $actor = $this->auth->guard('web')->user();
        abort_unless($actor instanceof User, 404);

        return $this->response->handle($actor, $place, $placeMedia, $variant);
    }
}
