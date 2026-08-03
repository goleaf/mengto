<?php

declare(strict_types=1);

namespace App\Enums;

enum OrganizationRole: string
{
    case Owner = 'owner';
    case Administrator = 'administrator';
    case EventManager = 'event_manager';
    case FinanceManager = 'finance_manager';
    case SafetyLead = 'safety_lead';
    case MarketplaceManager = 'marketplace_manager';
    case ShelterCoordinator = 'shelter_coordinator';
    case Member = 'member';
    case Auditor = 'auditor';

    public function label(): string
    {
        return __('organizations.roles.'.$this->value);
    }

    public function canManageMembers(): bool
    {
        return in_array($this, [self::Owner, self::Administrator], true);
    }

    public function canManageEvents(): bool
    {
        return in_array($this, [self::Owner, self::Administrator, self::EventManager], true);
    }

    public function canManageFinance(): bool
    {
        return in_array($this, [self::Owner, self::Administrator, self::FinanceManager], true);
    }

    public function canManageSafety(): bool
    {
        return in_array($this, [self::Owner, self::Administrator, self::SafetyLead], true);
    }

    public function canManageMarketplace(): bool
    {
        return in_array($this, [self::Owner, self::Administrator, self::MarketplaceManager], true);
    }

    public function canManageShelter(): bool
    {
        return in_array($this, [self::Owner, self::Administrator, self::ShelterCoordinator], true);
    }

    public function canViewAudit(): bool
    {
        return in_array($this, [self::Owner, self::Administrator, self::Auditor], true);
    }

    /** @return list<self> */
    public static function assignableCases(): array
    {
        return array_values(array_filter(
            self::cases(),
            static fn (self $role): bool => $role !== self::Owner,
        ));
    }
}
