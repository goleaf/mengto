<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\CreateSmartDevice;
use App\Http\Requests\StoreSmartDeviceRequest;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class SmartDeviceStoreController extends Controller
{
    public function __invoke(
        StoreSmartDeviceRequest $request,
        CreateSmartDevice $create,
    ): RedirectResponse {
        Gate::authorize('create', SmartDevice::class);
        $device = $create->handle($request->validated());

        return to_route('devices.show', $device)
            ->with('feedback', __('devices.feedback.connected'));
    }
}
