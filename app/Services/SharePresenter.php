<?php

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
        $message = __('presentation.share_message', ['title' => $item['title'], 'url' => $url]);

        return [
            'item' => [
                ...$item,
                'url' => $url,
            ],
            'channels' => [
                [
                    'title' => __('messages.email_969ccbd3cf'),
                    'description' => __('messages.open_a_ready_to_send_email_with_the_pawcircle_link_0a3b3462f2'),
                    'icon' => 'mail',
                    'href' => $this->emailUrl($item['title'], $message),
                    'label' => __('messages.open_email_02377d0df1'),
                ],
                [
                    'title' => __('messages.text_message_ca47049e48'),
                    'description' => __('messages.open_your_messaging_app_with_the_link_already_included_bd6c71f256'),
                    'icon' => 'message-square-text',
                    'href' => 'sms:?body='.rawurlencode($message),
                    'label' => __('messages.open_messages_cf997592c9'),
                ],
                [
                    'title' => __('messages.original_page_a7fd19fbb1'),
                    'description' => __('messages.review_the_full_pawcircle_page_before_you_send_it_3bc5ba94a9'),
                    'icon' => 'external-link',
                    'href' => $url,
                    'label' => __('messages.open_original_44a915faf3'),
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
                ['label' => __('messages.share_type_418f2bb1d1'), 'value' => $item['type']],
                ['label' => __('messages.destination_293d404a50'), 'value' => $item['title']],
                ['label' => __('messages.link_a6a32dbc56'), 'value' => $url],
            ],
        ];
    }

    private function emailUrl(string $title, string $message): string
    {
        return 'mailto:?'.http_build_query([
            'subject' => __('presentation.share_subject', ['title' => $title]),
            'body' => $message,
        ], '', '&', PHP_QUERY_RFC3986);
    }
}
