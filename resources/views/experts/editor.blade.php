<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    @php
        $isEditing = $expert !== null;
        $selectedSpecializations = old('specializations', $expert?->specializations ?? []);
        $selectedSpecies = old('species', $expert?->species ?? []);
        $selectedAges = old('age_groups', $expert?->age_groups ?? []);
        $selectedLanguages = old('languages', $expert?->languages ?? []);
        $selectedFormats = old('formats', $expert?->formats ?? []);
        $selectedAccessibility = old('accessibility', $expert?->accessibility ?? []);
    @endphp

    <div class="mx-auto grid max-w-5xl gap-6">
        <header class="border-b border-paw-line pb-6">
            <a href="{{ $isEditing ? route('experts.show', $expert) : route('experts.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-paw-leaf">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                {{ $isEditing ? 'Back to profile' : 'Expert directory' }}
            </a>
            <h1 class="mt-4 text-3xl font-bold">{{ $isEditing ? 'Edit professional profile' : 'Create a professional profile' }}</h1>
            <p class="mt-2 max-w-3xl leading-7 text-paw-muted">
                Describe only the work you are qualified to perform. Identity, education, qualification, license, workplace, and organization are reviewed separately.
            </p>
        </header>

        @if ($errors->any())
            <section class="rounded-md border border-red-300 bg-red-50 p-4 text-red-950" role="alert">
                <h2 class="font-bold">Please correct the highlighted information</h2>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @forelse ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @empty
                        <li>Check the form and try again.</li>
                    @endforelse
                </ul>
            </section>
        @endif

        <form
            method="POST"
            action="{{ $isEditing ? route('experts.update', $expert) : route('experts.store') }}"
            enctype="multipart/form-data"
            class="grid gap-8"
        >
            @csrf
            @if ($isEditing)
                @method('PUT')
            @endif

            <section class="grid gap-4" aria-labelledby="identity-section">
                <div>
                    <h2 id="identity-section" class="text-xl font-bold">Public identity</h2>
                    <p class="mt-1 text-sm text-paw-muted">Your legal name stays private and is used only by the verification team.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        Public professional name
                        <input name="public_name" required value="{{ old('public_name', $expert?->public_name) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @error('public_name')<span class="text-xs text-red-700">{{ $message }}</span>@enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Legal name
                        <input name="legal_name" value="{{ old('legal_name', $expert?->legal_name) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Professional type
                        <select name="primary_type" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('primary_type', $expert?->primary_type) === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No professional types</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Years of relevant experience
                        <input type="number" name="years_experience" required min="0" max="80" value="{{ old('years_experience', $expert?->years_experience ?? 0) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                </div>
                <label class="grid gap-1 text-sm font-semibold">
                    Professional headline
                    <input name="headline" required maxlength="180" value="{{ old('headline', $expert?->headline) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Avian veterinarian focused on preventive care and low-stress handling">
                </label>
                <label class="grid gap-1 text-sm font-semibold">
                    Detailed biography
                    <textarea name="bio" required minlength="80" rows="6" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('bio', $expert?->bio) }}</textarea>
                </label>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        Working approach
                        <textarea name="approach" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('approach', $expert?->approach) }}</textarea>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Professional boundaries
                        <textarea name="boundaries" required minlength="20" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="What you do not treat, diagnose, prescribe, or accept">{{ old('boundaries', $expert?->boundaries) }}</textarea>
                    </label>
                </div>
            </section>

            <section class="grid gap-5 border-y border-paw-line py-7" aria-labelledby="scope-section">
                <div>
                    <h2 id="scope-section" class="text-xl font-bold">Competence and species</h2>
                    <p class="mt-1 text-sm text-paw-muted">Choose precise areas. A checked veterinarian badge will not imply expertise in every species or discipline.</p>
                </div>

                <fieldset>
                    <legend class="text-sm font-bold">Specializations</legend>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($specializations as $value => $label)
                            <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="specializations[]" value="{{ $value }}" @checked(in_array($value, $selectedSpecializations, true)) class="mt-0.5 size-4 rounded border-paw-line text-paw-leaf"><span>{{ $label }}</span></label>
                        @empty
                            <p class="text-sm text-paw-muted">No specialization options.</p>
                        @endforelse
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-sm font-bold">Animal species</legend>
                    <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        @forelse ($species_options as $value => $label)
                            <label class="flex items-start gap-2 text-sm"><input type="checkbox" name="species[]" value="{{ $value }}" @checked(in_array($value, $selectedSpecies, true)) class="mt-0.5 size-4 rounded border-paw-line text-paw-leaf"><span>{{ $label }}</span></label>
                        @empty
                            <p class="text-sm text-paw-muted">No species options.</p>
                        @endforelse
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-sm font-bold">Age groups</legend>
                    <div class="mt-3 flex flex-wrap gap-4">
                        @forelse ($age_groups as $value => $label)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="age_groups[]" value="{{ $value }}" @checked(in_array($value, $selectedAges, true)) class="size-4 rounded border-paw-line text-paw-leaf">{{ $label }}</label>
                        @empty
                            <p class="text-sm text-paw-muted">No age group options.</p>
                        @endforelse
                    </div>
                </fieldset>
            </section>

            <section class="grid gap-5" aria-labelledby="practice-section">
                <h2 id="practice-section" class="text-xl font-bold">Practice details</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <label class="grid gap-1 text-sm font-semibold">
                        Country
                        <input name="country" required value="{{ old('country', $expert?->country ?? 'Lithuania') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        City
                        <input name="city" required value="{{ old('city', $expert?->city ?? 'Vilnius') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Service area
                        <input name="service_area" value="{{ old('service_area', $expert?->service_area) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Vilnius and 20 km radius">
                    </label>
                </div>

                <fieldset>
                    <legend class="text-sm font-bold">Consultation languages</legend>
                    <div class="mt-3 flex flex-wrap gap-4">
                        @forelse ($languages as $value => $label)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="languages[]" value="{{ $value }}" @checked(in_array($value, $selectedLanguages, true)) class="size-4 rounded border-paw-line text-paw-leaf">{{ $label }}</label>
                        @empty
                            <p class="text-sm text-paw-muted">No language options.</p>
                        @endforelse
                    </div>
                </fieldset>

                <fieldset>
                    <legend class="text-sm font-bold">Work formats</legend>
                    <div class="mt-3 flex flex-wrap gap-4">
                        @forelse ($formats as $value => $label)
                            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="formats[]" value="{{ $value }}" @checked(in_array($value, $selectedFormats, true)) class="size-4 rounded border-paw-line text-paw-leaf">{{ $label }}</label>
                        @empty
                            <p class="text-sm text-paw-muted">No format options.</p>
                        @endforelse
                    </div>
                </fieldset>

                <div class="grid gap-4 sm:grid-cols-2">
                    <fieldset class="grid gap-2">
                        <legend class="text-sm font-bold">Methods and principles</legend>
                        @for ($index = 0; $index < 3; $index++)
                            <input name="methods[]" value="{{ old('methods.'.$index, $expert?->methods[$index] ?? '') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Method or principle {{ $index + 1 }}">
                        @endfor
                    </fieldset>
                    <fieldset>
                        <legend class="text-sm font-bold">Place accessibility</legend>
                        <div class="mt-3 grid gap-2">
                            @forelse ($accessibility_options as $value => $label)
                                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="accessibility[]" value="{{ $value }}" @checked(in_array($value, $selectedAccessibility, true)) class="size-4 rounded border-paw-line text-paw-leaf">{{ $label }}</label>
                            @empty
                                <p class="text-sm text-paw-muted">No accessibility options.</p>
                            @endforelse
                        </div>
                    </fieldset>
                </div>

                <div class="grid gap-4 sm:grid-cols-4">
                    <label class="grid gap-1 text-sm font-semibold">
                        Availability
                        <select name="availability_status" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @foreach (['available' => 'Available', 'limited' => 'Limited', 'waitlist' => 'Waitlist', 'unavailable' => 'Unavailable'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('availability_status', $expert?->availability_status ?? 'available') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Typical response
                        <input name="response_time" value="{{ old('response_time', $expert?->response_time) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Within one business day">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Price from
                        <input type="number" name="price_from" min="0" step="0.01" value="{{ old('price_from', $expert?->price_from) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Currency
                        <input name="currency" required maxlength="3" value="{{ old('currency', $expert?->currency ?? 'EUR') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5 uppercase">
                    </label>
                </div>
                <div class="flex flex-wrap gap-5">
                    <input type="hidden" name="accepts_new_clients" value="0">
                    <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="accepts_new_clients" value="1" @checked((bool) old('accepts_new_clients', $expert?->accepts_new_clients ?? true)) class="size-4 rounded border-paw-line text-paw-leaf"> Accepting new clients</label>
                    <input type="hidden" name="offers_emergency_care" value="0">
                    <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="offers_emergency_care" value="1" @checked((bool) old('offers_emergency_care', $expert?->offers_emergency_care ?? false)) class="size-4 rounded border-paw-line text-paw-leaf"> Provides verified emergency care</label>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">Avatar image URL<input type="url" name="avatar_url" value="{{ old('avatar_url', $expert?->avatar_url) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></label>
                    <label class="grid gap-1 text-sm font-semibold">Cover image URL<input type="url" name="cover_url" value="{{ old('cover_url', $expert?->cover_url) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></label>
                </div>
            </section>

            @unless ($isEditing)
                <section class="grid gap-4 border-y border-paw-line py-7" aria-labelledby="credential-section">
                    <div>
                        <h2 id="credential-section" class="text-xl font-bold">Private verification document</h2>
                        <p class="mt-1 text-sm text-paw-muted">The original file is stored privately and never displayed in the public profile.</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        <label class="grid gap-1 text-sm font-semibold">Document type<input name="credential_type" value="{{ old('credential_type') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="license or diploma"></label>
                        <label class="grid gap-1 text-sm font-semibold">Document title<input name="credential_title" value="{{ old('credential_title') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></label>
                        <label class="grid gap-1 text-sm font-semibold">Issuer<input name="credential_issuer" value="{{ old('credential_issuer') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5"></label>
                    </div>
                    <label class="grid gap-1 text-sm font-semibold">
                        PDF or image, up to 10 MB
                        <input type="file" name="credential_file" accept=".pdf,.jpg,.jpeg,.png,.webp" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                </section>
            @endunless

            <footer class="flex flex-wrap justify-end gap-2">
                <x-action-control label="Cancel" icon="x" :href="$isEditing ? route('experts.show', $expert) : route('experts.index')" />
                <button type="submit" class="action action--primary action--comfortable">
                    <x-lucide-save class="icon icon--sm" aria-hidden="true" />
                    <span>{{ $isEditing ? 'Save profile' : 'Create profile' }}</span>
                </button>
            </footer>
        </form>
    </div>
</x-app-shell>
