<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountEntryDestination;
use App\Services\SafeIntendedUrl;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

final class VerifyEmailController extends Controller
{
    public function __invoke(
        EmailVerificationRequest $request,
        AccountEntryDestination $destination,
        SafeIntendedUrl $intendedUrl,
    ): RedirectResponse {
        $request->fulfill();
        $user = $request->user();
        abort_unless($user instanceof User, 403);
        $pendingRoute = $destination->pendingRoute($user);

        return is_string($pendingRoute)
            ? redirect()->route($pendingRoute)->with('feedback', __('auth.verification.success'))
            : redirect()->to($intendedUrl->pull(route('home')))
                ->with('feedback', __('auth.verification.success'));
    }
}
