<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\UpdateProfilePreferences;
use App\Livewire\Forms\ProfilePreferencesForm;
use App\Models\User;
use App\Services\ProfilePresenter;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class ProfileSettings extends Component
{
    public ProfilePreferencesForm $form;

    public string $feedback = '';

    private AuthFactory $auth;

    private ProfilePresenter $profiles;

    private UpdateProfilePreferences $updatePreferences;

    public function boot(
        AuthFactory $auth,
        ProfilePresenter $profiles,
        UpdateProfilePreferences $updatePreferences,
    ): void {
        $this->auth = $auth;
        $this->profiles = $profiles;
        $this->updatePreferences = $updatePreferences;
    }

    public function mount(): void
    {
        $this->form->fillFromUser($this->requireUser());
        $this->feedback = (string) Session::get('profile-settings-feedback', '');
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

    public function save(): void
    {
        $user = $this->updatePreferences->handle(
            $this->requireUser(),
            $this->form->validatedData(),
        );

        Session::put('locale', $user->locale);
        $this->feedback = trans('auth.settings.saved', locale: $user->locale);
        Session::flash('profile-settings-feedback', $this->feedback);
        $this->redirectRoute('profile.settings');
    }

    public function render(): View
    {
        return view('livewire.profile-settings')
            ->layout('components.livewire-app-layout', [
                'owner' => $this->profiles->owner(),
                'title' => __('auth.settings.title'),
                'activeSection' => 'profile',
            ]);
    }

    private function requireUser(): User
    {
        $user = $this->auth->guard('web')->user();

        abort_unless($user instanceof User && $user->isActive(), 403);

        return $user;
    }
}
