<?php

namespace App\Http\Controllers;

use App\Actions\CreateDeviceSafeZone;
use App\Http\Requests\StoreDeviceSafeZoneRequest;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class DeviceSafeZoneStoreController extends Controller
{
    public function __invoke(
        StoreDeviceSafeZoneRequest $request,
        SmartDevice $smartDevice,
        CreateDeviceSafeZone $create,
    ): RedirectResponse {
        Gate::authorize('update', $smartDevice);
        $create->handle($smartDevice, $request->validated());

        return to_route('devices.show', $smartDevice)
            ->with('feedback', __('messages.private_safe_zone_saved_exact_coordinates_remain_owner_o_fb6e7d594e'));
    }
}
