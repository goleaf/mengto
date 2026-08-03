<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventType: string
{
    case SocialMeetup = 'social_meetup';
    case GroupWalk = 'group_walk';
    case TrainingSession = 'training_session';
    case Workshop = 'workshop';
    case Conference = 'conference';
    case Webinar = 'webinar';
    case Exhibition = 'exhibition';
    case Competition = 'competition';
    case AdoptionDay = 'adoption_day';
    case ShelterOpenDay = 'shelter_open_day';
    case Fundraiser = 'fundraiser';
    case VolunteerShift = 'volunteer_shift';
    case OrganizationMeeting = 'organization_meeting';
    case MarketplaceFair = 'marketplace_fair';
    case ControlledAnimalIntroduction = 'controlled_animal_introduction';
    case Custom = 'custom';

    // Preserved route and data values from the original meetup implementation.
    case Walk = 'walk';
    case Training = 'training';
    case Show = 'show';
    case Adoption = 'adoption';
    case Volunteer = 'volunteer';
    case Celebration = 'celebration';
    case OnlineSession = 'online_session';
    case ClubMeetup = 'club_meetup';
    case Other = 'other';

    public function label(): string
    {
        return __('forum_events.types.'.$this->value);
    }

    public function category(): string
    {
        return match ($this) {
            self::SocialMeetup, self::ClubMeetup, self::Celebration => 'community',
            self::GroupWalk, self::Walk => 'outdoor',
            self::TrainingSession, self::Training, self::Workshop => 'education',
            self::Conference, self::Webinar, self::OnlineSession => 'professional',
            self::Exhibition, self::Show => 'exhibition',
            self::Competition => 'competition',
            self::AdoptionDay, self::Adoption, self::ShelterOpenDay => 'shelter',
            self::Fundraiser => 'fundraising',
            self::VolunteerShift, self::Volunteer => 'volunteer',
            self::OrganizationMeeting => 'organization',
            self::MarketplaceFair => 'marketplace',
            self::ControlledAnimalIntroduction => 'welfare',
            self::Custom, self::Other => 'custom',
        };
    }

    public function supportsSessions(): bool
    {
        return in_array($this, [
            self::TrainingSession,
            self::Training,
            self::Workshop,
            self::Conference,
            self::Webinar,
            self::OnlineSession,
            self::Exhibition,
            self::Show,
            self::Competition,
        ], true);
    }

    public function supportsCompetition(): bool
    {
        return $this === self::Competition;
    }

    public function supportsOnline(): bool
    {
        return ! in_array($this, [
            self::GroupWalk,
            self::Walk,
            self::ControlledAnimalIntroduction,
        ], true);
    }

    public function supportsRecurrence(): bool
    {
        return ! in_array($this, [self::Competition, self::AdoptionDay], true);
    }

    public function supportsTicketing(): bool
    {
        return ! in_array($this, [
            self::ControlledAnimalIntroduction,
            self::OrganizationMeeting,
        ], true);
    }

    public function requiresSafetyReview(): bool
    {
        return in_array($this, [
            self::GroupWalk,
            self::Walk,
            self::TrainingSession,
            self::Training,
            self::Exhibition,
            self::Show,
            self::Competition,
            self::AdoptionDay,
            self::Adoption,
            self::ShelterOpenDay,
            self::ControlledAnimalIntroduction,
        ], true);
    }
}
