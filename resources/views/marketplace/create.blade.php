<x-app-shell :owner="$owner" :title="$page_title" :active-section="$active_section">
    <div class="mx-auto grid max-w-5xl gap-6">
        <x-page-header
            :eyebrow="__('ui.new_marketplace_listing_e6c98b5c48')"
            :title="__('ui.create_a_structured_offer_1feacd39c7')"
            :description="__('ui.disclose_measurements_defects_availability_handover_terms_and_the_ece5ea6b60')"
            heading-id="create-marketplace-listing-heading"
            :action-label="__('ui.marketplace_c608981d8d')"
            action-icon="arrow-left"
            :action-href="route('marketplace.index')"
            action-variant="paper"
            data-section="marketplace-create-header"
        />

        @if ($errors->any())
            <div class="form-errors" role="alert">
                <x-ui-icon name="circle-alert" />
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('marketplace.store') }}" enctype="multipart/form-data" class="grid gap-8">
            @csrf

            <section aria-labelledby="offer-heading">
                <h2 id="offer-heading" class="text-2xl font-bold">{{ __('ui.offer_0cf57c63eb') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.listing_type_329627e862') }}
                        <select name="type" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', 'sale') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_types_available_26b37288d5') }}</option>
                            @endforelse
                        </select>
                        @error('type') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.category_292c06f004') }}
                        <select name="category" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(old('category', 'walking-gear') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_categories_available_12969fffdd') }}</option>
                            @endforelse
                        </select>
                        @error('category') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.seller_type_13685f8f8c') }}
                        <select name="seller_type" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($seller_types as $value => $label)
                                <option value="{{ $value }}" @selected(old('seller_type', 'private') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_seller_types_available_ef7907b088') }}</option>
                            @endforelse
                        </select>
                        @error('seller_type') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold lg:col-span-2">
                        {{ __('ui.title_7e8cd2056d') }}
                        <input name="title" value="{{ old('title') }}" required minlength="12" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.atlas_40_carrier_for_a_cat_good_condition_8b5a9eda50') }}">
                        @error('title') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.business_or_organization_4fe4d99ef7') }}
                        <input name="business_name" value="{{ old('business_name') }}" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.required_outside_private_sales_fe4506b5f5') }}">
                        @error('business_name') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2 lg:col-span-3">
                        {{ __('ui.description_526e0087cc') }}
                        <textarea name="description" required minlength="50" maxlength="5000" rows="6" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.describe_the_real_item_or_service_included_parts_adb0a1e0de') }}">{{ old('description') }}</textarea>
                        @error('description') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                </div>
            </section>

            <section aria-labelledby="identity-heading" class="border-t border-paw-line pt-7">
                <h2 id="identity-heading" class="text-2xl font-bold">{{ __('ui.identity_and_condition_a0b8a46157') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.brand_090ed4316f') }}
                        <input name="brand" value="{{ old('brand') }}" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.model_5e2c614c23') }}
                        <input name="model" value="{{ old('model') }}" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.material_616cb218a6') }}
                        <input name="material" value="{{ old('material') }}" maxlength="120" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.condition_39b36d38d6') }}
                        <select name="condition" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($conditions as $value => $label)
                                <option value="{{ $value }}" @selected(old('condition', 'good') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_condition_options_641304cebc') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2 lg:col-span-4">
                        {{ __('ui.defects_and_repairs_0109eec235') }}
                        <textarea name="defects" maxlength="2000" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.list_scratches_cracks_missing_parts_battery_wear_stains_6f11f8a093') }}">{{ old('defects') }}</textarea>
                        @error('defects') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.hygiene_status_7b0cfc2dc9') }}
                        <select name="hygiene_status" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            <option value="">{{ __('ui.not_specified_dc12bec5d7') }}</option>
                            @forelse ($hygiene_statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('hygiene_status') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_hygiene_options_0dc9bd44d9') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="flex items-center gap-3 self-end rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                        <input type="checkbox" name="sealed_package" value="1" @checked(old('sealed_package')) class="size-4 rounded border-paw-line text-paw-leaf">
                        {{ __('ui.package_is_sealed_d105ef7318') }}
                    </label>
                </div>
            </section>

            <section aria-labelledby="commercial-heading" class="border-t border-paw-line pt-7">
                <h2 id="commercial-heading" class="text-2xl font-bold">{{ __('ui.price_and_availability_9d0a285939') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.price_93c91c851e') }}
                        <span class="flex rounded-md border border-paw-line bg-white">
                            <span class="grid w-11 place-items-center border-r border-paw-line font-bold text-paw-muted">€</span>
                            <input type="number" name="price" value="{{ old('price') }}" min="0" max="999999.99" step="0.01" class="min-w-0 flex-1 border-0 bg-transparent px-3 py-2.5 outline-none">
                        </span>
                        @error('price') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <input type="hidden" name="currency" value="EUR">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.quantity_822bab8d41') }}
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" max="100000" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.availability_12f67f8539') }}
                        <select name="availability" required class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($availability_options as $value => $label)
                                <option value="{{ $value }}" @selected(old('availability', 'in-stock') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_availability_options_9f0f7c913f') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="flex items-center gap-3 self-end rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                        <input type="checkbox" name="is_free" value="1" @checked(old('is_free')) class="size-4 rounded border-paw-line text-paw-leaf">
                        {{ __('ui.free_handover_082327a58b') }}
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2 lg:col-span-4">
                        {{ __('ui.exchange_preference_04a57bf45e') }}
                        <textarea name="exchange_preferences" maxlength="1000" rows="2" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('exchange_preferences') }}</textarea>
                    </label>
                </div>
            </section>

            <section aria-labelledby="fit-heading" class="border-t border-paw-line pt-7">
                <h2 id="fit-heading" class="text-2xl font-bold">{{ __('ui.pet_fit_and_measurements_4b98c93485') }}</h2>
                <fieldset class="mt-4">
                    <legend class="text-sm font-semibold">{{ __('ui.suitable_pet_types_ce6e18ca0f') }}</legend>
                    <div class="mt-2 grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
                        @forelse ($species_options as $value => $label)
                            <label class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                                <input type="checkbox" name="species[]" value="{{ $value }}" @checked(in_array($value, old('species', ['dog']), true)) class="size-4 rounded border-paw-line text-paw-leaf">
                                {{ $label }}
                            </label>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_pet_types_available_dcd00ea2c2') }}</p>
                        @endforelse
                    </div>
                </fieldset>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.pet_size_8458af913c') }}
                        <select name="pet_size" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse (['any' => __('ui.any_size_9f46b4f2f6'), 'small' => __('ui.small_5263293fc2'), 'medium' => __('ui.medium_8e588cd187'), 'large' => __('ui.large_ab80540d98')] as $value => $label)
                                <option value="{{ $value }}" @selected(old('pet_size', 'any') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_size_options_60cfd1979a') }}</option>
                            @endforelse
                        </select>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.age_group_b26d964d1c') }}
                        <select name="age_group" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                            @forelse ($age_groups as $value => $label)
                                <option value="{{ $value }}" @selected(old('age_group', 'all') === $value)>{{ $label }}</option>
                            @empty
                                <option disabled>{{ __('ui.no_age_groups_d4614e15df') }}</option>
                            @endforelse
                        </select>
                    </label>
                    @forelse ([
                        'length_cm' => __('ui.length_cm_b3211ae1c6'),
                        'width_cm' => __('ui.width_cm_2e8f7f3953'),
                        'height_cm' => __('ui.height_cm_5cfbb1811a'),
                        'max_weight_kg' => __('ui.maximum_weight_kg_1a65ab238f'),
                    ] as $field => $label)
                        <label class="grid gap-1 text-sm font-semibold">
                            {{ $label }}
                            <input type="number" name="{{ $field }}" value="{{ old($field) }}" min="0.1" step="0.1" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        </label>
                    @empty
                        <p class="text-sm text-paw-muted">{{ __('ui.no_measurement_fields_7e405adffa') }}</p>
                    @endforelse
                </div>
            </section>

            <section aria-labelledby="special-heading" class="border-t border-paw-line pt-7">
                <h2 id="special-heading" class="text-2xl font-bold">{{ __('ui.type_specific_details_b156af2d67') }}</h2>
                <div class="mt-4 grid gap-6 lg:grid-cols-2">
                    <fieldset class="market-fieldset">
                        <legend>{{ __('ui.rental_dbd6652e88') }}</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label>{{ __('ui.rate_unit_d108b4c6de') }}
                                <select name="rental_rate_unit">
                                    <option value="">{{ __('ui.not_a_rental_e6d4a9ca31') }}</option>
                                    <option value="day" @selected(old('rental_rate_unit') === 'day')>{{ __('ui.per_day_5b9e193b00') }}</option>
                                    <option value="week" @selected(old('rental_rate_unit') === 'week')>{{ __('ui.per_week_9e2aef7283') }}</option>
                                </select>
                            </label>
                            <label>{{ __('ui.deposit_0da00b600a') }}
                                <input type="number" name="deposit_amount" value="{{ old('deposit_amount') }}" min="0" step="0.01">
                            </label>
                            <label>{{ __('ui.available_from_6956ec13a7') }}
                                <input type="date" name="available_from" value="{{ old('available_from') }}">
                            </label>
                            <label>{{ __('ui.available_until_64c3257d27') }}
                                <input type="date" name="available_until" value="{{ old('available_until') }}">
                            </label>
                            <label>{{ __('ui.minimum_days_0eedfd4d2b') }}
                                <input type="number" name="minimum_days" value="{{ old('minimum_days', 1) }}" min="1" max="365">
                            </label>
                            <label>{{ __('ui.maximum_days_e1344648bd') }}
                                <input type="number" name="maximum_days" value="{{ old('maximum_days') }}" min="1" max="365">
                            </label>
                        </div>
                    </fieldset>

                    <fieldset class="market-fieldset">
                        <legend>{{ __('ui.service_d677190e0a') }}</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label>{{ __('ui.duration_minutes_c7d487ea6a') }}
                                <input type="number" name="service_duration_minutes" value="{{ old('service_duration_minutes') }}" min="15" max="1440">
                            </label>
                            <label>{{ __('ui.format_2f343666aa') }}
                                <select name="service_format">
                                    <option value="">{{ __('ui.not_a_service_43beae6de5') }}</option>
                                    @forelse (['in-person' => __('ui.in_person_5cf02dbb1e'), 'online' => __('ui.online_0d21bd5202'), 'home-visit' => __('ui.home_visit_a66655882a'), 'group' => __('ui.group_34ca0e7660')] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('service_format') === $value)>{{ $label }}</option>
                                    @empty
                                        <option disabled>{{ __('ui.no_formats_56383a7a25') }}</option>
                                    @endforelse
                                </select>
                            </label>
                        </div>
                    </fieldset>

                    <fieldset class="market-fieldset">
                        <legend>{{ __('ui.shelter_need_af8235e967') }}</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label>{{ __('ui.priority_d60dbba079') }}
                                <select name="urgency">
                                    <option value="">{{ __('ui.not_a_shelter_request_8af9890c2c') }}</option>
                                    @forelse (['urgent' => __('ui.urgent_1b015904cc'), 'important' => __('ui.important_ddca9a57e6'), 'regular' => __('ui.regular_need_7968cff83d'), 'wish' => __('ui.wish_3ab8a88114')] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('urgency') === $value)>{{ $label }}</option>
                                    @empty
                                        <option disabled>{{ __('ui.no_priorities_ef7a962dd3') }}</option>
                                    @endforelse
                                </select>
                            </label>
                            <label>{{ __('ui.already_received_6b565ae3ec') }}
                                <input type="number" name="received_quantity" value="{{ old('received_quantity', 0) }}" min="0">
                            </label>
                            <label>{{ __('ui.needed_by_fd35647f8e') }}
                                <input type="date" name="needed_by" value="{{ old('needed_by') }}">
                            </label>
                        </div>
                    </fieldset>

                    <fieldset class="market-fieldset">
                        <legend>{{ __('ui.adoption_profile_6c7cd4fe9a') }}</legend>
                        <div class="grid gap-3 sm:grid-cols-2">
                            <label>{{ __('ui.animal_name_875b1fd52c') }}
                                <input name="animal_name" value="{{ old('animal_name') }}" maxlength="100">
                            </label>
                            <label>{{ __('ui.age_39b7370f30') }}
                                <input name="animal_age" value="{{ old('animal_age') }}" maxlength="80">
                            </label>
                            <label>{{ __('ui.sex_953dd6f2b4') }}
                                <select name="animal_sex">
                                    <option value="">{{ __('ui.not_specified_dc12bec5d7') }}</option>
                                    <option value="female" @selected(old('animal_sex') === 'female')>{{ __('ui.female_e8cca808ae') }}</option>
                                    <option value="male" @selected(old('animal_sex') === 'male')>{{ __('ui.male_03f8c1273e') }}</option>
                                    <option value="unknown" @selected(old('animal_sex') === 'unknown')>{{ __('ui.unknown_b764cdc0ea') }}</option>
                                </select>
                            </label>
                            <label class="sm:col-span-2">{{ __('ui.temperament_and_important_needs_51f853ce6d') }}
                                <textarea name="temperament" rows="3" maxlength="1500">{{ old('temperament') }}</textarea>
                            </label>
                            <label class="sm:col-span-2">{{ __('ui.adoption_conditions_d70d86dbd3') }}
                                <textarea name="adoption_conditions" rows="3" maxlength="2000">{{ old('adoption_conditions') }}</textarea>
                            </label>
                        </div>
                    </fieldset>
                </div>
            </section>

            <section aria-labelledby="media-heading" class="border-t border-paw-line pt-7">
                <h2 id="media-heading" class="text-2xl font-bold">{{ __('ui.media_d357175cfe') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.photos_5e3147ab51') }}
                        <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @error('photos.*') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.demonstration_video_3f61839a5e') }}
                        <input type="file" name="video" accept="video/mp4,video/quicktime,video/webm" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                        @error('video') <span class="form-field__error">{{ $message }}</span> @enderror
                    </label>
                    <label class="grid gap-1 text-sm font-semibold sm:col-span-2">
                        {{ __('ui.existing_cover_url_bce5b4641e') }}
                        <input type="url" name="cover_url" value="{{ old('cover_url') }}" class="rounded-md border border-paw-line bg-white px-3 py-2.5" placeholder="{{ __('ui.https_66dfeeedab') }}">
                    </label>
                </div>
            </section>

            <section aria-labelledby="handover-heading" class="border-t border-paw-line pt-7">
                <h2 id="handover-heading" class="text-2xl font-bold">{{ __('ui.handover_and_returns_69985435da') }}</h2>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.city_fc33f73246') }}
                        <input name="city" value="{{ old('city', __('ui.vilnius_c283e0869a')) }}" required maxlength="80" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.approximate_area_21f7516dc7') }}
                        <input name="area" value="{{ old('area') }}" maxlength="100" class="rounded-md border border-paw-line bg-white px-3 py-2.5">
                    </label>
                </div>
                <fieldset class="mt-4">
                    <legend class="text-sm font-semibold">{{ __('ui.available_handover_options_f25ec0a337') }}</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        @forelse ($delivery_options as $value => $label)
                            <label class="flex items-center gap-2 rounded-md border border-paw-line bg-white px-3 py-2.5 text-sm font-semibold">
                                <input type="checkbox" name="delivery_options[]" value="{{ $value }}" @checked(in_array($value, old('delivery_options', ['meetup']), true)) class="size-4 rounded border-paw-line text-paw-leaf">
                                {{ $label }}
                            </label>
                        @empty
                            <p class="text-sm text-paw-muted">{{ __('ui.no_handover_options_271d7ac31a') }}</p>
                        @endforelse
                    </div>
                </fieldset>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.safe_handover_notes_c92af47176') }}
                        <textarea name="meetup_notes" maxlength="1000" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('meetup_notes') }}</textarea>
                    </label>
                    <label class="grid gap-1 text-sm font-semibold">
                        {{ __('ui.return_or_cancellation_policy_5e1de48db8') }}
                        <textarea name="return_policy" maxlength="2000" rows="3" class="rounded-md border border-paw-line bg-white px-3 py-2.5">{{ old('return_policy') }}</textarea>
                    </label>
                </div>
            </section>

            <section class="market-safety">
                <x-ui-icon name="shield-check" size="xl" class="shrink-0" />
                <label class="flex gap-3 text-sm leading-6">
                    <input type="checkbox" name="safety_acknowledged" value="1" required @checked(old('safety_acknowledged')) class="mt-1 size-4 shrink-0 rounded border-paw-line text-paw-leaf">
                    <span>{{ __('ui.i_disclosed_known_defects_omitted_exact_private_addresses_13b6980af5') }}</span>
                </label>
            </section>

            <div class="flex flex-col-reverse gap-2 border-t border-paw-line pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('marketplace.index') }}" class="action action--surface">
                    <x-ui-icon name="x" />
                    <span>{{ __('ui.cancel_19766ed6cc') }}</span>
                </a>
                <button type="submit" name="intent" value="draft" class="action action--surface">
                    <x-ui-icon name="file-pen-line" />
                    <span>{{ __('ui.save_draft_3de100106d') }}</span>
                </button>
                <button type="submit" name="intent" value="publish" class="action action--primary">
                    <x-ui-icon name="send" />
                    <span>{{ __('ui.submit_listing_525f0ca85f') }}</span>
                </button>
            </div>
        </form>
    </div>
</x-app-shell>
