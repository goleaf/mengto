<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final readonly class UnavailableAccountResponse
{
    public function __construct(private AuthManager $auth) {}

    public function redirect(Request $request): RedirectResponse
    {
        $this->auth->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('feedback', __('auth.login.account_unavailable'));
    }
}
