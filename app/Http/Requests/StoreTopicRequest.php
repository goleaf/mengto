<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Rules\ValidWebVtt;
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
    public function rules(ForumTaxonomy $taxonomy): array
    {
        return [
            'type' => ['required', Rule::in(array_keys($taxonomy->typeOptions()))],
            'category' => ['required', Rule::in($taxonomy->acceptedCategoryKeys())],
            'subcategory' => ['nullable', 'string', 'max:80'],
            'pet_key' => ['nullable', Rule::in(array_keys($taxonomy->petOptions()))],
            'taxon_ids' => ['nullable', 'array', 'max:5'],
            'taxon_ids.*' => [
                'integer',
                Rule::exists('taxa', 'id')->where('is_active', true),
                'distinct',
            ],
            'animal_context' => ['nullable', Rule::in(['taxa', 'unidentified'])],
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
            'photos.*' => [
                'file',
                File::image()
                    ->types(['jpg', 'jpeg', 'png', 'webp'])
                    ->max('4mb')
                    ->dimensions(
                        Rule::dimensions()
                            ->minWidth(32)
                            ->minHeight(32)
                            ->maxWidth(12000)
                            ->maxHeight(12000),
                    ),
            ],
            'photo_alt' => [
                Rule::requiredIf($this->hasFile('photos') || $this->hasFile('video')),
                'nullable',
                'string',
                'max:240',
            ],
            'video' => ['nullable', 'file', File::types(['mp4', 'webm', 'mov'])->max('20mb')],
            'video_transcript' => [
                Rule::requiredIf($this->hasFile('video')),
                'nullable',
                'string',
                'max:10000',
            ],
            'video_captions' => [
                Rule::prohibitedIf(! $this->hasFile('video')),
                'nullable',
                'file',
                File::types(['vtt'])->max('256kb'),
                new ValidWebVtt,
            ],
            'video_caption_locale' => [
                Rule::prohibitedIf(! $this->hasFile('video_captions')),
                Rule::requiredIf($this->hasFile('video_captions')),
                'nullable',
                Rule::in(['en', 'lt', 'ru']),
            ],
            'sensitive_media' => ['nullable', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'photo_alt.required' => __('forum_accessibility.validation.media_description_required'),
            'video_transcript.required' => __('forum_accessibility.validation.video_transcript_required'),
            'video_captions.prohibited' => __('forum_accessibility.validation.caption_video_required'),
            'video_caption_locale.required' => __('forum_accessibility.validation.caption_locale_required'),
            'video_caption_locale.prohibited' => __('forum_accessibility.validation.caption_file_required'),
        ];
    }
}
