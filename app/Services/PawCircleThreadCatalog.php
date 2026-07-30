<?php

namespace App\Services;

final class PawCircleThreadCatalog
{
    /**
     * @param  array<string, mixed>  $post
     * @return array<int, array{author: string, pet: string, initials: string, tone: string, body: string, time: string, datetime: string, mine: bool}>
     */
    public function comments(array $post): array
    {
        return match ($post['pet']) {
            'Mochi' => [
                $this->comment(
                    'Jamie Cho',
                    'Olive',
                    'JC',
                    'mint',
                    'That patient patio practice really shows. We use the quiet corner near the planters for the same reason.',
                    '12 min ago',
                    '2026-07-29T09:48:00-07:00',
                ),
                $this->comment(
                    'Mia Carter',
                    'Scout',
                    'MC',
                    'sun',
                    'A full patio loop is a serious win. Scout and I would happily join for a calm practice walk next week.',
                    '6 min ago',
                    '2026-07-29T09:54:00-07:00',
                    true,
                ),
            ],
            'Juniper' => [
                $this->comment(
                    'Mia Carter',
                    'Scout',
                    'MC',
                    'sun',
                    'Thank you for noting the shade. Is the west entrance the gentler start for older dogs?',
                    '42 min ago',
                    '2026-07-29T09:18:00-07:00',
                    true,
                ),
                $this->comment(
                    'Ari Jensen',
                    'Mochi',
                    'AJ',
                    'paper',
                    'The west entrance stays quiet before five and has a water fountain near the first bench.',
                    '31 min ago',
                    '2026-07-29T09:29:00-07:00',
                ),
            ],
            'Pip' => [
                $this->comment(
                    'Priya Shah',
                    'Clover',
                    'PS',
                    'mint',
                    'The snack clause is always the decisive one. Clover accepted her carrier after the same negotiation.',
                    '2 hrs ago',
                    '2026-07-29T08:05:00-07:00',
                ),
                $this->comment(
                    'Mia Carter',
                    'Scout',
                    'MC',
                    'sun',
                    'That first comfortable session is worth celebrating. Pip looks wonderfully focused.',
                    '1 hr ago',
                    '2026-07-29T08:46:00-07:00',
                    true,
                ),
            ],
            'Scout' => [
                $this->comment(
                    'Ari Jensen',
                    'Mochi',
                    'AJ',
                    'paper',
                    'Excellent catch, Scout. Mochi remains committed to supervising fetch from a respectful distance.',
                    'Yesterday',
                    '2026-07-28T18:10:00-07:00',
                ),
                $this->comment(
                    'Noah Patel',
                    'Juniper',
                    'NP',
                    'mint',
                    'That focused second try is impressive. The grass there looks perfect for a softer landing.',
                    'Yesterday',
                    '2026-07-28T18:24:00-07:00',
                ),
            ],
            default => [],
        };
    }

    /**
     * @return array<int, array{icon: string, title: string, description: string}>
     */
    public function guide(): array
    {
        return [
            [
                'icon' => 'heart-handshake',
                'title' => 'Lead with care',
                'description' => 'Share context that helps pets and people feel understood.',
            ],
            [
                'icon' => 'map-pin',
                'title' => 'Keep it local',
                'description' => 'Add useful route, place, timing, or accessibility details.',
            ],
            [
                'icon' => 'shield-check',
                'title' => 'Protect privacy',
                'description' => 'Keep personal addresses and sensitive care details in direct messages.',
            ],
        ];
    }

    /**
     * @return array{author: string, pet: string, initials: string, tone: string, body: string, time: string, datetime: string, mine: bool}
     */
    private function comment(
        string $author,
        string $pet,
        string $initials,
        string $tone,
        string $body,
        string $time,
        string $datetime,
        bool $mine = false,
    ): array {
        return compact('author', 'pet', 'initials', 'tone', 'body', 'time', 'datetime', 'mine');
    }
}
