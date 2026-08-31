<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\UpdateProfilePreferences;
use App\Livewire\Forms\ProfilePreferencesForm;
use App\Models\User;
use App\Services\AuthenticatedUserPresenter;
use App\Services\EmailVerificationMode;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class ProfileSettings extends Component
{
    public ProfilePreferencesForm $form;

    public string $feedback = '';

    #[Locked]
    public int $mountedUserId = 0;

    private AuthFactory $auth;

    private EmailVerificationMode $emailVerification;

    private AuthenticatedUserPresenter $authenticatedUsers;

    private UpdateProfilePreferences $updatePreferences;

    private bool $mounting = false;

    public function boot(
        AuthFactory $auth,
        AuthenticatedUserPresenter $authenticatedUsers,
        EmailVerificationMode $emailVerification,
        UpdateProfilePreferences $updatePreferences,
    ): void {
        $this->auth = $auth;
        $this->authenticatedUsers = $authenticatedUsers;
        $this->emailVerification = $emailVerification;
        $this->updatePreferences = $updatePreferences;
    }

    public function mount(): void
    {
        $this->mounting = true;

        try {
            $user = $this->requireUser();
            $this->mountedUserId = (int) $user->getKey();
            $this->form->fillFromUser($user);
            $this->feedback = (string) Session::get('profile-settings-feedback', '');
        } finally {
            $this->mounting = false;
        }
    }

    public function hydrate(): void
    {
        $this->requireUser();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function localeOptions(): array
    {
        return collect(config('platform.supported_locales', ['en']))
            ->mapWithKeys(static fn (string $locale): array => [
                $locale => __('auth.locales.'.$locale),
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    #[Computed]
    public function timezoneOptions(): array
    {
        return collect(timezone_identifiers_list())
            ->mapWithKeys(static fn (string $timezone): array => [
                $timezone => $timezone,
            ])
            ->all();
    }

    #[Computed]
    public function profileUrl(): string
    {
        return $this->authenticatedUsers->present($this->requireUser())['profile_url'];
    }

    public function save(): void
    {
        $user = $this->updatePreferences->handle(
            $this->requireUser(),
            $this->form->validatedData(),
        );

        $this->auth->guard('web')->setUser($user);
        Session::put('locale', $user->locale);
        App::setLocale($user->locale);
        $this->feedback = trans('auth.settings.saved', locale: $user->locale);
        Session::flash('profile-settings-feedback', $this->feedback);
        $this->redirectRoute('profile.settings');
    }

    public function render(): View
    {
        return view('livewire.profile-settings')
            ->layout('components.livewire-app-layout', [
                'title' => __('auth.settings.title'),
                'activeSection' => 'profile',
            ]);
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();

        abort_unless(
            $user instanceof User
                && $user->isActive()
                && $this->emailVerification->allows($user)
                && (
                    $this->mounting
                    || ($this->mountedUserId > 0 && $this->mountedUserId === (int) $user->getKey())
                ),
            403,
        );

        return $user;
    }
}
