<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MeetupCard extends Component
{
    /**
     * @param  array<string, mixed>  $meetup
     */
    public function __construct(
        public array $meetup,
        public bool $eager = false,
    ) {}

    public function render(): View
    {
        return view('components.meetup-card');
    }
}
