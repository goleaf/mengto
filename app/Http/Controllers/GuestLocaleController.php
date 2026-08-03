<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateGuestLocaleRequest;
use Illuminate\Http\RedirectResponse;

final class GuestLocaleController extends Controller
{
    public function __invoke(UpdateGuestLocaleRequest $request): RedirectResponse
    {
        $request->session()->put('locale', $request->validated('locale'));

        return redirect()->route('login');
    }
}
