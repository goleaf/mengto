<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumTopicType: string
{
    case Question = 'question';
    case Discussion = 'discussion';
    case Case = 'case';
    case Recommendation = 'recommendation';
    case CaseStudy = 'case-study';
    case Journal = 'journal';
    case Guide = 'guide';
    case UrgentRequest = 'urgent-request';
    case EmergencyAlert = 'emergency-alert';
    case LostAnimal = 'lost-animal';
    case FoundAnimal = 'found-animal';
    case Sighting = 'sighting';
    case AdoptionListing = 'adoption-listing';
    case FosterRequest = 'foster-request';
    case VolunteerRequest = 'volunteer-request';
    case ServiceReview = 'service-review';
    case ProductReview = 'product-review';
    case PlaceReview = 'place-review';
    case Event = 'event';
    case Comparison = 'comparison';
    case Poll = 'poll';
    case Checklist = 'checklist';
    case MarketplaceListing = 'marketplace-listing';
    case SupportRequest = 'support-request';
    case CorrectionRequest = 'correction-request';
    case IdentificationRequest = 'identification-request';
    case ResearchDiscussion = 'research-discussion';
    case OrganizationAnnouncement = 'organization-announcement';
    case ExpertQa = 'expert-qa';
    case Update = 'update';
    case Support = 'support';
    case LostPet = 'lost-pet';

    public function label(): string
    {
        return __("forum.topic_types.{$this->value}.name");
    }
}
