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
            $blocked[] = 'This marketplace does not allow unverified medicines, biological material, or wildlife trading.';
            $flags[] = 'prohibited-product';
        }

        if (Str::contains($text, [
            'guaranteed cure',
            'cures all diseases',
            'replaces a veterinarian',
            'no veterinarian needed',
            '100% safe treatment',
        ])) {
            $blocked[] = 'Remove medical guarantees or claims that a product replaces veterinary care.';
            $flags[] = 'medical-claim';
        }

        if (Str::contains($text, [
            'electric shock collar',
            'pain training device',
            'choking punishment',
        ])) {
            $blocked[] = 'Devices intended to cause pain or cruel punishment are prohibited.';
            $flags[] = 'animal-welfare';
        }

        if (($data['category'] ?? null) === 'food'
            && ! (bool) ($data['sealed_package'] ?? false)
            && ($data['condition'] ?? null) !== 'new') {
            $blocked[] = 'Food must be sealed, clearly dated, and in new condition.';
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
