<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeviceCommandStatus;
use Database\Factories\DeviceCommandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $author_key
 * @property string $author_name
 * @property string $command_type
 * @property Carbon|null $completed_at
 * @property Carbon|null $confirmed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $expires_at
 * @property string|null $failure_reason
 * @property int $id
 * @property string $idempotency_key
 * @property Carbon $issued_at
 * @property array<array-key, mixed>|null $parameters
 * @property bool $requires_confirmation
 * @property array<array-key, mixed>|null $result
 * @property string $safety_level
 * @property-read SmartDevice|null $smartDevice
 * @property int $smart_device_id
 * @property DeviceCommandStatus $status
 * @property Carbon|null $updated_at
 */
class DeviceCommand extends Model
{
    /** @use HasFactory<DeviceCommandFactory> */
    use HasFactory;

    protected $fillable = [
        'smart_device_id', 'author_key', 'author_name', 'idempotency_key',
        'command_type', 'parameters', 'status', 'safety_level',
        'requires_confirmation', 'confirmed_at', 'issued_at',
        'delivered_at', 'completed_at', 'expires_at', 'result',
        'failure_reason',
    ];

    protected $hidden = ['parameters', 'result', 'failure_reason'];

    protected function casts(): array
    {
        return [
            'parameters' => 'encrypted:array',
            'status' => DeviceCommandStatus::class,
            'requires_confirmation' => 'boolean',
            'confirmed_at' => 'datetime',
            'issued_at' => 'datetime',
            'delivered_at' => 'datetime',
            'completed_at' => 'datetime',
            'expires_at' => 'datetime',
            'result' => 'encrypted:array',
            'failure_reason' => 'encrypted',
        ];
    }

    /** @return BelongsTo<\App\Models\SmartDevice, $this>*/
    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }
}
