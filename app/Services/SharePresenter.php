<?php

declare(strict_types=1);

namespace App\Services;

final class SharePresenter
{
    /**
     * @param  array<string, mixed>  $item
     * @param  array<int, array<string, mixed>>  $recipients
     * @return array<string, mixed>
     */
    public function present(array $item, array $recipients): array
    {
        $url = route($item['route'], $item['route_parameters'] ?? []);
        $targetCopy = $this->targetCopy((string) $item['active_section']);
        $presentedItem = [
            ...$item,
            ...($targetCopy ?? []),
            'url' => $url,
        ];
        $message = __('sharing.message.body', ['title' => $item['title'], 'url' => $url]);
        $channels = [
            [
                'code' => 'email',
                'title' => __('sharing.channels.email.title'),
                'description' => __('sharing.channels.email.description'),
                'icon' => 'mail',
                'href' => $this->emailUrl((string) $item['title'], $message),
                'label' => __('sharing.channels.email.action'),
            ],
            [
                'code' => 'text',
                'title' => __('sharing.channels.text.title'),
                'description' => __('sharing.channels.text.description'),
                'icon' => 'message-square-text',
                'href' => 'sms:?body='.rawurlencode($message),
                'label' => __('sharing.channels.text.action'),
            ],
            [
                'code' => 'original',
                'title' => __('sharing.channels.original.title'),
                'description' => __('sharing.channels.original.description'),
                'icon' => 'external-link',
                'href' => $url,
                'label' => __('sharing.channels.original.action'),
            ],
        ];
        $presentedRecipients = array_map(
            static fn (array $recipient): array => [
                'key' => $recipient['key'],
                'name' => $recipient['name'],
                'detail' => $recipient['pet'].' · '.$recipient['neighborhood'],
                'image' => $recipient['thumbnail'],
                'image_alt' => $recipient['image_alt'],
                'message' => $message,
                'action_label' => __('sharing.neighbors.send'),
            ],
            $recipients,
        );

        return [
            'item' => $presentedItem,
            'channels' => $channels,
            'recipients' => $presentedRecipients,
            'linkDetails' => [
                ['label' => __('sharing.details.type'), 'value' => $presentedItem['type']],
                ['label' => __('sharing.details.destination'), 'value' => $item['title']],
                ['label' => __('sharing.details.link'), 'value' => $url],
            ],
            'copy' => [
                'page' => [
                    'title' => __('sharing.page.title', ['title' => $item['title']]),
                    'back' => __('sharing.page.back_to_original'),
                ],
                'channels' => [
                    'eyebrow' => __('sharing.channels.eyebrow'),
                    'title' => __('sharing.channels.title'),
                    'count' => trans_choice('sharing.channels.count', count($channels), ['count' => count($channels)]),
                    'empty_title' => __('sharing.channels.empty.title'),
                    'empty_description' => __('sharing.channels.empty.description'),
                ],
                'neighbors' => [
                    'eyebrow' => __('sharing.neighbors.eyebrow'),
                    'title' => __('sharing.neighbors.title'),
                    'count' => trans_choice('sharing.neighbors.count', count($presentedRecipients), ['count' => count($presentedRecipients)]),
                    'empty_title' => __('sharing.neighbors.empty.title'),
                    'empty_description' => __('sharing.neighbors.empty.description'),
                ],
                'details' => [
                    'title' => __('sharing.details.title'),
                    'empty' => __('sharing.details.empty'),
                ],
                'privacy' => [
                    'title' => __('sharing.privacy.title'),
                    'description' => __('sharing.privacy.description'),
                ],
            ],
        ];
    }

    /**
     * @return array{type: string, eyebrow: string}|null
     */
    private function targetCopy(string $activeSection): ?array
    {
        return match ($activeSection) {
            'feed' => [
                'type' => __('sharing.targets.pet_moment.type'),
                'eyebrow' => __('sharing.targets.pet_moment.eyebrow'),
            ],
            'groups' => [
                'type' => __('sharing.targets.community.type'),
                'eyebrow' => __('sharing.targets.community.eyebrow'),
            ],
            'meetups' => [
                'type' => __('sharing.targets.meetup.type'),
                'eyebrow' => __('sharing.targets.meetup.eyebrow'),
            ],
            'profile' => [
                'type' => __('sharing.targets.member_profile.type'),
                'eyebrow' => __('sharing.targets.member_profile.eyebrow'),
            ],
            'pets' => [
                'type' => __('sharing.targets.pet_profile.type'),
                'eyebrow' => __('sharing.targets.pet_profile.eyebrow'),
            ],
            default => null,
        };
    }

    private function emailUrl(string $title, string $message): string
    {
        return 'mailto:?'.http_build_query([
            'subject' => __('sharing.message.subject', ['title' => $title]),
            'body' => $message,
        ], '', '&', PHP_QUERY_RFC3986);
    }
}
