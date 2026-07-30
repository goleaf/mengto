<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BrowseConnectionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tab' => ['nullable', Rule::in(['following', 'followers', 'requests', 'recommendations'])],
            'type' => [
                'nullable',
                Rule::in(['all', 'people', 'pets', 'organizations', 'specialists', 'groups', 'topics']),
            ],
            'sort' => ['nullable', Rule::in(['recommended', 'recent', 'name'])],
        ];
    }
}
