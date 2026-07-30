<?php

namespace App\Http\Controllers;

use App\Actions\PrepareMedicalDocumentDownload;
use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MedicalDocumentDownloadController extends Controller
{
    public function __invoke(
        MedicalRecord $medicalRecord,
        MedicalDocument $medicalDocument,
        PrepareMedicalDocumentDownload $download,
    ): StreamedResponse {
        Gate::authorize('view', $medicalRecord);

        return $download->forOwner($medicalRecord, $medicalDocument);
    }
}
