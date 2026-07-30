<?php

namespace App\Http\Requests;

use App\Services\SearchTaxonomy;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PerformSearchActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(SearchTaxonomy $taxonomy): array
    {
        return [
            'action' => [
                'required',
                Rule::in([
                    'join-search',
                    'create-sector',
                    'create-task',
                    'claim-task',
                    'start-task',
                    'complete-task',
                    'confirm-sighting',
                    'reject-sighting',
                    'publish-update',
                    'update-status',
                ]),
            ],
            'capabilities' => ['nullable', 'array', 'max:8', 'required_if:action,join-search'],
            'capabilities.*' => ['string', Rule::in(array_keys($taxonomy->volunteerCapabilities()))],
            'sector_code' => ['nullable', 'string', 'max:30', 'required_if:action,create-sector'],
            'sector_label' => ['nullable', 'string', 'max:120', 'required_if:action,create-sector'],
            'sector_priority' => ['nullable', 'integer', 'min:1', 'max:3'],
            'sector_risk_notes' => ['nullable', 'string', 'max:1000'],
            'sector_access_notes' => ['nullable', 'string', 'max:1000'],
            'sector_id' => ['nullable', 'integer', 'exists:search_sectors,id'],
            'task_id' => [
                'nullable',
                'integer',
                'required_if:action,claim-task,start-task,complete-task',
                'exists:search_tasks,id',
            ],
            'task_type' => [
                'nullable',
                Rule::in(array_keys($taxonomy->taskTypes())),
                'required_if:action,create-task',
            ],
            'task_title' => ['nullable', 'string', 'max:140', 'required_if:action,create-task'],
            'task_description' => ['nullable', 'string', 'max:2000', 'required_if:action,create-task'],
            'safety_level' => ['nullable', Rule::in(['standard', 'pair-required', 'specialist-only', 'dangerous'])],
            'starts_at' => ['nullable', 'date'],
            'due_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'task_result' => ['nullable', 'string', 'max:2000', 'required_if:action,complete-task'],
            'sighting_id' => [
                'nullable',
                'integer',
                'required_if:action,confirm-sighting,reject-sighting',
                'exists:sightings,id',
            ],
            'update_title' => ['nullable', 'string', 'max:160', 'required_if:action,publish-update'],
            'update_body' => ['nullable', 'string', 'max:2000'],
            'update_area' => ['nullable', 'string', 'max:160'],
            'status' => [
                'nullable',
                Rule::in(array_keys($taxonomy->statuses())),
                'required_if:action,update-status',
            ],
            'status_note' => ['nullable', 'string', 'max:1500'],
            'return_confirmed' => [
                'exclude_unless:action,update-status',
                'accepted_if:status,returned,self-returned',
            ],
        ];
    }
}
