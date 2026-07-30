<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BrowseEventsRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:80'],
            'filter' => [
                'nullable',
                Rule::in([
                    'recommended',
                    'walks',
                    'training',
                    'shows',
                    'adoption',
                    'online',
                    'free',
                    'interested',
                ]),
            ],
            'sort' => ['nullable', Rule::in(['soonest', 'recommended', 'closest', 'name'])],
            'view' => ['nullable', Rule::in(['list', 'calendar', 'map'])],
            'tab' => [
                'nullable',
                Rule::in([
                    'overview',
                    'tickets',
                    'schedule',
                    'attendees',
                    'pets',
                    'chat',
                    'announcements',
                    'location',
                    'media',
                    'rules',
                    'reviews',
                    'manage',
                ]),
            ],
        ];
    }
}
