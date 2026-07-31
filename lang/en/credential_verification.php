<?php

declare(strict_types=1);

return [
    'type' => [
        'identity' => 'Government identity',
        'education' => 'Education record',
        'qualification' => 'Professional qualification',
        'license' => 'Professional license',
        'workplace' => 'Workplace relationship',
        'contact' => 'Professional contact',
        'organization-role' => 'Organization role',
        'organization-registration' => 'Organization registration',
        'rescue-organization' => 'Rescue organization',
        'shelter' => 'Animal shelter',
        'breeder' => 'Breeder registration',
        'organization-representative' => 'Organization representative',
    ],
    'profile_status' => [
        'unsubmitted' => 'Not submitted',
        'submitted' => 'Documents submitted',
        'in-review' => 'Under review',
        'more-information' => 'More information needed',
        'partially-verified' => 'Partially verified',
        'verified' => 'Verification current',
        'expiring' => 'Verification needs renewal',
        'suspended' => 'Verification paused',
        'rejected' => 'Documents not accepted',
    ],
    'status' => [
        'submitted' => 'Submitted',
        'in-review' => 'Under review',
        'verified' => 'Verified',
        'expiring' => 'Renewal due soon',
        'expired' => 'Expired',
        'rejected' => 'Rejected',
        'suspended' => 'Suspended',
        'revoked' => 'Revoked',
    ],
    'reason' => [
        'approved' => 'Credential approved after independent review.',
        'expired' => 'The credential has reached its stated expiration date.',
        'information-required' => 'Additional credential information is required.',
        'rejected' => 'Credential evidence was rejected after independent review.',
        'renewed' => 'A replacement credential was submitted for renewal.',
        'revoked' => 'Credential verification was revoked after review.',
        'suspended' => 'Credential verification was suspended pending review.',
    ],
    'validation' => [
        'appeal_exists' => 'An open appeal already exists for this credential.',
        'appeal_status' => 'This credential is not eligible for an appeal.',
        'conflict' => 'A reviewer cannot review their own professional profile.',
        'expiry' => 'A verified credential must have a future expiration date when an expiration date is provided.',
        'original_reviewer' => 'The original reviewer cannot be the only appeal reviewer.',
        'transition' => 'That credential status transition is not allowed.',
    ],
];
