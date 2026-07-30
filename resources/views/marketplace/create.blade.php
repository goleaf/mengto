<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-4xl gap-6">
        <a href="{{ route('marketplace.index') }}" class="inline-flex w-fit items-center gap-2 text-sm font-bold text-paw-leaf">
            <x-lucide-arrow-left class="size-4" aria-hidden="true" />
            Marketplace
        </a>

        <header class="border-b border-paw-line pb-6">
            <p class="text-sm font-bold uppercase text-paw-leaf">New marketplace listing</p>
            <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Describe exactly what you offer</h1>
            <p class="mt-3 max-w-2xl leading-7 text-paw-muted">Clear condition, pet fit, price, and handover details reduce unsafe private negotiation.</p>
        </header>

        @if ($errors->any())
            <div class="form-errors" role="alert">
                <x-lucide-circle-alert class="icon" aria-hidden="true" />
                <span>Please review the highlighted fields. {{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('marketplace.store') }}" class="grid gap-8">
            @csrf

            <section aria-labelledby="offer-heading">
                <h2 id="offer-heading" class="text-2xl font-bold">Offer</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        Listing type
                        <select name="type" required class="rounded-md border border-paw-line bg-white px-3 py-2.5" aria-invalid="{{ $errors->has('type') ? 'true' : 'false' }}">
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
                        <select name="category" required class="rounded-md border border-paw-line bg-white px-3 py-2.5" aria-invalid="{{ $errors->has('category') ? 'true' : 'false' }}">
                            @forelse ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>No categories available</option>
                            @endforelse
                        </select>
                        @error('category') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2">
                        Title
                        <input name="title" value="{{ old('title') }}" required minlength="12" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Reflective harness for a medium dog" aria-invalid="{{ $errors->has('title') ? 'true' : 'false' }}">
                        @error('title') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2">
                        Description
                        <textarea name="description" required minlength="50" maxlength="5000" rows="7" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Describe condition, measurements, what is included, and anything the other person should verify." aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}">{{ old('description') }}</textarea>
                        @error('description') <span class="form-field__error">{{ $message }}</span> @enderror
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
                    <label class="grid gap-1 text-sm font-semibold">
                        Cover photo URL, optional
                        <input type="url" name="cover_url" value="{{ old('cover_url') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="https://">
                        @error('cover_url') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                </div>
            </section>

            <section aria-labelledby="price-heading" class="border-t border-paw-line pt-7">
                <h2 id="price-heading" class="text-2xl font-bold">Price or exchange</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        Price
                        <span class="flex rounded-md border border-paw-line bg-white">
                            <span class="grid w-11 place-items-center border-r border-paw-line font-bold text-paw-muted">€</span>
                            <input type="number" name="price" value="{{ old('price') }}" min="0" max="999999.99" step="0.01" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 outline-none">
                        </span>
                        @error('price') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <input type="hidden" name="currency" value="EUR">
                    <label class="flex items-center gap-3 self-end rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                        <input type="checkbox" name="is_free" value="1" @checked(old('is_free')) class="size-4 rounded border-paw-line text-paw-leaf">
                        This listing is free
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2">
                        Exchange preference, optional
                        <textarea name="exchange_preferences" maxlength="1000" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="What would be a fair exchange?">{{ old('exchange_preferences') }}</textarea>
                    </label>
                </div>
            </section>

            <section aria-labelledby="fit-heading" class="border-t border-paw-line pt-7">
                <h2 id="fit-heading" class="text-2xl font-bold">Pet fit</h2>
                <fieldset class="mt-4">
                    <legend class="text-sm font-semibold">Suitable pet types</legend>
                    <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-4">
                        @forelse ($species_options as $value => $label)
                            <label class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                                <input type="checkbox" name="species[]" value="{{ $value }}" @checked(in_array($value, old('species', ['dog']), true)) class="size-4 rounded border-paw-line text-paw-leaf">
                                {{ $label }}
                            </label>
                        @empty
                            <p class="text-sm text-paw-muted">No pet types available.</p>
                        @endforelse
                    </div>
                    @error('species') <span class="mt-2 block form-field__error">{{ $message }}</span> @enderror
                </fieldset>
                <label class="mt-4 grid gap-1 text-sm font-semibold sm:max-w-xs">
                    Pet size
                    <select name="pet_size" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @forelse (['any' => 'Any size', 'small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('pet_size', 'any') === $value)>{{ $label }}</option>
                        @empty
                            <option disabled>No size options</option>
                        @endforelse
                    </select>
                </label>
            </section>

            <section aria-labelledby="handover-heading" class="border-t border-paw-line pt-7">
                <h2 id="handover-heading" class="text-2xl font-bold">Area and handover</h2>
                <p class="mt-1 text-sm text-paw-muted">Use an approximate area. Do not publish a home address.</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        City
                        <input name="city" value="{{ old('city', 'Vilnius') }}" required maxlength="80" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @error('city') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Area, optional
                        <input name="area" value="{{ old('area') }}" maxlength="100" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Naujamiestis">
                    </label>
                </div>
                <fieldset class="mt-4">
                    <legend class="text-sm font-semibold">Available handover options</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2">
                        @forelse ($delivery_options as $value => $label)
                            <label class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                                <input type="checkbox" name="delivery_options[]" value="{{ $value }}" @checked(in_array($value, old('delivery_options', ['meetup']), true)) class="size-4 rounded border-paw-line text-paw-leaf">
                                {{ $label }}
                            </label>
                        @empty
                            <p class="text-sm text-paw-muted">No handover options available.</p>
                        @endforelse
                    </div>
                    @error('delivery_options') <span class="mt-2 block form-field__error">{{ $message }}</span> @enderror
                </fieldset>
                <label class="mt-4 grid gap-1 text-sm font-semibold">
                    Safe handover notes, optional
                    <textarea name="meetup_notes" maxlength="1000" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="Suggest a public place, daylight hours, or an online delivery process.">{{ old('meetup_notes') }}</textarea>
                </label>
            </section>

            <section aria-labelledby="identity-heading" class="border-t border-paw-line pt-7">
                <h2 id="identity-heading" class="text-2xl font-bold">Seller context</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="flex items-center gap-3 rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                        <input type="checkbox" name="is_business" value="1" @checked(old('is_business')) class="size-4 rounded border-paw-line text-paw-leaf">
                        This is a business listing
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        Business name, when applicable
                        <input name="business_name" value="{{ old('business_name') }}" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @error('business_name') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                </div>
            </section>

            <section class="market-safety">
                <x-lucide-shield-check class="size-6 shrink-0" aria-hidden="true" />
                <label class="flex gap-3 text-sm leading-6">
                    <input type="checkbox" name="safety_acknowledged" value="1" required @checked(old('safety_acknowledged')) class="mt-1 size-4 shrink-0 rounded border-paw-line text-paw-leaf">
                    <span>I will keep initial contact inside the platform, avoid exact home addresses, disclose defects, and will not ask anyone for a password, card number, or verification code.</span>
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
                    <span>Publish listing</span>
                </button>
            </div>
        </form>
    </div>
</x-app-shell>
