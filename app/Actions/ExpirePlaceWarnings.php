<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\PlaceWarningResolution;
use App\Enums\PlaceWarningStatus;
use App\Models\PlaceWarning;
use App\Models\PlaceWarningEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

final class ExpirePlaceWarnings
{
    public function handle(CarbonInterface $at): int
    {
        $ids = PlaceWarning::query()
            ->whereIn('status', [PlaceWarningStatus::Published->value, PlaceWarningStatus::Disputed->value])
            ->where('expires_at', '<=', $at)
            ->orderBy('id')
            ->pluck('id');

        $expired = 0;
        foreach ($ids as $id) {
            $didExpire = DB::transaction(function () use ($id, $at): bool {
                $warning = PlaceWarning::query()->lockForUpdate()->find($id);
                if ($warning === null || ! $warning->status->isActive() || $warning->expires_at->greaterThan($at)) {
                    return false;
                }

                $from = $warning->status;
                $warning->forceFill([
                    'status' => PlaceWarningStatus::Expired,
                    'resolution' => PlaceWarningResolution::Expired,
                    'resolved_at' => $at,
                    'lock_version' => $warning->lock_version + 1,
                ])->save();
                PlaceWarningEvent::query()->create([
                    'place_warning_id' => $warning->id,
                    'event_type' => 'expired',
                    'from_status' => $from->value,
                    'to_status' => PlaceWarningStatus::Expired->value,
                    'public_summary_key' => 'places.warnings.events.expired',
                ]);

                return true;
            }, 3);
            $expired += $didExpire ? 1 : 0;
        }

        return $expired;
    }
}
