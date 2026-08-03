<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationRestrictionCapability: string
{
    case CreateEvents = 'create_events';
    case PublishEvents = 'publish_events';
    case AcceptRegistrations = 'accept_registrations';
    case AcceptPayments = 'accept_payments';
    case AccessParticipantData = 'access_participant_data';
    case RunCheckIn = 'run_check_in';
    case EnterResults = 'enter_results';
    case CreateInvitations = 'create_invitations';

    public function label(): string
    {
        return __('organizations.restriction_capabilities.'.$this->value);
    }

    /** @return list<self> */
    public static function operationalEventCapabilities(): array
    {
        return [
            self::CreateEvents,
            self::PublishEvents,
            self::AcceptRegistrations,
            self::AcceptPayments,
            self::AccessParticipantData,
            self::RunCheckIn,
            self::EnterResults,
            self::CreateInvitations,
        ];
    }
}
