<?php

declare(strict_types=1);

namespace App\Enums;

enum PetProfilePermission: string
{
    case View = 'view';
    case EditBasics = 'edit-basics';
    case ManagePrivacy = 'manage-privacy';
    case Publish = 'publish';
    case ManageMedia = 'manage-media';
    case ManageSocial = 'manage-social';
    case ManageCare = 'manage-care';
    case ViewMedical = 'view-medical';
    case ManageDocuments = 'manage-documents';
    case ActivateLost = 'activate-lost';
    case ManageDevices = 'manage-devices';
    case InviteCaregiver = 'invite-caregiver';
    case ManageManagers = 'manage-managers';
    case ViewExactLocation = 'view-exact-location';
    case TransferOwnership = 'transfer-ownership';
    case DeleteProfile = 'delete-profile';
    case ChangePrimaryOwner = 'change-primary-owner';
    case ChangeMicrochip = 'change-microchip';
    case PublishAdoption = 'publish-adoption';
    case StartMarketplaceTransaction = 'start-marketplace-transaction';
    case ActivateMemorial = 'activate-memorial';

    public function label(): string
    {
        return __("pet_profiles.permissions.{$this->value}");
    }
}
