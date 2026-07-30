<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FormField extends Component
{
    public string $name;

    public string $errorId;

    public mixed $value;

    public bool $required;

    /** @param array<string, mixed> $field */
    public function __construct(public array $field)
    {
        $this->name = $field['name'];
        $this->errorId = $this->name.'-error';
        $this->value = old($this->name, $field['value']);
        $this->required = $field['required'] ?? false;
    }

    public function render(): View
    {
        return view('components.form-field');
    }
}
