<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-6">
        <header class="border-b border-paw-line pb-6">
            <a href="{{ route('lost-found.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-paw-leaf">
                <x-lucide-arrow-left class="size-4" aria-hidden="true" />
                Lost & found
            </a>
            <p class="mt-5 text-sm font-bold uppercase text-paw-coral">Urgent report</p>
            <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Report a missing or found animal</h1>
            <p class="mt-3 max-w-3xl leading-7 text-paw-muted">
                Publish the essential location and identification details now. Exact coordinates and direct contact details stay protected.
            </p>
        </header>

        @if ($errors->any())
            <div class="rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-900" role="alert">
                <div class="flex items-center gap-2 font-bold">
                    <x-lucide-circle-alert class="size-5" aria-hidden="true" />
                    Check the highlighted fields
                </div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @forelse ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @empty
                        <li>No validation details are available.</li>
                    @endforelse
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('lost-found.store') }}" enctype="multipart/form-data" class="grid gap-8">
            @csrf
            <input type="hidden" name="intent" value="publish">
            <input type="hidden" name="country" value="LT">

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="report-kind-title">
                <h2 id="report-kind-title" class="text-xl font-bold">What happened?</h2>
                <div class="grid gap-3 sm:grid-cols-2">
                    @forelse ($types as $value => $label)
                        <label class="flex cursor-pointer items-start gap-3 rounded-md border border-paw-line bg-white p-4 has-[:checked]:border-paw-leaf has-[:checked]:bg-paw-mint">
                            <input type="radio" name="type" value="{{ $value }}" class="mt-1" @checked(old('type', 'lost') === $value)>
                            <span>
                                <strong class="block">{{ $label }}</strong>
                                <span class="mt-1 block text-sm text-paw-muted">
                                    {{ $value === 'lost' ? 'Start an active search from a pet profile.' : 'Create a protected handover and owner verification path.' }}
                                </span>
                            </span>
                        </label>
                    @empty
                        <p class="text-sm text-paw-muted">Report types are unavailable.</p>
                    @endforelse
                </div>
            </section>

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="animal-title">
                <h2 id="animal-title" class="text-xl font-bold">Animal details</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="grid gap-1 text-sm font-semibold">
                        Pet profile
                        <select name="pet_profile_key" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">No saved profile</option>
                            @forelse ($pet_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('pet_profile_key', 'scout') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No profiles</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Name
                        <input name="pet_name" value="{{ old('pet_name', $default_pet['name'] ?? '') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="100">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Species
                        <select name="species" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                            @forelse ($species_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('species', 'dog') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No species</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Breed or mix
                        <input name="breed" value="{{ old('breed', $default_pet['breed'] ?? '') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="120">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Primary color
                        <input name="primary_color" value="{{ old('primary_color', 'black with white chest') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="80">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Size
                        <select name="size" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">Choose size</option>
                            @forelse ($size_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('size', 'large') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No size options</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Sex
                        <select name="sex" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">Unknown</option>
                            <option value="male" @selected(old('sex') === 'male')>Male</option>
                            <option value="female" @selected(old('sex') === 'female')>Female</option>
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Age
                        <input name="age_label" value="{{ old('age_label', $default_pet['age'] ?? '') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="80" placeholder="4 years">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Microchip
                        <select name="microchip_status" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($microchip_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('microchip_status', 'present') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No microchip options</option>
                            @endforelse
                        </select>
                    </label>
                </div>

                <label class="grid gap-1 text-sm font-semibold">
                    Public description
                    <textarea name="description" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="3000">{{ old('description', 'Friendly at home but may run if approached quickly. Please report the location and direction without chasing.') }}</textarea>
                </label>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        Visible identifying marks
                        <textarea name="distinctive_marks" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1500">{{ old('distinctive_marks') }}</textarea>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Hidden verification mark
                        <textarea name="hidden_marks" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1000">{{ old('hidden_marks') }}</textarea>
                        <span class="text-xs font-normal text-paw-muted">Visible only to the search coordinator.</span>
                    </label>
                </div>

                <label class="grid gap-1 text-sm font-semibold">
                    Current photos
                    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    <span class="text-xs font-normal text-paw-muted">Up to six JPG, PNG, or WebP files. Include a full-body image and visible markings.</span>
                </label>
            </section>

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="location-title">
                <h2 id="location-title" class="text-xl font-bold">Last location and time</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        Public area
                        <input name="last_seen_area" value="{{ old('last_seen_area') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="160" placeholder="Vingis Park, western entrance">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        City
                        <input name="city" value="{{ old('city', 'Vilnius') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required maxlength="100">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Observed at
                        <input type="datetime-local" name="last_seen_at" value="{{ old('last_seen_at', now()->subMinutes(30)->format('Y-m-d\TH:i')) }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Direction
                        <input name="direction" value="{{ old('direction') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="100" placeholder="Toward the river path">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Exact latitude
                        <input type="number" step="0.000001" name="latitude" value="{{ old('latitude', '54.683400') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Exact longitude
                        <input type="number" step="0.000001" name="longitude" value="{{ old('longitude', '25.236800') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" required>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2">
                        Exact location note
                        <input name="location_note" value="{{ old('location_note') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="300" placeholder="Bench beside the west gate">
                        <span class="text-xs font-normal text-paw-muted">Encrypted and limited to the search team; public coordinates are generalized.</span>
                    </label>
                </div>
            </section>

            <section class="grid gap-4 border-b border-paw-line pb-8" aria-labelledby="safety-title">
                <h2 id="safety-title" class="text-xl font-bold">Safe approach</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        What to do
                        <textarea name="approach_instructions" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1500">{{ old('approach_instructions', 'Stay at a distance, speak quietly, note the direction, and contact the owner through the platform.') }}</textarea>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        What to avoid
                        <textarea name="avoid_instructions" rows="4" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1500">{{ old('avoid_instructions', 'Do not chase, surround, shout, or enter unsafe or private areas.') }}</textarea>
                    </label>
                </div>
                <label class="grid gap-1 text-sm font-semibold">
                    Health or immediate safety notice
                    <input name="health_notice" value="{{ old('health_notice') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="1000" placeholder="Needs regular medication; contact the owner quickly">
                </label>
            </section>

            <section class="grid gap-4" aria-labelledby="contact-title">
                <h2 id="contact-title" class="text-xl font-bold">Protected contact and alerts</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        Contact channel
                        <select name="contact_channel" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="platform" @selected(old('contact_channel', 'platform') === 'platform')>Protected platform messages</option>
                            <option value="email" @selected(old('contact_channel') === 'email')>Protected email</option>
                            <option value="phone" @selected(old('contact_channel') === 'phone')>Protected phone</option>
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Email or phone when selected
                        <input name="contact_value" value="{{ old('contact_value') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" maxlength="160">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Alert radius
                        <select name="notification_radius_km" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ([2, 5, 10, 25, 50] as $radius)
                                <option value="{{ $radius }}" @selected((int) old('notification_radius_km', 5) === $radius)>{{ $radius }} km</option>
                            @empty
                                <option value="">No radius options available</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Visibility
                        <select name="visibility" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="public" @selected(old('visibility', 'public') === 'public')>Public directory</option>
                            <option value="link" @selected(old('visibility') === 'link')>Accessible by link</option>
                            <option value="registered" @selected(old('visibility') === 'registered')>Registered community</option>
                            <option value="local-group" @selected(old('visibility') === 'local-group')>Local group only</option>
                        </select>
                    </label>
                </div>

                <label class="flex items-start gap-3 rounded-md border border-paw-line bg-white p-4">
                    <input type="checkbox" name="animal_secured" value="1" class="mt-1" @checked(old('animal_secured'))>
                    <span class="text-sm">
                        <strong class="block">The found animal is currently secured in a safe place</strong>
                        <span class="mt-1 block text-paw-muted">The exact address remains private until ownership is verified.</span>
                    </span>
                </label>

                <label class="flex items-start gap-3 rounded-md border border-paw-line bg-white p-4">
                    <input type="checkbox" name="safety_acknowledged" value="1" class="mt-1" required @checked(old('safety_acknowledged'))>
                    <span class="text-sm">
                        <strong class="block">I will keep exact addresses, payment codes, and sensitive medical details out of the public description.</strong>
                        <span class="mt-1 block text-paw-muted">Urgent reports are for coordination, not emergency dispatch.</span>
                    </span>
                </label>

                <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('lost-found.index') }}" class="action action--surface">Cancel</a>
                    <button type="submit" class="action action--primary">
                        <x-lucide-siren class="icon" aria-hidden="true" />
                        <span>Publish urgent report</span>
                    </button>
                </div>
            </section>
        </form>
    </div>
</x-app-shell>
