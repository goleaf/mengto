<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PortalMediaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PortalMediaController extends Controller
{
    public function __invoke(string $path, PortalMediaResponse $mediaResponse): StreamedResponse
    {
        return $mediaResponse->inline($path);
    }
}
