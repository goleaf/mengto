<?php

namespace App\Models;

use App\Enums\DeviceConnectionStatus;
use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use Database\Factories\SmartDeviceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartDevice extends Model
{
    /** @use HasFactory<SmartDeviceFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'owner_id', 'owner_key', 'slug', 'name', 'type', 'brand',
        'model', 'serial_number', 'image_url', 'public_zone_label',
        'private_location_label', 'privacy', 'status', 'connection_status',
        'operating_mode', 'connection_type', 'firmware_version',
        'battery_percent', 'signal_strength', 'last_seen_at',
        'last_synced_at', 'last_location_at', 'current_latitude',
        'current_longitude', 'location_accuracy_meters',
        'subscription_details', 'purchased_on', 'warranty_ends_on',
        'has_backup_power', 'supports_local_operation', 'requires_cloud',
        'is_medical_device', 'is_blocked', 'is_reported_stolen',
        'lock_version', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'owner_id', 'owner_key', 'slug', 'name', 'type', 'brand', 'model',
        'serial_number', 'image_url', 'public_zone_label',
        'private_location_label', 'privacy', 'status', 'connection_status',
        'operating_mode', 'connection_type', 'firmware_version',
        'battery_percent', 'signal_strength', 'last_seen_at',
        'last_synced_at', 'last_location_at', 'current_latitude',
        'current_longitude', 'location_accuracy_meters',
        'subscription_details', 'purchased_on', 'warranty_ends_on',
        'has_backup_power', 'supports_local_operation', 'requires_cloud',
        'is_medical_device', 'is_blocked', 'is_reported_stolen',
        'lock_version',
    ];

    protected $hidden = [
        'serial_number',
        'private_location_label',
        'current_latitude',
        'current_longitude',
        'subscription_details',
    ];

    protected $attributes = [
        'privacy' => 'private',
        'status' => 'active',
        'connection_status' => 'offline',
        'operating_mode' => 'normal',
        'has_backup_power' => false,
        'supports_local_operation' => false,
        'requires_cloud' => true,
        'is_medical_device' => false,
        'is_blocked' => false,
        'is_reported_stolen' => false,
        'lock_version' => 1,
    ];

    protected function casts(): array
    {
        return [
            'type' => DeviceType::class,
            'serial_number' => 'encrypted',
            'private_location_label' => 'encrypted',
            'status' => DeviceStatus::class,
            'connection_status' => DeviceConnectionStatus::class,
            'last_seen_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'last_location_at' => 'datetime',
            'current_latitude' => 'encrypted',
            'current_longitude' => 'encrypted',
            'location_accuracy_meters' => 'decimal:2',
            'subscription_details' => 'encrypted:array',
            'purchased_on' => 'date',
            'warranty_ends_on' => 'date',
            'has_backup_power' => 'boolean',
            'supports_local_operation' => 'boolean',
            'requires_cloud' => 'boolean',
            'is_medical_device' => 'boolean',
            'is_blocked' => 'boolean',
            'is_reported_stolen' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select(self::ROUTE_COLUMNS);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(DevicePetAssignment::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(DeviceReading::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(DeviceEvent::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class);
    }

    public function safeZones(): HasMany
    {
        return $this->hasMany(DeviceSafeZone::class);
    }

    public function automations(): HasMany
    {
        return $this->hasMany(DeviceAutomation::class);
    }

    public function accessGrants(): HasMany
    {
        return $this->hasMany(DeviceAccessGrant::class);
    }

    public function scopeForOwnerDirectory(Builder $query, string $ownerKey): Builder
    {
        return $query
            ->select([
                'id', 'owner_key', 'slug', 'name', 'type', 'brand', 'model',
                'serial_number', 'image_url', 'public_zone_label',
                'private_location_label', 'privacy', 'status', 'connection_status',
                'operating_mode', 'connection_type', 'firmware_version',
                'battery_percent', 'signal_strength', 'last_seen_at',
                'last_synced_at', 'last_location_at', 'current_latitude',
                'current_longitude',
                'location_accuracy_meters',
                'has_backup_power', 'supports_local_operation',
                'requires_cloud', 'is_medical_device', 'is_blocked',
                'is_reported_stolen', 'updated_at',
            ])
            ->where('owner_key', $ownerKey)
            ->whereNot('status', DeviceStatus::Retired->value);
    }

    public function isOwnedBy(string $actorKey): bool
    {
        return hash_equals($this->owner_key, $actorKey);
    }

    public function maskedSerialNumber(): ?string
    {
        $serial = preg_replace('/\s+/', '', (string) $this->serial_number);

        if ($serial === '') {
            return null;
        }

        return str_repeat('*', max(0, mb_strlen($serial) - 4)).mb_substr($serial, -4);
    }
}
