<?php

namespace App\Http\Controllers;

use App\Actions\CreateSmartDevice;
use App\Http\Requests\StoreSmartDeviceRequest;
use Illuminate\Http\RedirectResponse;

class SmartDeviceStoreController extends Controller
{
    public function __invoke(
        StoreSmartDeviceRequest $request,
        CreateSmartDevice $create,
    ): RedirectResponse {
        $device = $create->handle($request->validated());

        return to_route('devices.show', $device)
            ->with('feedback', 'Device connected privately. Verify its first signal and ownership settings.');
    }
}
