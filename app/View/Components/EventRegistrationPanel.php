<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class EventRegistrationPanel extends Component
{
    /**
     * @var array<string, mixed>|null
     */
    public ?array $record;

    /**
     * @param  array<string, mixed>  $event
     * @param  array<string, mixed>  $registration
     */
    public function __construct(
        public array $event,
        public array $registration,
    ) {
        $this->record = $registration['registration'];
    }

    public function render(): View
    {
        return view('components.event-registration-panel');
    }
}
