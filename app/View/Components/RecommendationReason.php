<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RecommendationReason extends Component
{
    public ?string $resolvedReason;

    /** @var array<int, string> */
    public array $resolvedSignals;

    /**
     * @param  array<string, mixed>|null  $item
     * @param  array<int, string>  $signals
     */
    public function __construct(
        public ?array $item = null,
        public ?string $reason = null,
        public array $signals = [],
    ) {
        $this->resolvedReason = $reason ?? ($item['recommendation_reason'] ?? null);
        $this->resolvedSignals = $signals ?: ($item['signals'] ?? []);
    }

    public function render(): View
    {
        return view('components.recommendation-reason');
    }
}
