<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\PetWorkspaceFilter;
use App\Enums\PetWorkspaceSort;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class BrowsePetProfilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isActive() === true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:80'],
            'filter' => ['nullable', Rule::enum(PetWorkspaceFilter::class)],
            'sort' => ['nullable', Rule::enum(PetWorkspaceSort::class)],
            'page' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ];
    }
}
