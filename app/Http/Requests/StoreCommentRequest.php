<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'answer_id' => ['required', 'integer', 'exists:forum_answers,id'],
            'parent_id' => ['nullable', 'integer', 'exists:forum_comments,id'],
            'body' => ['required', 'string', 'min:2', 'max:1500'],
        ];
    }
}
