<?php

namespace App\Http\Controllers;

use App\Actions\PrepareCareMediaDownload;
use App\Models\CareJournal;
use App\Models\CareMedia;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CareMediaDownloadController extends Controller
{
    public function __invoke(
        CareJournal $careJournal,
        CareMedia $careMedia,
        PrepareCareMediaDownload $download,
    ): StreamedResponse {
        Gate::authorize('view', $careJournal);

        return $download->forOwner($careJournal, $careMedia);
    }
}
