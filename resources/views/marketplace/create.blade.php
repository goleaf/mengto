<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-6">
        <a href="{{ route('marketplace.index') }}" class="inline-flex w-fit items-center gap-2 text-sm font-bold text-paw-leaf">
            <x-lucide-arrow-left class="size-4" aria-hidden="true" />
            Marketplace
        </a>

        <header class="border-b border-paw-line pb-6">
            <p class="text-sm font-bold uppercase text-paw-leaf">New marketplace listing</p>
            <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Create a structured offer</h1>
            <p class="mt-3 max-w-2xl leading-7 text-paw-muted">Disclose measurements, defects, availability, handover terms, and the seller context that applies.</p>
        </header>

        @if ($errors->any())
            <div class="form-errors" role="alert">
                <x-lucide-circle-alert class="icon" aria-hidden="true" />
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('marketplace.store') }}" enctype="multipart/form-data" class="grid gap-8">
            @csrf

            <section aria-labelledby="offer-heading">
                <h2 id="offer-heading" class="text-2xl font-bold">Offer</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="grid gap-1 text-sm font-semibold">
                        Listing type
                        <select name="type" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', 'sale') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No types available</option>
                            @endforelse
                        </select>
                        @error('type') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Category
                        <select name="category" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(old('category', 'walking-gear') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No categories available</option>
                            @endforelse
                        </select>
                        @error('category') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Seller type
                        <select name="seller_type" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($seller_types as $value => $label)
                                <option value="{{ $value }}" @selected(old('seller_type', 'private') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No seller types available</option>
                            @endforelse
                        </select>
                        @error('seller_type') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold lg:col-span-2">
                        Title
                        <input name="title" value="{{ old('title') }}" required minlength="12" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Atlas 40 carrier for a cat, good condition">
                        @error('title') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Business or organization
                        <input name="business_name" value="{{ old('business_name') }}" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Required outside private sales">
                        @error('business_name') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2 lg:col-span-3">
                        Description
                        <textarea name="description" required minlength="50" maxlength="5000" rows="6" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Describe the real item or service, included parts, limits, and anything the recipient must verify.">{{ old('description') }}</textarea>
                        @error('description') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                </div>
            </section>

            <section aria-labelledby="identity-heading" class="border-t border-paw-line pt-7">
                <h2 id="identity-heading" class="text-2xl font-bold">Identity and condition</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="grid gap-1 text-sm font-semibold">
                        Brand
                        <input name="brand" value="{{ old('brand') }}" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Model
                        <input name="model" value="{{ old('model') }}" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Material
                        <input name="material" value="{{ old('material') }}" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Condition
                        <select name="condition" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($conditions as $value => $label)
                                <option value="{{ $value }}" @selected(old('condition', 'good') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No condition options</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2 lg:col-span-4">
                        Defects and repairs
                        <textarea name="defects" maxlength="2000" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="List scratches, cracks, missing parts, battery wear, stains, repairs, or other limitations.">{{ old('defects') }}</textarea>
                        @error('defects') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Hygiene status
                        <select name="hygiene_status" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">Not specified</option>
                            @forelse ($hygiene_statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('hygiene_status') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No hygiene options</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="flex items-center gap-3 self-end rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                        <input type="checkbox" name="sealed_package" value="1" @checked(old('sealed_package')) class="size-4 rounded border-paw-line text-paw-leaf">
                        Package is sealed
                    </label>
                </div>
            </section>

            <section aria-labelledby="commercial-heading" class="border-t border-paw-line pt-7">
                <h2 id="commercial-heading" class="text-2xl font-bold">Price and availability</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="grid gap-1 text-sm font-semibold">
                        Price
                        <span class="flex rounded-md border border-paw-line bg-white">
                            <span class="grid w-11 place-items-center border-r border-paw-line font-bold text-paw-muted">€</span>
                            <input type="number" name="price" value="{{ old('price') }}" min="0" max="999999.99" step="0.01" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 outline-none">
                        </span>
                        @error('price') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <input type="hidden" name="currency" value="EUR">
                    <label class="grid gap-1 text-sm font-semibold">
                        Quantity
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="100000" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Availability
                        <select name="availability" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($availability_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('availability', 'in-stock') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No availability options</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="flex items-center gap-3 self-end rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                        <input type="checkbox" name="is_free" value="1" @checked(old('is_free')) class="size-4 rounded border-paw-line text-paw-leaf">
                        Free handover
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2 lg:col-span-4">
                        Exchange preference
                        <textarea name="exchange_preferences" maxlength="1000" rows="2" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('exchange_preferences') }}</textarea>
                    </label>
                </div>
            </section>

            <section aria-labelledby="fit-heading" class="border-t border-paw-line pt-7">
                <h2 id="fit-heading" class="text-2xl font-bold">Pet fit and measurements</h2>
                <fieldset class="mt-4">
                    <legend class="text-sm font-semibold">Suitable pet types</legend>
                    <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                        @forelse ($species_options as $value => $label)
                            <label class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                                <input type="checkbox" name="species[]" value="{{ $value }}" @checked(in_array($value, old('species', ['dog']), true)) class="size-4 rounded border-paw-line text-paw-leaf">
                                {{ $label }}
                            </label>
                        @empty
                            <p class="text-sm text-paw-muted">No pet types available.</p>
                        @endforelse
                    </div>
                </fieldset>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="grid gap-1 text-sm font-semibold">
                        Pet size
                        <select name="pet_size" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse (['any' => 'Any size', 'small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('pet_size', 'any') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No size options</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Age group
                        <select name="age_group" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($age_groups as $value => $label)
                                <option value="{{ $value }}" @selected(old('age_group', 'all') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No age groups</option>
                            @endforelse
                        </select>
                    </label>
                    @forelse ([
                        'length_cm' => 'Length, cm',
                        'width_cm' => 'Width, cm',
                        'height_cm' => 'Height, cm',
                        'max_weight_kg' => 'Maximum weight, kg',
                    ] as $field => $label)
                        <label class="grid gap-1 text-sm font-semibold">
                            {{ $label }}
                            <input type="number" name="{{ $field }}" value="{{ old($field) }}" min="0.1" step="0.1" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        </label>
                    @empty
                        <p class="text-sm text-paw-muted">No measurement fields.</p>
                    @endforelse
                </div>
            </section>

            <section aria-labelledby="special-heading" class="border-t border-paw-line pt-7">
                <h2 id="special-heading" class="text-2xl font-bold">Type-specific details</h2>
                <div class="mt-4 grid gap-6 lg:grid-cols-2">
                    <fieldset class="market-fieldset">
                        <legend>Rental</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label>Rate unit
                                <select name="rental_rate_unit">
                                    <option value="">Not a rental</option>
                                    <option value="day" @selected(old('rental_rate_unit') === 'day')>Per day</option>
                                    <option value="week" @selected(old('rental_rate_unit') === 'week')>Per week</option>
                                </select>
                            </label>
                            <label>Deposit
                                <input type="number" name="deposit_amount" value="{{ old('deposit_amount') }}" min="0" step="0.01">
                            </label>
                            <label>Available from
                                <input type="date" name="available_from" value="{{ old('available_from') }}">
                            </label>
                            <label>Available until
                                <input type="date" name="available_until" value="{{ old('available_until') }}">
                            </label>
                            <label>Minimum days
                                <input type="number" name="minimum_days" value="{{ old('minimum_days', 1) }}" min="1" max="365">
                            </label>
                            <label>Maximum days
                                <input type="number" name="maximum_days" value="{{ old('maximum_days') }}" min="1" max="365">
                            </label>
                        </div>
                    </fieldset>

                    <fieldset class="market-fieldset">
                        <legend>Service</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label>Duration, minutes
                                <input type="number" name="service_duration_minutes" value="{{ old('service_duration_minutes') }}" min="15" max="1440">
                            </label>
                            <label>Format
                                <select name="service_format">
                                    <option value="">Not a service</option>
                                    @forelse (['in-person' => 'In person', 'online' => 'Online', 'home-visit' => 'Home visit', 'group' => 'Group'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('service_format') === $value)>{{ $label }}</option>
                                    @empty
                                        <option disabled>No formats</option>
                                    @endforelse
                                </select>
                            </label>
                        </div>
                    </fieldset>

                    <fieldset class="market-fieldset">
                        <legend>Shelter need</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label>Priority
                                <select name="urgency">
                                    <option value="">Not a shelter request</option>
                                    @forelse (['urgent' => 'Urgent', 'important' => 'Important', 'regular' => 'Regular need', 'wish' => 'Wish'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('urgency') === $value)>{{ $label }}</option>
                                    @empty
                                        <option disabled>No priorities</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>Already received
                                <input type="number" name="received_quantity" value="{{ old('received_quantity', 0) }}" min="0">
                            </label>
                            <label>Needed by
                                <input type="date" name="needed_by" value="{{ old('needed_by') }}">
                            </label>
                        </div>
                    </fieldset>

                    <fieldset class="market-fieldset">
                        <legend>Adoption profile</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label>Animal name
                                <input name="animal_name" value="{{ old('animal_name') }}" maxlength="100">
                            </label>
                            <label>Age
                                <input name="animal_age" value="{{ old('animal_age') }}" maxlength="80">
                            </label>
                            <label>Sex
                                <select name="animal_sex">
                                    <option value="">Not specified</option>
                                    <option value="female" @selected(old('animal_sex') === 'female')>Female</option>
                                    <option value="male" @selected(old('animal_sex') === 'male')>Male</option>
                                    <option value="unknown" @selected(old('animal_sex') === 'unknown')>Unknown</option>
                                </select>
                            </label>
                            <label class="sm:col-span-2">Temperament and important needs
                                <textarea name="temperament" rows="3" maxlength="1500">{{ old('temperament') }}</textarea>
                            </label>
                            <label class="sm:col-span-2">Adoption conditions
                                <textarea name="adoption_conditions" rows="3" maxlength="2000">{{ old('adoption_conditions') }}</textarea>
                            </label>
                        </div>
                    </fieldset>
                </div>
            </section>

            <section aria-labelledby="media-heading" class="border-t border-paw-line pt-7">
                <h2 id="media-heading" class="text-2xl font-bold">Media</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        Photos
                        <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @error('photos.*') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Demonstration video
                        <input type="file" name="video" accept="video/mp4,video/quicktime,video/webm" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @error('video') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2">
                        Existing cover URL
                        <input type="url" name="cover_url" value="{{ old('cover_url') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="https://">
                    </label>
                </div>
            </section>

            <section aria-labelledby="handover-heading" class="border-t border-paw-line pt-7">
                <h2 id="handover-heading" class="text-2xl font-bold">Handover and returns</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        City
                        <input name="city" value="{{ old('city', 'Vilnius') }}" required maxlength="80" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Approximate area
                        <input name="area" value="{{ old('area') }}" maxlength="100" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                </div>
                <fieldset class="mt-4">
                    <legend class="text-sm font-semibold">Available handover options</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($delivery_options as $value => $label)
                            <label class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                                <input type="checkbox" name="delivery_options[]" value="{{ $value }}" @checked(in_array($value, old('delivery_options', ['meetup']), true)) class="size-4 rounded border-paw-line text-paw-leaf">
                                {{ $label }}
                            </label>
                        @empty
                            <p class="text-sm text-paw-muted">No handover options.</p>
                        @endforelse
                    </div>
                </fieldset>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        Safe handover notes
                        <textarea name="meetup_notes" maxlength="1000" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('meetup_notes') }}</textarea>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Return or cancellation policy
                        <textarea name="return_policy" maxlength="2000" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('return_policy') }}</textarea>
                    </label>
                </div>
            </section>

            <section class="market-safety">
                <x-lucide-shield-check class="size-6 shrink-0" aria-hidden="true" />
                <label class="flex gap-3 text-sm leading-6">
                    <input type="checkbox" name="safety_acknowledged" value="1" required @checked(old('safety_acknowledged')) class="mt-1 size-4 shrink-0 rounded border-paw-line text-paw-leaf">
                    <span>I disclosed known defects, omitted exact private addresses, and will keep payment and verification codes out of messages.</span>
                </label>
            </section>

            <div class="flex flex-col-reverse gap-2 border-t border-paw-line pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('marketplace.index') }}" class="action action--surface">Cancel</a>
                <button type="submit" name="intent" value="draft" class="action action--surface">
                    <x-lucide-file-pen-line class="icon" aria-hidden="true" />
                    <span>Save draft</span>
                </button>
                <button type="submit" name="intent" value="publish" class="action action--primary">
                    <x-lucide-send class="icon" aria-hidden="true" />
                    <span>Submit listing</span>
                </button>
            </div>
        </form>
    </div>
</x-app-shell>
