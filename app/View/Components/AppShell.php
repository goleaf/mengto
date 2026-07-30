<?php

declare(strict_types=1);

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\View\Component;

final class AppShell extends Component
{
    public string $htmlLocale;

    public string $title;

    public string $csrfToken;

    /**
     * @param  array<string, mixed>  $owner
     */
    public function __construct(
        public array $owner,
        ?string $title = null,
        public string $activeSection = 'feed',
    ) {
        $this->htmlLocale = str_replace('_', '-', App::currentLocale());
        $this->title = $title ?? __('auth.brand');
        $this->csrfToken = csrf_token();
    }

    public function render(): View
    {
        return view('components.app-shell');
    }
}
