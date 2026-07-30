<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class MeetupCard extends Component
{
    public string $meetupKey;

    public bool $rsvp;

    /**
     * @param  array<string, mixed>  $meetup
     */
    public function __construct(
        public array $meetup,
        public bool $eager = false,
    ) {
        $this->meetupKey = $meetup['key'] ?? Str::slug($meetup['title']);
        $this->rsvp = $meetup['rsvp'] ?? false;
    }

    public function render(): View
    {
        return view('components.meetup-card');
    }
}
