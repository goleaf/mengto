<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceConnectionStatus;
use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use Database\Factories\SmartDeviceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property-read Collection<int, DeviceAccessGrant> $accessGrants
 * @property-read Collection<int, DevicePetAssignment> $assignments
 * @property-read Collection<int, DeviceAutomation> $automations
 * @property int|null $battery_percent
 * @property string|null $brand
 * @property-read Collection<int, DeviceCommand> $commands
 * @property DeviceConnectionStatus $connection_status
 * @property string|null $connection_type
 * @property Carbon|null $created_at
 * @property string|null $current_latitude
 * @property string|null $current_longitude
 * @property int $enabled_automations_count
 * @property-read Collection<int, DeviceEvent> $events
 * @property string|null $firmware_version
 * @property bool $has_backup_power
 * @property int $id
 * @property string|null $image_url
 * @property bool $is_blocked
 * @property bool $is_medical_device
 * @property bool $is_reported_stolen
 * @property-read Collection<int, DeviceLifecycleRecord> $lifecycleRecords
 * @property Carbon|null $last_location_at
 * @property Carbon|null $last_seen_at
 * @property Carbon|null $last_synced_at
 * @property numeric-string|null $location_accuracy_meters
 * @property int $location_retention_days
 * @property int $lock_version
 * @property int $media_retention_days
 * @property string|null $model
 * @property string $name
 * @property int $open_events_count
 * @property string $operating_mode
 * @property int|null $owner_id
 * @property string $owner_key
 * @property string $privacy
 * @property string|null $private_location_label
 * @property string $provider_status
 * @property string|null $public_zone_label
 * @property Carbon|null $purchased_on
 * @property-read Collection<int, DeviceReading> $readings
 * @property bool $requires_cloud
 * @property-read Collection<int, DeviceSafeZone> $safeZones
 * @property string|null $serial_number
 * @property array<string, bool>|null $safety_state
 * @property Carbon|null $safety_state_recorded_at
 * @property int|null $signal_strength
 * @property string $slug
 * @property DeviceStatus $status
 * @property array<array-key, mixed>|null $subscription_details
 * @property bool $supports_local_operation
 * @property int $telemetry_retention_days
 * @property DeviceType $type
 * @property Carbon|null $updated_at
 * @property int $urgent_events_count
 * @property Carbon|null $warranty_ends_on
 */
class SmartDevice extends Model
{
    /** @use HasFactory<SmartDeviceFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'owner_id', 'owner_key', 'slug', 'name', 'type', 'brand',
        'model', 'serial_number', 'image_url', 'public_zone_label',
        'private_location_label', 'privacy', 'status', 'connection_status',
        'operating_mode', 'connection_type', 'provider_status',
        'firmware_version',
        'battery_percent', 'signal_strength', 'last_seen_at',
        'last_synced_at', 'last_location_at', 'current_latitude',
        'current_longitude', 'location_accuracy_meters',
        'location_retention_days', 'media_retention_days',
        'telemetry_retention_days', 'safety_state', 'safety_state_recorded_at',
        'subscription_details', 'purchased_on', 'warranty_ends_on',
        'has_backup_power', 'supports_local_operation', 'requires_cloud',
        'is_medical_device', 'is_blocked', 'is_reported_stolen',
        'lock_version', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'owner_id', 'owner_key', 'slug', 'name', 'type', 'brand', 'model',
        'serial_number', 'image_url', 'public_zone_label',
        'private_location_label', 'privacy', 'status', 'connection_status',
        'operating_mode', 'connection_type', 'provider_status',
        'firmware_version',
        'battery_percent', 'signal_strength', 'last_seen_at',
        'last_synced_at', 'last_location_at', 'current_latitude',
        'current_longitude', 'location_accuracy_meters',
        'location_retention_days', 'media_retention_days',
        'telemetry_retention_days', 'safety_state', 'safety_state_recorded_at',
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
        'safety_state',
    ];

    protected $attributes = [
        'privacy' => 'private',
        'status' => 'active',
        'connection_status' => 'offline',
        'operating_mode' => 'normal',
        'provider_status' => 'not-configured',
        'location_retention_days' => 30,
        'media_retention_days' => 7,
        'telemetry_retention_days' => 365,
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
            'location_retention_days' => 'integer',
            'media_retention_days' => 'integer',
            'telemetry_retention_days' => 'integer',
            'safety_state' => 'encrypted:array',
            'safety_state_recorded_at' => 'immutable_datetime',
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

    /** @return BelongsTo<User, $this> */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** @return HasMany<\App\Models\DevicePetAssignment, $this>*/
    public function assignments(): HasMany
    {
        return $this->hasMany(DevicePetAssignment::class);
    }

    /** @return HasMany<\App\Models\DeviceReading, $this>*/
    public function readings(): HasMany
    {
        return $this->hasMany(DeviceReading::class);
    }

    /** @return HasMany<\App\Models\DeviceEvent, $this>*/
    public function events(): HasMany
    {
        return $this->hasMany(DeviceEvent::class);
    }

    /** @return HasMany<\App\Models\DeviceCommand, $this>*/
    public function commands(): HasMany
    {
        return $this->hasMany(DeviceCommand::class);
    }

    /** @return HasMany<\App\Models\DeviceSafeZone, $this>*/
    public function safeZones(): HasMany
    {
        return $this->hasMany(DeviceSafeZone::class);
    }

    /** @return HasMany<\App\Models\DeviceAutomation, $this>*/
    public function automations(): HasMany
    {
        return $this->hasMany(DeviceAutomation::class);
    }

    /** @return HasMany<\App\Models\DeviceAccessGrant, $this>*/
    public function accessGrants(): HasMany
    {
        return $this->hasMany(DeviceAccessGrant::class);
    }

    /** @return HasMany<DeviceAutomationRun, $this> */
    public function automationRuns(): HasMany
    {
        return $this->hasMany(DeviceAutomationRun::class);
    }

    /** @return HasMany<DeviceLifecycleRecord, $this> */
    public function lifecycleRecords(): HasMany
    {
        return $this->hasMany(DeviceLifecycleRecord::class);
    }

    public function scopeForOwnerDirectory(Builder $query, string $ownerKey): Builder
    {
        return $query
            ->select([
                'id', 'owner_key', 'slug', 'name', 'type', 'brand', 'model',
                'serial_number', 'image_url', 'public_zone_label',
                'private_location_label', 'privacy', 'status', 'connection_status',
                'operating_mode', 'connection_type', 'firmware_version',
                'provider_status',
                'battery_percent', 'signal_strength', 'last_seen_at',
                'last_synced_at', 'last_location_at', 'current_latitude',
                'current_longitude',
                'location_accuracy_meters',
                'location_retention_days', 'media_retention_days',
                'telemetry_retention_days', 'safety_state',
                'safety_state_recorded_at',
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

    /**
     * @param  list<string>  $requiredClearKeys
     */
    public function hasFreshClearInterlocks(
        array $requiredClearKeys,
        int $maximumAgeSeconds = 120,
    ): bool {
        if (
            $this->safety_state_recorded_at === null
            || $this->safety_state_recorded_at->isBefore(now()->subSeconds($maximumAgeSeconds))
        ) {
            return false;
        }

        $state = $this->safety_state ?? [];

        foreach ($requiredClearKeys as $key) {
            if (! array_key_exists($key, $state) || $state[$key] !== false) {
                return false;
            }
        }

        return true;
    }
}
