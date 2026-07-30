<?php

namespace App\Models;

use Database\Factories\DeviceAccessGrantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceAccessGrant extends Model
{
    /** @use HasFactory<DeviceAccessGrantFactory> */
    use HasFactory;

    protected $fillable = [
        'smart_device_id', 'granted_by_key', 'recipient_key',
        'recipient_name', 'recipient_role', 'label', 'token_hash',
        'permissions', 'allow_location', 'allow_camera', 'allow_commands',
        'allow_audio', 'max_views', 'views_used', 'starts_at', 'expires_at',
        'last_opened_at', 'revoked_at',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'allow_location' => 'boolean',
            'allow_camera' => 'boolean',
            'allow_commands' => 'boolean',
            'allow_audio' => 'boolean',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select([
                'id', 'smart_device_id', 'granted_by_key', 'recipient_key',
                'recipient_name', 'recipient_role', 'label', 'token_hash',
                'permissions', 'allow_location', 'allow_camera',
                'allow_commands', 'allow_audio', 'max_views', 'views_used',
                'starts_at', 'expires_at', 'last_opened_at', 'revoked_at',
                'created_at', 'updated_at',
            ]);
    }

    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>', now())
            ->whereColumn('views_used', '<', 'max_views');
    }

    public function canBeOpened(): bool
    {
        return $this->revoked_at === null
            && $this->starts_at?->isPast()
            && $this->expires_at?->isFuture()
            && $this->views_used < $this->max_views;
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? [], true)
            && $this->revoked_at === null
            && $this->starts_at?->isPast()
            && $this->expires_at?->isFuture();
    }
}
