<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class BrowseContentFeedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'cursor' => ['nullable', 'string', 'max:500'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:25'],
        ];
    }
}
