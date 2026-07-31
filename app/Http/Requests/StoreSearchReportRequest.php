<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Services\ForumReportReasonCatalog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSearchReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(ForumReportReasonCatalog $reasons): array
    {
        return [
            'sighting_id' => ['nullable', 'integer', 'exists:sightings,id'],
            'reason' => ['required', Rule::in($reasons->acceptedInputKeys())],
            'details' => ['nullable', 'string', 'max:2500'],
            'truthfulness_confirmed' => ['required', 'accepted'],
            'immediate_safety' => ['nullable', 'boolean'],
        ];
    }
}
