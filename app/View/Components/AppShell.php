<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\User;
use App\Services\AuthenticatedUserPresenter;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\View\Component;

final class AppShell extends Component
{
    public string $htmlLocale;

    public string $title;

    public string $csrfToken;

    /** @var array<string, mixed>|null */
    public ?array $owner;

    /** @param array<string, mixed>|null $owner */
    public function __construct(
        ?array $owner,
        AuthFactory $auth,
        AuthenticatedUserPresenter $authenticatedUsers,
        ?string $title = null,
        public string $activeSection = 'feed',
    ) {
        $user = $auth->guard()->user();
        $this->owner = $user instanceof User
            ? $authenticatedUsers->present($user)
            : null;
        $this->htmlLocale = str_replace('_', '-', App::currentLocale());
        $this->title = $title ?? __('auth.brand');
        $this->csrfToken = csrf_token();
    }

    public function render(): View
    {
        return view('components.app-shell');
    }
}
