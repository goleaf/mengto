<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSearchContactRelayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'uuid'],
            'purpose' => [
                'required',
                Rule::in(['sighting', 'identity-evidence', 'safe-custody', 'safety', 'other']),
            ],
            'message' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }
}
