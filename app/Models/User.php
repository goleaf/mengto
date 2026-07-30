<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use Carbon\CarbonImmutable;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

#[Fillable([
    'actor_key',
    'name',
    'email',
    'password',
    'locale',
    'timezone',
    'status',
])]
#[Hidden(['password', 'remember_token'])]
/**
 * @property string $actor_key
 * @property Carbon|null $created_at
 * @property string $email
 * @property CarbonImmutable|null $email_verified_at
 * @property int $id
 * @property bool $is_admin
 * @property CarbonImmutable|null $last_login_at
 * @property string $locale
 * @property string $name
 * @property string $password
 * @property string|null $remember_token
 * @property UserStatus $status
 * @property string $timezone
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable implements MustVerifyEmailContract
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use MustVerifyEmail;
    use Notifiable;

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function isAdministrator(): bool
    {
        return $this->is_admin && $this->isActive();
    }

    /** @return HasMany<PetProfile, $this> */
    public function petProfiles(): HasMany
    {
        return $this->hasMany(PetProfile::class);
    }

    /** @return HasMany<UserDomainState, $this> */
    public function domainStates(): HasMany
    {
        return $this->hasMany(UserDomainState::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'immutable_datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'is_admin' => 'bool',
            'last_login_at' => 'immutable_datetime',
        ];
    }
}
