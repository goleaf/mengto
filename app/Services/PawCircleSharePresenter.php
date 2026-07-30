<?php

namespace App\Services;

final class PawCircleSharePresenter
{
    /**
     * @param  array<string, mixed>  $item
     * @param  array<int, array<string, mixed>>  $recipients
     * @return array<string, mixed>
     */
    public function present(array $item, array $recipients): array
    {
        $url = route($item['route'], $item['route_parameters'] ?? []);
        $message = 'I thought you would enjoy '.$item['title'].' on PawCircle: '.$url;

        return [
            'item' => [
                ...$item,
                'url' => $url,
            ],
            'channels' => [
                [
                    'title' => 'Email',
                    'description' => 'Open a ready-to-send email with the PawCircle link.',
                    'icon' => 'mail',
                    'href' => $this->emailUrl($item['title'], $message),
                    'label' => 'Open email',
                ],
                [
                    'title' => 'Text message',
                    'description' => 'Open your messaging app with the link already included.',
                    'icon' => 'message-square-text',
                    'href' => 'sms:?body='.rawurlencode($message),
                    'label' => 'Open messages',
                ],
                [
                    'title' => 'Original page',
                    'description' => 'Review the full PawCircle page before you send it.',
                    'icon' => 'external-link',
                    'href' => $url,
                    'label' => 'Open original',
                ],
            ],
            'recipients' => array_map(
                static fn (array $recipient): array => [
                    'key' => $recipient['key'],
                    'name' => $recipient['name'],
                    'detail' => $recipient['pet'].' · '.$recipient['neighborhood'],
                    'image' => $recipient['thumbnail'],
                    'image_alt' => $recipient['image_alt'],
                    'message' => $message,
                ],
                $recipients,
            ),
            'linkDetails' => [
                ['label' => 'Share type', 'value' => $item['type']],
                ['label' => 'Destination', 'value' => $item['title']],
                ['label' => 'Link', 'value' => $url],
            ],
        ];
    }

    private function emailUrl(string $title, string $message): string
    {
        return 'mailto:?'.http_build_query([
            'subject' => 'From PawCircle: '.$title,
            'body' => $message,
        ], '', '&', PHP_QUERY_RFC3986);
    }
}
