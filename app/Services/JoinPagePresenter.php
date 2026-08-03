<?php

declare(strict_types=1);

namespace App\Services;

final class JoinPagePresenter
{
    /**
     * @return array<string, mixed>
     */
    public function page(): array
    {
        return [
            'html_locale' => str_replace('_', '-', app()->getLocale()),
            'page_title' => __('join.meta.title'),
            'meta_description' => __('join.meta.description'),
            'canonical_url' => url('/'),
            'register_url' => route('register'),
            'login_url' => route('login'),
            'explore_url' => route('discover.index'),
            'forum_url' => route('forum.index'),
            'places_url' => route('places.index'),
            'locale_url' => route('locale.update'),
            'current_locale' => app()->getLocale(),
            'locale_options' => collect(config('platform.supported_locales', ['en']))
                ->mapWithKeys(static fn (string $locale): array => [
                    $locale => __('auth.locales.'.$locale),
                ])
                ->all(),
            'outcomes' => [
                [
                    'icon' => 'paw-print',
                    'title' => __('join.outcomes.profile.title'),
                    'description' => __('join.outcomes.profile.description'),
                ],
                [
                    'icon' => 'messages-square',
                    'title' => __('join.outcomes.community.title'),
                    'description' => __('join.outcomes.community.description'),
                ],
                [
                    'icon' => 'map-pinned',
                    'title' => __('join.outcomes.local.title'),
                    'description' => __('join.outcomes.local.description'),
                ],
            ],
            'steps' => [
                [
                    'number' => '01',
                    'title' => __('join.steps.account.title'),
                    'description' => __('join.steps.account.description'),
                ],
                [
                    'number' => '02',
                    'title' => __('join.steps.pet.title'),
                    'description' => __('join.steps.pet.description'),
                ],
                [
                    'number' => '03',
                    'title' => __('join.steps.circle.title'),
                    'description' => __('join.steps.circle.description'),
                ],
            ],
            'tools' => [
                [
                    'icon' => 'newspaper',
                    'title' => __('join.tools.feed.title'),
                    'description' => __('join.tools.feed.description'),
                ],
                [
                    'icon' => 'circle-help',
                    'title' => __('join.tools.forum.title'),
                    'description' => __('join.tools.forum.description'),
                ],
                [
                    'icon' => 'siren',
                    'title' => __('join.tools.lost.title'),
                    'description' => __('join.tools.lost.description'),
                ],
                [
                    'icon' => 'notebook-tabs',
                    'title' => __('join.tools.care.title'),
                    'description' => __('join.tools.care.description'),
                ],
            ],
            'privacy_points' => [
                __('join.privacy.points.audience'),
                __('join.privacy.points.records'),
                __('join.privacy.points.control'),
            ],
            'faqs' => [
                [
                    'question' => __('join.faq.cost.question'),
                    'answer' => __('join.faq.cost.answer'),
                ],
                [
                    'question' => __('join.faq.pet.question'),
                    'answer' => __('join.faq.pet.answer'),
                ],
                [
                    'question' => __('join.faq.privacy.question'),
                    'answer' => __('join.faq.privacy.answer'),
                ],
                [
                    'question' => __('join.faq.local.question'),
                    'answer' => __('join.faq.local.answer'),
                ],
            ],
        ];
    }
}
