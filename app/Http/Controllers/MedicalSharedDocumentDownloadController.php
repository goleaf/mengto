<?php

namespace App\Http\Controllers;

use App\Actions\PrepareMedicalDocumentDownload;
use App\Actions\ResolveMedicalAccess;
use App\Models\MedicalAccessGrant;
use App\Models\MedicalDocument;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MedicalSharedDocumentDownloadController extends Controller
{
    public function __invoke(
        string $token,
        MedicalDocument $medicalDocument,
        ResolveMedicalAccess $resolve,
        PrepareMedicalDocumentDownload $download,
    ): StreamedResponse {
        return $resolve->execute(
            $token,
            'medical-access.document-opened',
            static fn (MedicalAccessGrant $grant): StreamedResponse => $download->forGrant(
                $grant,
                $medicalDocument,
            ),
        );
    }
}
