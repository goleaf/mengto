<?php

declare(strict_types=1);

namespace App\Enums;

enum SocialRelationshipType: string
{
    case Follow = 'follow';
    case OwnerFriendship = 'owner-friendship';
    case PetFriendship = 'pet-friendship';
    case Acquaintance = 'acquaintance';
    case Family = 'family';
    case Professional = 'professional';
    case GroupContext = 'group-context';
    case EventContext = 'event-context';
    case TemporaryContact = 'temporary-contact';
    case CloseCircle = 'close-circle';
    case Restrict = 'restrict';
    case Mute = 'mute';
    case Block = 'block';

    public function direction(): SocialRelationshipDirection
    {
        return match ($this) {
            self::OwnerFriendship,
            self::PetFriendship,
            self::Acquaintance,
            self::Family => SocialRelationshipDirection::Symmetric,
            default => SocialRelationshipDirection::Directed,
        };
    }

    public function requiresAcceptance(): bool
    {
        return in_array($this, [
            self::OwnerFriendship,
            self::PetFriendship,
            self::Family,
            self::Professional,
            self::TemporaryContact,
        ], true);
    }

    public function label(): string
    {
        return __("social_relationships.relationship_types.{$this->value}");
    }
}
