<?php

namespace App\Http\Controllers;

use App\Actions\PrepareCareMediaDownload;
use App\Actions\ResolveCareAccess;
use App\Models\CareAccessGrant;
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
        return $resolve->execute(
            $token,
            'care-access.media-opened',
            static fn (CareAccessGrant $grant): StreamedResponse => $download->forGrant(
                $grant,
                $careMedia,
            ),
        );
    }
}
