<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Validation\PetProfileMediaRules;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;

final class PetProfileMediaForm extends Form
{
    public ?TemporaryUploadedFile $upload = null;

    public string $altText = '';

    /** @return array<string, list<string>> */
    protected function rules(): array
    {
        return [
            'upload' => PetProfileMediaRules::upload(false),
            'altText' => PetProfileMediaRules::altText($this->upload !== null),
        ];
    }

    /** @return array{upload: TemporaryUploadedFile|null, alt_text: string} */
    public function data(bool $uploadRequired = false): array
    {
        $validated = $this->validate([
            'upload' => PetProfileMediaRules::upload($uploadRequired),
            'altText' => PetProfileMediaRules::altText($this->upload !== null || $uploadRequired),
        ]);

        return [
            'upload' => $validated['upload'] instanceof TemporaryUploadedFile
                ? $validated['upload']
                : null,
            'alt_text' => trim((string) ($validated['altText'] ?? '')),
        ];
    }
}
