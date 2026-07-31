<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class PhotoInteractionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $action = (string) $this->input('action');

        return [
            'action' => ['required', 'string', Rule::in(['set-reaction', 'create-comment'])],
            'photo' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9-]+$/'],
            'reaction' => [
                Rule::requiredIf($action === 'set-reaction'),
                Rule::prohibitedIf($action !== 'set-reaction'),
                'nullable',
                Rule::in(['like', 'love', 'funny', 'support', 'useful']),
            ],
            'body' => [
                Rule::requiredIf($action === 'create-comment'),
                Rule::prohibitedIf($action !== 'create-comment'),
                'nullable',
                'string',
                'max:1200',
            ],
            'idempotency_key' => [
                Rule::requiredIf($action === 'create-comment'),
                Rule::prohibitedIf($action !== 'create-comment'),
                'nullable',
                'ulid',
            ],
        ];
    }
}
