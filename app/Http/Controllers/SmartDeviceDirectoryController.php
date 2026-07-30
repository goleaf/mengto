<?php

namespace App\Http\Controllers;

use App\Services\SmartDevicePresenter;
use Illuminate\Contracts\View\View;

class SmartDeviceDirectoryController extends Controller
{
    public function __invoke(SmartDevicePresenter $presenter): View
    {
        return view('devices.index', $presenter->directory());
    }
}
