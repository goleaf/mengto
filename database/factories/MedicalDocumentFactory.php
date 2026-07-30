<?php

namespace Database\Factories;

use App\Enums\MedicalSourceType;
use App\Enums\MedicalVerificationStatus;
use App\Models\MedicalDocument;
use App\Models\MedicalRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedicalDocument>
 */
class MedicalDocumentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'type' => 'visit-summary',
            'title' => 'Veterinary visit summary',
            'file_path' => 'medical-records/demo/visit-summary.pdf',
            'original_name' => 'visit-summary.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 24576,
            'source_type' => MedicalSourceType::Clinic,
            'source_name' => 'Paws 24 Veterinary Center',
            'verification_status' => MedicalVerificationStatus::OrganizationIssued,
            'expires_on' => null,
            'uploaded_by_key' => 'paws-24-vet',
            'download_count' => 0,
        ];
    }
}
