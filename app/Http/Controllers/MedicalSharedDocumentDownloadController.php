<?php

namespace App\Http\Controllers;

use App\Actions\PrepareMedicalDocumentDownload;
use App\Actions\ResolveMedicalAccess;
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
        $grant = $resolve->handle($token, 'medical-access.document-opened');

        return $download->forGrant($grant, $medicalDocument);
    }
}
