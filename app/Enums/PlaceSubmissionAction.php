<?php

declare(strict_types=1);

namespace App\Enums;

enum PlaceSubmissionAction: string
{
    case Submitted = 'submitted';
    case DuplicateReviewOpened = 'duplicate-review-opened';
    case ExistingPlaceConfirmed = 'existing-place-confirmed';
    case InformationRequested = 'information-requested';
    case InformationProvided = 'information-provided';
    case ContinuedAsDistinct = 'continued-as-distinct';
    case NewPlaceApproved = 'new-place-approved';
    case Published = 'published';
    case ExistingPlaceLinked = 'existing-place-linked';
    case PlacesMerged = 'places-merged';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
    case Reopened = 'reopened';
    case MergeRestored = 'merge-restored';
}
