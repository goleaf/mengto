<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

final class PawCircleWalkPlanPresenter
{
    private const FILTERS = ['Upcoming', 'Drafts', 'Completed', 'Cancelled'];

    public function __construct(private readonly PawCirclePrototypeState $state) {}

    /**
     * @param  array<string, mixed>  $owner
     * @return array<string, mixed>
     */
    public function present(string $filter, array $owner): array
    {
        $activeFilter = $this->activeFilter($filter);
        $plans = $this->plans();
        $visiblePlans = array_values(array_filter(
            $plans,
            fn (array $plan): bool => $this->matchesFilter($plan, $activeFilter),
        ));

        return [
            'owner' => $owner,
            'summary' => $this->summary($plans),
            'filters' => self::FILTERS,
            'activeFilter' => $activeFilter,
            'plans' => $visiblePlans,
            'hasPlans' => $plans !== [],
            'starterItems' => $this->starterItems(),
        ];
    }

    /**
     * @return array<int, string>
     */
    public function conversationKeys(): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn (array $plan): string => in_array($plan['status'], ['draft', 'confirmed'], true)
                ? $plan['conversation']
                : '',
            $this->plans(),
        ))));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function messagePlans(): array
    {
        return array_slice(array_values(array_filter(
            $this->plans(),
            static fn (array $plan): bool => $plan['conversation'] !== ''
                && in_array($plan['status'], ['draft', 'confirmed'], true),
        )), 0, 2);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function plansForConversation(string $conversation): array
    {
        return array_values(array_filter(
            $this->plans(),
            static fn (array $plan): bool => $plan['conversation'] === $conversation
                && in_array($plan['status'], ['draft', 'confirmed'], true),
        ));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function plans(): array
    {
        $plans = array_map(
            fn (array $plan): array => $this->decorate($plan),
            $this->state->walkPlans(),
        );

        usort($plans, static fn (array $left, array $right): int => strcmp($left['datetime'], $right['datetime']));

        return $plans;
    }

    /**
     * @param  array<string, string>  $plan
     * @return array<string, mixed>
     */
    private function decorate(array $plan): array
    {
        $participant = $this->participant($plan['target'] ?? 'scout');
        $dateTime = CarbonImmutable::createFromFormat(
            'Y-m-d H:i',
            ($plan['date'] ?? today()->format('Y-m-d')).' '.($plan['time'] ?? '08:30'),
        );
        $status = $plan['status'] ?? 'draft';
        $statusMeta = $this->statusMeta($status);

        return [
            ...$plan,
            'label' => $participant['pet'],
            'conversation' => $participant['conversation'],
            'participant' => $participant,
            'status' => $status,
            'status_label' => $statusMeta['label'],
            'status_icon' => $statusMeta['icon'],
            'status_tone' => $statusMeta['tone'],
            'date_label' => $dateTime->format('D, M j'),
            'time_label' => $dateTime->format('g:i A'),
            'datetime' => $dateTime->toAtomString(),
            'next_action' => $this->nextAction($status),
            'steps' => [
                [
                    'icon' => 'map-pin',
                    'label' => 'Meet',
                    'title' => $plan['location'] ?? 'Choose a familiar meeting point',
                ],
                [
                    'icon' => 'footprints',
                    'label' => 'Walk',
                    'title' => $plan['detail'] ?: 'Easy pace, 30 min',
                ],
                [
                    'icon' => 'message-circle',
                    'label' => 'Settle',
                    'title' => 'Share a quick check-in before heading home',
                ],
            ],
        ];
    }

    private function activeFilter(string $filter): string
    {
        $allowed = array_map(
            static fn (string $label): string => Str::slug($label),
            self::FILTERS,
        );

        return in_array($filter, $allowed, true) ? $filter : 'upcoming';
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function matchesFilter(array $plan, string $filter): bool
    {
        return match ($filter) {
            'drafts' => $plan['status'] === 'draft',
            'completed' => $plan['status'] === 'completed',
            'cancelled' => $plan['status'] === 'cancelled',
            default => in_array($plan['status'], ['draft', 'confirmed'], true),
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $plans
     * @return array<string, mixed>
     */
    private function summary(array $plans): array
    {
        $countByStatus = array_count_values(array_column($plans, 'status'));
        $activePlans = array_filter(
            $plans,
            static fn (array $plan): bool => in_array($plan['status'], ['draft', 'confirmed'], true),
        );
        $neighbors = array_unique(array_filter(array_column($activePlans, 'conversation')));

        return [
            'eyebrow' => 'Walk planner',
            'title' => 'Clear plans make calmer walks',
            'description' => 'Keep timing, meeting points, pace, and neighbor context together from the first draft through the final check-in.',
            'count' => count($plans).' '.Str::plural('plan', count($plans)),
            'stats' => [
                ['label' => 'Upcoming', 'value' => (string) count($activePlans), 'detail' => 'draft or confirmed'],
                ['label' => 'Confirmed', 'value' => (string) ($countByStatus['confirmed'] ?? 0), 'detail' => 'ready to go'],
                ['label' => 'Completed', 'value' => (string) ($countByStatus['completed'] ?? 0), 'detail' => 'walks finished'],
                ['label' => 'Neighbors', 'value' => (string) count($neighbors), 'detail' => 'in active plans'],
            ],
        ];
    }

    /**
     * @return array{label: string, icon: string, tone: string}
     */
    private function statusMeta(string $status): array
    {
        return match ($status) {
            'confirmed' => ['label' => 'Confirmed', 'icon' => 'calendar-check', 'tone' => 'mint'],
            'completed' => ['label' => 'Completed', 'icon' => 'circle-check', 'tone' => 'ink'],
            'cancelled' => ['label' => 'Cancelled', 'icon' => 'circle-x', 'tone' => 'surface'],
            default => ['label' => 'Draft', 'icon' => 'pencil-line', 'tone' => 'sun'],
        };
    }

    /**
     * @return array{label: string, icon: string}|null
     */
    private function nextAction(string $status): ?array
    {
        return match ($status) {
            'draft' => ['label' => 'Confirm plan', 'icon' => 'calendar-check'],
            'confirmed' => ['label' => 'Mark complete', 'icon' => 'circle-check'],
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    private function participant(string $target): array
    {
        return match ($target) {
            'mochi' => [
                'pet' => 'Mochi',
                'person' => 'Ari Jensen',
                'conversation' => 'ari',
                'image' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => 'Ari relaxing with Mochi in a neighborhood park',
            ],
            'juniper' => [
                'pet' => 'Juniper',
                'person' => 'Noah Patel',
                'conversation' => 'noah',
                'image' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => 'Noah practicing with a small dog in a wooded park',
            ],
            default => [
                'pet' => 'Scout',
                'person' => 'Mia Carter',
                'conversation' => '',
                'image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => 'Scout, a black and white Border Collie, resting on grass',
            ],
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function starterItems(): array
    {
        return [
            [
                'title' => 'Start with a familiar companion',
                'meta' => 'Scout and Mia',
                'image' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1654256578072-b932c33cb92e?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => 'Scout, a black and white Border Collie, resting on grass',
                'href' => route('pet-social.compose', 'walk'),
                'icon' => 'calendar-plus',
            ],
            [
                'title' => 'Plan a quiet city loop',
                'meta' => 'Ari and Mochi',
                'image' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1753685723016-78c233daa8a2?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => 'Ari relaxing with Mochi in a neighborhood park',
                'href' => route('pet-social.neighbors.ari'),
                'icon' => 'map',
            ],
            [
                'title' => 'Choose a shaded route',
                'meta' => 'Noah and Juniper',
                'image' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=1200&h=800&q=85',
                'image_small' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=576&h=384&q=80',
                'image_medium' => 'https://images.unsplash.com/photo-1638552718376-7d4881e31418?auto=format&fit=crop&w=900&h=600&q=82',
                'image_alt' => 'Noah practicing with a small dog in a wooded park',
                'href' => route('pet-social.neighbors.index', ['q' => 'Noah']),
                'icon' => 'trees',
            ],
        ];
    }
}
