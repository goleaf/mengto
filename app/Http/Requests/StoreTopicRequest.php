<?php

namespace App\Http\Requests;

use App\Services\ForumTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;

class StoreTopicRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $taxonomy = app(ForumTaxonomy::class);

        return [
            'type' => ['required', Rule::in(array_keys($taxonomy->typeOptions()))],
            'category' => ['required', Rule::in(array_keys($taxonomy->categoryOptions()))],
            'subcategory' => ['nullable', 'string', 'max:80'],
            'pet_key' => ['nullable', Rule::in(array_keys($taxonomy->petOptions()))],
            'title' => ['required', 'string', 'min:20', 'max:180'],
            'body' => ['required', 'string', 'min:60', 'max:10000'],
            'tried' => ['nullable', 'string', 'max:2500'],
            'desired_answer' => ['nullable', Rule::in(array_keys($taxonomy->desiredAnswerOptions()))],
            'tags' => ['nullable', 'string', 'max:300'],
            'location' => ['nullable', 'string', 'max:120'],
            'visibility' => ['required', Rule::in(array_keys($taxonomy->visibilityOptions()))],
            'comment_policy' => ['required', Rule::in(array_keys($taxonomy->commentPolicyOptions()))],
            'language' => ['required', Rule::in(['en', 'lt', 'ru'])],
            'intent' => ['required', Rule::in(['draft', 'publish'])],
            'is_urgent' => ['nullable', 'boolean'],
            'is_medical' => ['nullable', 'boolean'],
            'veterinary_status' => ['nullable', Rule::in(['seen', 'not-seen', 'scheduled', 'unavailable', 'not-medical'])],
            'photos' => ['nullable', 'array', 'max:4'],
            'photos.*' => ['file', File::image()->types(['jpg', 'jpeg', 'png', 'webp'])->max('4mb')],
            'photo_alt' => ['nullable', 'string', 'max:240'],
            'video' => ['nullable', 'file', File::types(['mp4', 'webm', 'mov'])->max('20mb')],
            'sensitive_media' => ['nullable', 'boolean'],
        ];
    }
}
