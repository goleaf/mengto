<?php

declare(strict_types=1);

namespace App\Enums;

enum ForumEventTeamRole: string
{
    case Owner = 'owner';
    case Administrator = 'administrator';
    case PrimaryOrganizer = 'primary_organizer';
    case CoOrganizer = 'co_organizer';
    case ScheduleManager = 'schedule_manager';
    case RegistrationManager = 'registration_manager';
    case TicketManager = 'ticket_manager';
    case PaymentReviewer = 'payment_reviewer';
    case CheckInOperator = 'check_in_operator';
    case SafetyLead = 'safety_lead';
    case WelfareOfficer = 'welfare_officer';
    case MedicalContact = 'medical_contact';
    case RouteLeader = 'route_leader';
    case Trainer = 'trainer';
    case Speaker = 'speaker';
    case SessionModerator = 'session_moderator';
    case Judge = 'judge';
    case Scorekeeper = 'scorekeeper';
    case VendorCoordinator = 'vendor_coordinator';
    case VolunteerCoordinator = 'volunteer_coordinator';
    case MediaCoordinator = 'media_coordinator';
    case Auditor = 'auditor';

    public function label(): string
    {
        return __('forum_events.team_roles.'.$this->value);
    }
}
