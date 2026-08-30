<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountEntryDestination;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

final class VerifyEmailController extends Controller
{
    public function __invoke(
        EmailVerificationRequest $request,
        AccountEntryDestination $destination,
    ): RedirectResponse {
        $request->fulfill();
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        return redirect()
            ->to($destination->urlFor($user, route('home')))
            ->with('feedback', __('auth.verification.success'));
    }
}
