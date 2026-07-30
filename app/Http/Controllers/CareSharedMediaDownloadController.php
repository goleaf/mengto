<?php

namespace App\Http\Controllers;

use App\Actions\PrepareCareMediaDownload;
use App\Actions\ResolveCareAccess;
use App\Models\CareMedia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CareSharedMediaDownloadController extends Controller
{
    public function __invoke(
        string $token,
        CareMedia $careMedia,
        ResolveCareAccess $resolve,
        PrepareCareMediaDownload $download,
    ): StreamedResponse {
        $grant = $resolve->handle($token, 'care-access.media-opened');

        return $download->forGrant($grant, $careMedia);
    }
}
