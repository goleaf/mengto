<?php

namespace App\Services;

use App\Enums\ListingType;
use App\Enums\SellerType;
use Illuminate\Support\Str;

class ListingSafety
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{blocked: list<string>, flags: list<string>, manual_review: bool}
     */
    public function assess(array $data): array
    {
        $text = Str::lower(implode(' ', array_filter([
            $data['title'] ?? null,
            $data['description'] ?? null,
            $data['brand'] ?? null,
            $data['model'] ?? null,
        ])));

        $blocked = [];
        $flags = [];

        if (Str::contains($text, [
            'prescription medicine',
            'human medication',
            'used needle',
            'animal vaccine',
            'blood sample',
            'wild animal for sale',
        ])) {
            $blocked[] = __('messages.this_marketplace_does_not_allow_unverified_medicines_biological_material_or_wildlife_trading');
            $flags[] = 'prohibited-product';
        }

        if (Str::contains($text, [
            'guaranteed cure',
            'cures all diseases',
            'replaces a veterinarian',
            'no veterinarian needed',
            '100% safe treatment',
        ])) {
            $blocked[] = __('messages.remove_medical_guarantees_or_claims_that_a_product_replaces_veterinary_care');
            $flags[] = 'medical-claim';
        }

        if (Str::contains($text, [
            'electric shock collar',
            'pain training device',
            'choking punishment',
        ])) {
            $blocked[] = __('messages.devices_intended_to_cause_pain_or_cruel_punishment_are_prohibited');
            $flags[] = 'animal-welfare';
        }

        if (($data['category'] ?? null) === 'food'
            && ! (bool) ($data['sealed_package'] ?? false)
            && ($data['condition'] ?? null) !== 'new') {
            $blocked[] = __('messages.food_must_be_sealed_clearly_dated_and_in_new_condition');
            $flags[] = 'open-food';
        }

        $type = ListingType::tryFrom((string) ($data['type'] ?? ''));
        $sellerType = SellerType::tryFrom((string) ($data['seller_type'] ?? 'private'));

        if ($type?->requiresManualReview()) {
            $flags[] = $type === ListingType::Adoption ? 'adoption-review' : 'shelter-verification';
        }

        if ($sellerType?->requiresVerification()) {
            $flags[] = 'seller-verification';
        }

        if (in_array($data['category'] ?? null, ['food', 'rehabilitation', 'professional-service'], true)) {
            $flags[] = 'safety-sensitive-category';
        }

        $flags = array_values(array_unique($flags));

        return [
            'blocked' => array_values(array_unique($blocked)),
            'flags' => $flags,
            'manual_review' => $flags !== [],
        ];
    }
}
