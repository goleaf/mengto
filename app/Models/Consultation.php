<?php

namespace App\Models;

use App\Enums\ConsultationStatus;
use Database\Factories\ConsultationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Consultation extends Model
{
    /** @use HasFactory<ConsultationFactory> */
    use HasFactory;

    private const ROUTE_COLUMNS = [
        'id', 'booking_id', 'expert_profile_id', 'status', 'started_at',
        'ended_at', 'client_summary', 'action_plan', 'referral_summary',
        'follow_up_until', 'summary_confirmed_at', 'created_at', 'updated_at',
    ];

    protected $fillable = [
        'booking_id', 'expert_profile_id', 'status', 'started_at', 'ended_at',
        'private_notes', 'client_summary', 'action_plan', 'referral_summary',
        'follow_up_until', 'summary_confirmed_at',
    ];

    protected $hidden = ['private_notes'];

    protected function casts(): array
    {
        return [
            'status' => ConsultationStatus::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'private_notes' => 'encrypted',
            'action_plan' => 'array',
            'follow_up_until' => 'datetime',
            'summary_confirmed_at' => 'datetime',
        ];
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select(self::ROUTE_COLUMNS);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function expertProfile(): BelongsTo
    {
        return $this->belongsTo(ExpertProfile::class);
    }
}
