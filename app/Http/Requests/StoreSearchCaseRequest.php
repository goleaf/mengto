<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SearchCaseType;
use App\Models\DomesticClassification;
use App\Models\PetProfile;
use App\Models\SearchCase;
use App\Models\User;
use App\Services\SearchSafety;
use App\Services\SearchTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

final class StoreSearchCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', SearchCase::class) === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(SearchTaxonomy $taxonomy): array
    {
        return [
            'type' => ['required', Rule::enum(SearchCaseType::class)],
            'intent' => ['required', Rule::in(['publish', 'draft'])],
            'pet_profile_key' => ['nullable', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'pet_profile_id' => ['nullable', 'integer', 'exists:pet_profiles,id'],
            'taxon_id' => [
                'nullable',
                'integer',
                'required_with:domestic_classification_id',
                'exists:taxa,id',
            ],
            'domestic_classification_id' => [
                'nullable',
                'integer',
                'exists:domestic_classifications,id',
            ],
            'pet_name' => ['required', 'string', 'max:100'],
            'species' => ['required', Rule::in(array_keys($taxonomy->species()))],
            'breed' => ['nullable', 'string', 'max:120'],
            'sex' => ['nullable', Rule::in(['male', 'female', 'unknown'])],
            'age_label' => ['nullable', 'string', 'max:80'],
            'size' => ['nullable', Rule::in(array_keys($taxonomy->sizes()))],
            'primary_color' => ['required', 'string', 'max:80'],
            'coat' => ['nullable', 'string', 'max:80'],
            'distinctive_marks' => ['nullable', 'string', 'max:1500'],
            'hidden_marks' => ['nullable', 'string', 'max:1000'],
            'description' => ['required', 'string', 'min:20', 'max:4000'],
            'health_notice' => ['nullable', 'string', 'max:1000'],
            'approach_instructions' => ['nullable', 'string', 'max:1500'],
            'avoid_instructions' => ['nullable', 'string', 'max:1500'],
            'accessories' => ['nullable', 'array', 'max:8'],
            'accessories.*' => ['string', 'max:80'],
            'temperament' => ['nullable', 'string', 'max:300'],
            'microchip_status' => ['required', Rule::in(array_keys($taxonomy->microchipStatuses()))],
            'last_seen_area' => ['required', 'string', 'max:160'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'size:2'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_note' => ['nullable', 'string', 'max:300'],
            'direction' => ['nullable', 'string', 'max:100'],
            'last_seen_at' => ['required', 'date', 'before_or_equal:now'],
            'notification_radius_km' => ['required', 'integer', 'min:1', 'max:100'],
            'visibility' => ['required', Rule::in(['public', 'registered', 'local-group', 'link'])],
            'animal_secured' => ['nullable', 'boolean'],
            'contact_channel' => ['required', Rule::in(['platform', 'email', 'phone'])],
            'contact_value' => ['nullable', 'string', 'max:160'],
            'reward_offered' => ['nullable', 'boolean'],
            'reward_summary' => ['nullable', 'string', 'max:300', 'required_if_accepted:reward_offered'],
            'cover_url' => ['nullable', 'url:http,https', 'max:2048'],
            'photos' => ['nullable', 'array', 'max:8'],
            'photos.*' => [
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
                'dimensions:min_width=32,min_height=32,max_width=12000,max_height=12000',
            ],
            'safety_acknowledged' => ['required', 'accepted'],
        ];
    }

    public function after(SearchSafety $safety): array
    {
        return [
            function (Validator $validator) use ($safety): void {
                $type = $this->string('type')->toString();

                if (
                    in_array($type, [SearchCaseType::Lost->value, SearchCaseType::Stolen->value], true)
                    && blank($this->input('pet_profile_key'))
                    && blank($this->input('pet_profile_id'))
                ) {
                    $validator->errors()->add('pet_profile_key', __('messages.choose_the_pet_profile_for_a_missing_pet_search_b22b0b96d2'));
                }

                $user = $this->user();
                $petProfileId = $this->integer('pet_profile_id');
                $petProfileKey = $this->string('pet_profile_key')->toString();

                if (
                    $petProfileId > 0
                    && $user instanceof User
                    && ! PetProfile::query()
                        ->whereKey($petProfileId)
                        ->where('user_id', $user->id)
                        ->where('status', 'active')
                        ->exists()
                ) {
                    $validator->errors()->add('pet_profile_id', __('lost_found.validation.pet_ownership'));
                }

                if (
                    $petProfileKey !== ''
                    && $user instanceof User
                    && ! PetProfile::query()
                        ->where('user_id', $user->id)
                        ->where('status', 'active')
                        ->where(
                            fn ($profiles) => $profiles
                                ->where('profile_key', $petProfileKey)
                                ->orWhere('slug', $petProfileKey),
                        )
                        ->exists()
                ) {
                    $validator->errors()->add('pet_profile_key', __('lost_found.validation.pet_ownership'));
                }

                $classificationId = $this->integer('domestic_classification_id');
                $taxonId = $this->integer('taxon_id');

                if (
                    $classificationId > 0
                    && ! DomesticClassification::query()
                        ->whereKey($classificationId)
                        ->where('taxon_id', $taxonId)
                        ->where('is_active', true)
                        ->exists()
                ) {
                    $validator->errors()->add(
                        'domestic_classification_id',
                        __('lost_found.validation.taxonomy_relation'),
                    );
                }

                if ($this->string('contact_channel')->toString() !== 'platform' && blank($this->input('contact_value'))) {
                    $validator->errors()->add('contact_value', __('messages.add_the_protected_contact_value_aa102be0b3'));
                }

                if (! $safety->rewardSummaryIsSafe($this->string('reward_summary')->toString())) {
                    $validator->errors()->add(
                        'reward_summary',
                        __('lost_found.validation.reward_safety'),
                    );
                }

                $assessment = $safety->assessCase($this->all());
                foreach ($assessment['flags'] as $flag) {
                    if (in_array($flag, ['sensitive-payment-data', 'threat-language'], true)) {
                        $validator->errors()->add(
                            'description',
                            __('messages.remove_payment_codes_threats_or_other_unsafe_details_bef_76f50669e4'),
                        );
                    }
                }
            },
        ];
    }
}
