<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid w-full max-w-4xl gap-7">
        <header class="border-b border-paw-line pb-6">
            <a href="{{ route('care-journals.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                Care journals
            </a>
            <p class="mt-5 text-sm font-bold uppercase text-paw-leaf">Private by default</p>
            <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Create a care journal</h1>
            <p class="mt-2 max-w-2xl text-paw-muted">Choose one managed pet. Daily records stay separate from the public profile and professional medical history.</p>
        </header>

        @if ($errors->any())
            <div class="care-form-errors" role="alert">
                <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                <div>
                    <strong>The journal was not created</strong>
                    <ul>
                        @forelse ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @empty
                            <li>Validation failed.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('care-journals.store') }}" class="care-form-section">
            @csrf
            <div class="care-form-grid">
                <label>
                    Managed pet
                    <select name="pet_profile_key" required>
                        <option value="">Choose a pet</option>
                        @forelse ($pet_options as $key => $label)
                            <option value="{{ $key }}" @selected(old('pet_profile_key') === $key)>{{ $label }}</option>
                        @empty
                            <option value="" disabled>Every managed pet already has a journal</option>
                        @endforelse
                    </select>
                </label>
                <label>
                    Timezone
                    <input name="timezone" value="{{ old('timezone', $timezone) }}" required>
                </label>
                <label>
                    Responsible today
                    <input name="current_caregiver_name" value="{{ old('current_caregiver_name', 'Mia Carter') }}" maxlength="120">
                </label>
            </div>

            <label class="care-check care-check--boxed">
                <input type="checkbox" name="privacy_acknowledged" value="1" required @checked(old('privacy_acknowledged'))>
                <span>I understand this journal is private and access is granted explicitly to each family member, sitter, or specialist.</span>
            </label>

            <button type="submit" class="action action--primary" @disabled($pet_options === [])>
                <x-lucide-lock-keyhole class="icon" aria-hidden="true" />
                <span>Create private journal</span>
            </button>
        </form>
    </div>
</x-app-shell>
