<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceVerificationMethod: string
{
    case DomainEmail = 'domain_email';
    case PublishedPhone = 'published_phone';
    case PostalCode = 'postal_code';
    case OrganizationDocument = 'organization_document';
    case ManualReview = 'manual_review';

    public function label(): string
    {
        return __('places.management.verification_methods.'.$this->value);
    }
}
