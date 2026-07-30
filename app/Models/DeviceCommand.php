<?php

namespace App\Models;

use App\Enums\DeviceCommandStatus;
use Database\Factories\DeviceCommandFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function smartDevice(): BelongsTo
    {
        return $this->belongsTo(SmartDevice::class);
    }
}
