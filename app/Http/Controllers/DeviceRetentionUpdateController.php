<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\UpdateDeviceRetention;
use App\Http\Requests\UpdateDeviceRetentionRequest;
use App\Models\SmartDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class DeviceRetentionUpdateController extends Controller
{
    public function __invoke(
        UpdateDeviceRetentionRequest $request,
        SmartDevice $smartDevice,
        UpdateDeviceRetention $update,
    ): RedirectResponse {
        Gate::authorize('update', $smartDevice);
        $update->handle($smartDevice, $request->validated());

        return to_route('devices.manage', $smartDevice)
            ->with('feedback', __('devices.feedback.retention_updated'));
    }
}
