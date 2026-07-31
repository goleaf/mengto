<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

final class ForumJournalMediaForm extends Form
{
    public ?TemporaryUploadedFile $upload = null;

    public string $altText = '';

    public string $caption = '';

    public string $idempotencyKey = '';

    /** @return array<string, list<string>> */
    protected function rules(): array
    {
        return [
            'upload' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'mimetypes:image/jpeg,image/png,image/webp',
                'max:5120',
                'dimensions:min_width=32,min_height=32,max_width=12000,max_height=12000',
            ],
            'altText' => ['required', 'string', 'min:2', 'max:500'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'idempotencyKey' => ['required', 'string', 'min:16', 'max:190'],
        ];
    }
}
