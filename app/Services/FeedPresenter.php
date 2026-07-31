<?php

namespace App\Services;

use Illuminate\Support\Str;

final class FeedPresenter
{
    private const PER_PAGE = 4;

    public function __construct(
        private readonly FeedCatalog $catalog,
        private readonly PrototypeState $state,
        private readonly PhotoInteractionState $photoInteractions,
        private readonly ProfilePresenter $profiles,
        private readonly InteractionPresenter $interactions,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function page(
        string $mode = 'home',
        string $sort = 'recommended',
        string $type = 'all',
        string $pet = 'all',
        int $page = 1,
    ): array {
        $mode = array_key_exists($mode, $this->catalog->modes()) ? $mode : 'home';
        $sort = in_array($sort, ['recommended', 'latest'], true) ? $sort : 'recommended';
        $type = $this->validType($type);
        $pet = $this->validPet($pet);
        $page = max(1, min(10, $page));
        $posts = $this->filteredPosts($mode, $sort, $type, $pet);
        $visibleCount = min(count($posts), $page * self::PER_PAGE);
        $visiblePosts = array_slice($posts, 0, $visibleCount);
        $query = compact('mode', 'sort', 'type', 'pet');

        return [
            'owner' => $this->profiles->owner(),
            'pets' => $this->profiles->pets(),
            'feed' => [
                'mode' => $mode,
                'sort' => $sort,
                'type' => $type,
                'pet' => $pet,
                'page' => $page,
                'summary' => $this->summary($mode, $sort, count($posts)),
                'modes' => $this->modeOptions($query),
                'sort_options' => [
                    'recommended' => __('messages.recommended_d70604e843'),
                    'latest' => __('messages.newest_first_ffb6f5764b'),
                ],
                'type_options' => $this->typeOptions(),
                'pet_options' => [
                    'all' => __('messages.all_pets_46c6bd796e'),
                    'dogs' => __('messages.dogs_246b0deffb'),
                    'cats' => __('messages.cats_ec05d70c6f'),
                    'scout' => __('messages.scout_8a1db462be'),
                    'nori' => __('messages.nori_a64203ba20'),
                ],
                'stories' => $this->catalog->stories(),
                'posts' => $visiblePosts,
                'total' => count($posts),
                'showing' => count($visiblePosts),
                'next_url' => $visibleCount < count($posts)
                    ? route('home', [
                        'feed' => $mode,
                        'sort' => $sort,
                        'type' => $type,
                        'pet' => $pet,
                        'page' => $page + 1,
                    ])
                    : null,
                'new_posts_url' => route('home', [
                    'feed' => $mode,
                    'sort' => 'latest',
                    'type' => $type,
                    'pet' => $pet,
                ]),
                'composer_url' => route('compose', ['kind' => 'post']),
                'draft_count' => count(array_filter(
                    $this->state->posts(),
                    static fn (array $post): bool => ($post['status'] ?? 'published') === 'draft',
                )),
                'return_query' => [
                    'feed' => $mode,
                    'sort' => $sort,
                    'type' => $type,
                    'pet' => $pet,
                ],
            ],
            'meetups' => $this->interactions->meetups($this->meetups()),
            'groups' => $this->interactions->groups($this->groups()),
            'tips' => $this->tips(),
            'page_title' => __('presentation.brand_title', [
                'title' => $this->summary($mode, $sort, count($posts))['title'],
            ]),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function post(string $key): ?array
    {
        foreach ($this->allPosts() as $post) {
            if ($post['key'] === $key && ! $post['blocked']) {
                return $post;
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function photo(string $key): ?array
    {
        foreach ($this->allPosts() as $post) {
            if ($post['blocked']) {
                continue;
            }

            foreach ($post['media'] as $media) {
                if (($media['photo_key'] ?? '') === $key) {
                    return $media;
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, string>|null
     */
    public function editablePost(string $key): ?array
    {
        return $this->state->post($key);
    }

    /**
     * @return array{target: string, label: string, route: string, route_parameters: array<string, string>}|null
     */
    public function reportContext(string $key): ?array
    {
        $post = $this->post($key);

        if ($post === null) {
            return null;
        }

        return [
            'target' => $key,
            'label' => $post['title'] ?: __('presentation.represented_post', ['name' => $post['represented']]),
            'route' => 'posts.show',
            'route_parameters' => ['post' => $key],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function mediaPresets(): array
    {
        return $this->catalog->mediaPresets();
    }

    /**
     * @return array<string, string>
     */
    public function topics(): array
    {
        return $this->catalog->topics();
    }

    /**
     * @return array<string, string>
     */
    public function audiences(): array
    {
        return $this->catalog->audiences();
    }

    /**
     * @return array<string, string>
     */
    public function commentPolicies(): array
    {
        return $this->catalog->commentPolicies();
    }

    /**
     * @return array<string, string>
     */
    public function identities(): array
    {
        return $this->catalog->identities();
    }

    /**
     * @return array<string, string>
     */
    public function safePlaces(): array
    {
        return $this->catalog->safePlaces();
    }

    /**
     * @return array<string, string>
     */
    public function reportReasons(): array
    {
        return $this->catalog->reportReasons();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function filteredPosts(string $mode, string $sort, string $type, string $pet): array
    {
        $posts = $this->allPosts();

        $posts = array_values(array_filter($posts, function (array $post) use ($mode, $type, $pet): bool {
            $status = $post['status'] ?? 'published';

            if ($post['blocked']) {
                return false;
            }

            if ($mode === 'drafts') {
                return $status === 'draft'
                    && $this->matchesType($post, $type)
                    && $this->matchesPet($post, $pet);
            }

            if ($mode === 'archive') {
                return $status === 'archived'
                    && $this->matchesType($post, $type)
                    && $this->matchesPet($post, $pet);
            }

            if ($status !== 'published') {
                return false;
            }

            if ($mode === 'saved' && ! $post['saved']) {
                return false;
            }

            if ($mode === 'following' && ! $this->matchesFollowing($post)) {
                return false;
            }

            if (
                ! in_array($mode, ['home', 'saved', 'following'], true)
                && ! in_array($mode, $post['feeds'], true)
            ) {
                return false;
            }

            if ($mode !== 'saved' && ($post['hidden'] || $post['muted'])) {
                return false;
            }

            return $this->matchesType($post, $type) && $this->matchesPet($post, $pet);
        }));

        if ($sort === 'latest' || in_array($mode, ['drafts', 'archive'], true)) {
            usort(
                $posts,
                static fn (array $left, array $right): int => strcmp(
                    (string) $right['published_at'],
                    (string) $left['published_at'],
                ),
            );
        }

        return $posts;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function allPosts(): array
    {
        $posts = [
            ...array_map(fn (array $post): array => $this->createdPost($post), $this->state->posts()),
            ...$this->catalog->posts(),
        ];
        $photoKeys = [];

        foreach ($posts as $post) {
            foreach ($post['media'] ?? [] as $index => $item) {
                if (($item['type'] ?? '') === 'image') {
                    $photoKeys[] = (string) $post['key'].'-photo-'.($index + 1);
                }
            }
        }

        $this->photoInteractions->load($photoKeys);

        return array_map(fn (array $post): array => $this->decorate($post), $posts);
    }

    /**
     * @param  array<string, string>  $record
     * @return array<string, mixed>
     */
    private function createdPost(array $record): array
    {
        $identity = $this->catalog->identity($record['identity'] ?? 'mia');
        $preset = $this->catalog->mediaPresets()[$record['media'] ?? 'none']
            ?? $this->catalog->mediaPresets()['none'];
        $format = $record['format'] ?? $preset['format'];
        $media = $preset['media'];

        if (($record['media_alt'] ?? '') !== '' && isset($media[0])) {
            $media[0]['alt'] = $record['media_alt'];
        }

        $original = ($record['original_key'] ?? '') !== ''
            ? $this->catalogPost($record['original_key'])
            : null;

        if ($format === 'repost' && $original !== null && $media === []) {
            $media = $original['media'];
        }

        $topic = $this->catalog->topics()[$record['topic'] ?? 'community'] ?? __('messages.community_bb501d7877');
        $location = $this->catalog->safePlaces()[$record['location'] ?? 'none'] ?? __('messages.place_hidden_7fcf894e21');
        $audience = $this->catalog->audiences()[$record['audience'] ?? 'public'] ?? __('messages.everyone_da2e5dc515');
        $comments = $this->catalog->commentPolicies()[$record['comment_policy'] ?? 'all'] ?? __('messages.everyone_da2e5dc515');

        return [
            'key' => $record['key'],
            'format' => $format,
            'type_label' => $this->formatLabel($format),
            ...$identity,
            'author_parameters' => [],
            'published_at' => $record['updated_at'] ?? $record['created_at'],
            'time' => ($record['status'] ?? 'published') === 'draft' ? __('messages.draft_ebf12ef47c') : __('messages.just_now_66f53417d3'),
            'title' => ($record['title'] ?? '') !== '' ? $record['title'] : null,
            'body' => $record['body'],
            'topic' => $topic,
            'location' => $location,
            'audience' => $audience,
            'comment_policy' => $comments,
            'tags' => $this->tags($record['tags'] ?? ''),
            'feeds' => $this->createdFeeds($identity, $format),
            'why' => __('messages.you_published_this_from_a_profile_you_manage_6dafaf62d6'),
            'verified' => false,
            'urgent' => $format === 'lost',
            'sensitive' => ($record['sensitive'] ?? 'no') === 'yes',
            'created_by_current' => true,
            'status' => $record['status'] ?? 'published',
            'media' => $media,
            'reaction_counts' => [],
            'replies' => 0,
            'reposts' => 0,
            'original' => $original === null ? null : [
                'key' => $original['key'],
                'author' => $original['author'],
                'represented' => $original['represented'],
                'title' => $original['title'],
                'body' => $original['body'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $post
     * @return array<string, mixed>
     */
    private function decorate(array $post): array
    {
        $key = (string) $post['key'];
        $selectedReaction = $this->state->reaction($key);
        $reactionCounts = $post['reaction_counts'] ?? [];

        if ($selectedReaction !== null) {
            $reactionCounts[$selectedReaction] = (int) ($reactionCounts[$selectedReaction] ?? 0) + 1;
        }

        $reactionTotal = array_sum($reactionCounts);
        $replyTotal = (int) ($post['replies'] ?? 0) + count($this->state->comments($key));
        $authorKey = Str::slug((string) ($post['handle'] ?? $post['author']));
        $supportiveOnly = ($post['urgent'] ?? false) || ($post['sensitive'] ?? false);
        $reactionOptions = $this->catalog->reactionOptions($supportiveOnly);
        $reactionItems = $this->reactionItems($reactionOptions, $reactionCounts, $selectedReaction);
        $media = $this->decorateMedia($post['media'] ?? [], $post, $supportiveOnly);
        $firstMedia = $media[0] ?? null;

        return [
            ...$post,
            'status' => $post['status'] ?? 'published',
            'datetime' => $post['datetime'] ?? $post['published_at'],
            'pet' => $post['pet'] ?? $post['represented'],
            'selected_reaction' => $selectedReaction,
            'selected_reaction_label' => $selectedReaction === null
                ? null
                : ($reactionOptions[$selectedReaction] ?? ucfirst($selectedReaction)),
            'reaction_options' => $reactionOptions,
            'reaction_items' => $reactionItems,
            'reaction_counts' => $reactionCounts,
            'reaction_total' => $reactionTotal,
            'reply_total' => $replyTotal,
            'media' => $media,
            'saved' => $this->state->isActive('saved', $key),
            'subscribed' => $this->state->isActive('post-subscriptions', $key),
            'hidden' => $this->state->isActive('hidden-posts', $key),
            'muted' => $this->state->isActive('muted-authors', $authorKey),
            'blocked' => $this->state->isActive('blocked-authors', $authorKey),
            'author_key' => $authorKey,
            'connection_targets' => $post['connection_targets'] ?? $this->connectionTargets($post),
            'can_manage' => ($post['created_by_current'] ?? false) && $this->state->post($key) !== null,
            'anchor' => 'post-'.$key,
            'thread_url' => route('posts.show', ['post' => $key]),
            'return_url' => route('home').'#post-'.$key,
            'share_url' => route('share.show', ['target' => $key]),
            'edit_url' => ($post['created_by_current'] ?? false)
                ? route('compose', ['kind' => 'post-edit', 'post' => $key])
                : null,
            'report_url' => route('compose', ['kind' => 'report-post', 'target' => $key]),
            'image' => is_array($firstMedia)
                ? ($firstMedia['image'] ?? $firstMedia['poster'] ?? '')
                : '',
            'image_small' => is_array($firstMedia)
                ? ($firstMedia['image_small'] ?? $firstMedia['poster_small'] ?? '')
                : '',
            'image_medium' => is_array($firstMedia)
                ? ($firstMedia['image_medium'] ?? $firstMedia['poster_medium'] ?? '')
                : '',
            'image_alt' => is_array($firstMedia) ? ($firstMedia['alt'] ?? '') : '',
            'stats' => [
                'paws' => (string) $reactionTotal,
                'replies' => (string) $replyTotal,
            ],
            'pawed' => $selectedReaction !== null || $this->state->isActive('paws', $key),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $media
     * @param  array<string, mixed>  $post
     * @return array<int, array<string, mixed>>
     */
    private function decorateMedia(array $media, array $post, bool $supportiveOnly): array
    {
        $decorated = [];

        foreach ($media as $index => $item) {
            if (($item['type'] ?? '') !== 'image') {
                $decorated[] = $item;

                continue;
            }

            $photoKey = (string) $post['key'].'-photo-'.($index + 1);
            $selectedReaction = $this->photoInteractions->reaction($photoKey);
            $reactionCounts = $item['reaction_counts'] ?? [];

            foreach ($this->photoInteractions->reactionCounts($photoKey) as $reaction => $count) {
                $reactionCounts[$reaction] = (int) ($reactionCounts[$reaction] ?? 0) + $count;
            }

            $reactionOptions = $this->catalog->reactionOptions($supportiveOnly);
            $comments = $this->photoInteractions->comments($photoKey);

            $decorated[] = [
                ...$item,
                'photo_key' => $photoKey,
                'post_key' => (string) $post['key'],
                'position' => $index + 1,
                'viewer_srcset' => $item['image_small'].' 576w, '
                    .$item['image_medium'].' 900w, '
                    .$item['image'].' 1200w',
                'author' => $post['author'],
                'represented' => $post['represented'],
                'avatar' => $post['avatar'],
                'post_url' => route('posts.show', ['post' => $post['key']]),
                'reaction_options' => $reactionOptions,
                'reaction_items' => $this->reactionItems(
                    $reactionOptions,
                    $reactionCounts,
                    $selectedReaction,
                ),
                'selected_reaction' => $selectedReaction,
                'selected_reaction_label' => $selectedReaction === null
                    ? null
                    : $reactionOptions[$selectedReaction],
                'reaction_total' => array_sum($reactionCounts),
                'comments' => $comments,
                'comment_count' => $this->photoInteractions->commentCount($photoKey),
                'comment_idempotency_key' => Str::lower((string) Str::ulid()),
            ];
        }

        return $decorated;
    }

    /**
     * @param  array<string, string>  $options
     * @param  array<string, int>  $counts
     * @return array<int, array{
     *     value: string,
     *     label: string,
     *     icon: string,
     *     count: int,
     *     selected: bool
     * }>
     */
    private function reactionItems(array $options, array $counts, ?string $selected): array
    {
        $items = [];

        foreach ($options as $value => $label) {
            $items[] = [
                'value' => $value,
                'label' => $label,
                'icon' => $this->reactionIcon($value),
                'count' => (int) ($counts[$value] ?? 0),
                'selected' => $selected === $value,
            ];
        }

        return $items;
    }

    /**
     * @param  array<string, string>  $query
     * @return array<int, array<string, mixed>>
     */
    private function modeOptions(array $query): array
    {
        $options = [];

        foreach ($this->catalog->modes() as $key => $mode) {
            $options[] = [
                ...$mode,
                'href' => route('home', [
                    'feed' => $key,
                    'sort' => $query['sort'],
                    'type' => $query['type'],
                    'pet' => $query['pet'],
                ]),
                'active' => $query['mode'] === $key,
                'count' => $key === 'drafts'
                    ? (string) count(array_filter(
                        $this->state->posts(),
                        static fn (array $post): bool => ($post['status'] ?? '') === 'draft',
                    ))
                    : null,
            ];
        }

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private function typeOptions(): array
    {
        return [
            'all' => __('messages.all_formats_8a01c6b5b4'),
            'text' => __('messages.text_71988c4d8e'),
            'photo' => __('messages.photos_5e3147ab51'),
            'video' => __('messages.video_d534be829e'),
            'question' => __('messages.questions_9a72221a27'),
            'poll' => __('messages.polls_9967294585'),
            'event' => __('messages.events_8d14f6e72d'),
            'lost' => __('messages.lost_pets_0219466249'),
            'adoption' => __('messages.adoption_9b33128339'),
            'expert' => __('messages.expert_notes_89fa8fbfbf'),
            'group' => __('messages.groups_39bbb719fa'),
            'repost' => __('messages.reposts_13172e9767'),
        ];
    }

    /**
     * @return array{eyebrow: string, title: string, description: string, count: string}
     */
    private function summary(string $mode, string $sort, int $count): array
    {
        $copy = match ($mode) {
            'following' => [__('messages.following_feed_7b34012abf'), __('messages.profiles_you_chose_6d1226b547'), __('messages.only_posts_from_people_pets_and_organizations_you_follow_073038435f')],
            'friends' => [__('messages.friends_feed_6d7bb14048'), __('messages.your_closer_circle_60aef3e3d8'), __('messages.updates_from_owner_friends_and_pet_friends_ef4e806459')],
            'pets' => [__('messages.pet_feed_e95604e8e0'), 'Published as pets', __('messages.pet_profile_moments_stay_separate_from_owner_posts_b91fa84c02')],
            'local' => [__('messages.local_feed_71058215fd'), __('messages.around_portland_e8c73dafb5'), __('messages.posts_use_safe_neighborhoods_and_public_places_never_hom_0957dac059')],
            'groups' => [__('messages.group_feed_0889b9eb3f'), __('messages.from_your_communities_256d469ea4'), __('messages.updates_from_groups_you_joined_736568e5be')],
            'experts' => [__('messages.expert_feed_ede6af2a84'), __('messages.verified_professional_notes_99ec09f039'), __('messages.professional_context_stays_distinct_from_ordinary_social_bef40decac')],
            'shelters' => [__('messages.shelter_feed_ae0e5883f1'), __('messages.adoption_and_volunteer_updates_e7fc7fcdaa'), __('messages.verified_shelter_profiles_and_animals_looking_for_homes_d2edac30bb')],
            'alerts' => [__('messages.lost_and_found_a64c586261'), __('messages.active_local_alerts_f8937a2cae'), __('messages.priority_notices_with_approximate_locations_and_in_platf_00183a3451')],
            'video' => [__('messages.video_feed_63a6c097e3'), __('messages.pet_videos_with_controls_334144e9b2'), __('messages.no_autoplay_captions_and_native_playback_remain_availabl_17df1a3cce')],
            'photos' => [__('messages.photo_feed_0df6d13df5'), __('messages.photo_moments_and_albums_8d0ceb2253'), __('messages.responsive_images_with_author_provided_alternative_text_54c9082f78')],
            'saved' => [__('messages.your_library_1ad8428627'), __('messages.saved_posts_c6171ac089'), __('messages.private_bookmarks_collected_for_later_2770524194')],
            'drafts' => [__('messages.private_workspace_0ba6db3b75'), __('messages.post_drafts_f2065e643a'), __('messages.unpublished_ideas_visible_only_to_you_e27f5eff46')],
            'archive' => [__('messages.private_archive_bd92e82d53'), __('messages.archived_posts_1e95f11a3b'), __('messages.hidden_from_public_profiles_and_ready_to_restore_58321519c1')],
            default => [__('messages.recommended_feed_e170abf11a'), __('messages.today_around_your_pack_5a5743194e'), __('messages.following_local_context_and_explained_recommendations_in_e5f925bf03')],
        };

        return [
            'eyebrow' => $copy[0],
            'title' => $copy[1],
            'description' => $copy[2],
            'count' => __('presentation.sorted_posts_count', [
                'count' => trans_choice('presentation.posts_count', $count, ['count' => $count]),
                'sort' => $sort === 'latest' ? __('messages.newest_first_d5a5a90ac9') : __('presentation.recommended'),
            ]),
        ];
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function matchesType(array $post, string $type): bool
    {
        return $type === 'all' || $post['format'] === $type;
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function matchesPet(array $post, string $pet): bool
    {
        return match ($pet) {
            'dogs', 'cats' => $post['species'] === $pet,
            'scout', 'nori' => $post['pet_slug'] === $pet,
            default => true,
        };
    }

    /**
     * @param  array<string, mixed>  $post
     */
    private function matchesFollowing(array $post): bool
    {
        if ($post['created_by_current'] ?? false) {
            return true;
        }

        foreach ($post['connection_targets'] ?? [] as $target) {
            $subscription = $this->state->subscription($target);

            if (
                $subscription !== null
                && ! $subscription['muted']
                && ! $this->state->isActive('blocks', $target)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $post
     * @return array<int, string>
     */
    private function connectionTargets(array $post): array
    {
        return match ((string) ($post['key'] ?? '')) {
            'mochi-cafe-win' => ['pet-mochi'],
            'scout-shaded-loop' => ['pet-scout'],
            'dr-elena-heat-check' => ['specialist-elena-ruiz'],
            'rose-city-mabel-home', 'sunny-first-play-video' => ['organization-rose-city'],
            'willow-lost-richmond' => ['pet-willow'],
            'apartment-pets-rain-plan' => ['group-apartment-pets'],
            default => [],
        };
    }

    private function validType(string $type): string
    {
        return array_key_exists($type, $this->typeOptions()) ? $type : 'all';
    }

    private function validPet(string $pet): string
    {
        return in_array($pet, ['all', 'dogs', 'cats', 'scout', 'nori'], true) ? $pet : 'all';
    }

    /**
     * @return array<int, string>
     */
    private function tags(string $tags): array
    {
        $items = preg_split('/[\s,]+/', str_replace('#', '', trim($tags))) ?: [];

        return array_values(array_slice(array_unique(array_filter($items)), 0, 8));
    }

    /**
     * @param  array<string, string>  $identity
     * @return array<int, string>
     */
    private function createdFeeds(array $identity, string $format): array
    {
        $feeds = ['home', 'following'];

        if (($identity['pet_slug'] ?? '') !== '') {
            $feeds[] = 'pets';
        }

        if ($format === 'photo') {
            $feeds[] = 'photos';
        }

        if ($format === 'video') {
            $feeds[] = 'video';
        }

        return $feeds;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function catalogPost(string $key): ?array
    {
        foreach ($this->catalog->posts() as $post) {
            if ($post['key'] === $key) {
                return $post;
            }
        }

        return null;
    }

    private function formatLabel(string $format): string
    {
        return match ($format) {
            'photo' => __('messages.photo_update_e9b85666f8'),
            'video' => __('messages.pet_video_60c6c123fe'),
            'question' => __('messages.question_289aff12b0'),
            'poll' => __('messages.poll_d54f7d124c'),
            'event' => __('messages.event_4e1f49a9c8'),
            'lost' => __('messages.lost_pet_alert_43b8776771'),
            'adoption' => __('messages.adoption_profile_6c7cd4fe9a'),
            'expert' => __('messages.expert_note_5280705b5d'),
            'group' => __('messages.group_post_8e6ef13f5d'),
            'repost' => __('messages.repost_f4fd9adb8f'),
            default => __('messages.text_post_273de1dfea'),
        };
    }

    private function reactionIcon(string $reaction): string
    {
        return match ($reaction) {
            'love' => 'heart',
            'funny' => 'laugh',
            'support' => 'hand-heart',
            'useful' => 'lightbulb',
            default => 'thumbs-up',
        };
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function meetups(): array
    {
        return [
            [
                'key' => 'small-dog-social',
                'title' => __('messages.small_dog_social_hour_1bf893f4e1'),
                'place' => __('messages.laurelhurst_park_b88ab4320c'),
                'time' => __('messages.sat_10_00_d69c3a1278'),
                'datetime' => '2026-08-01T10:00:00-07:00',
                'date_accessible' => __('messages.saturday_august_1_2026_at_10_00_am_9b5da30186'),
                'attendees' => __('messages.18_neighbors_going_9c673ce4a4'),
                'detail_route' => 'meetups.small_dog_social',
            ],
            [
                'key' => 'foster-coffee-walk',
                'title' => __('messages.foster_coffee_walk_c7e7e0019b'),
                'place' => __('messages.tabor_commons_bd61fdc703'),
                'time' => __('messages.sun_9_30_ac84bc8651'),
                'datetime' => '2026-08-02T09:30:00-07:00',
                'date_accessible' => __('messages.sunday_august_2_2026_at_9_30_am_81e3f57dc7'),
                'attendees' => __('messages.12_neighbors_going_fc1ef249ec'),
            ],
        ];
    }

    /**
     * @return array<int, array<string, string>>
     */
    private function groups(): array
    {
        return [
            [
                'key' => 'apartment-pets',
                'name' => __('messages.apartment_pets_pdx_6488f4db06'),
                'members' => __('messages.2_4k_members_ef94699d39'),
                'topic' => __('messages.small_space_routines_31ea3a8e79'),
                'detail_route' => 'groups.apartment_pets',
            ],
            [
                'key' => 'trail-tails',
                'name' => __('messages.trail_tails_8c13c56b9f'),
                'members' => __('messages.8_1k_members_bd48b96dc1'),
                'topic' => __('messages.local_hikes_and_safety_8ee75e71d3'),
            ],
        ];
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    private function tips(): array
    {
        return [
            [
                'title' => __('messages.why_this_appears_f469b2f985'),
                'description' => __('messages.every_recommended_post_includes_a_specific_reason_you_ca_56f1b7aa25'),
            ],
            [
                'title' => __('messages.safe_location_defaults_13ba85fa86'),
                'description' => __('messages.posts_use_neighborhoods_parks_and_approximate_areas_inst_87ce3c44d7'),
            ],
            [
                'title' => __('messages.data_saver_ready_7ca4d7d288'),
                'description' => __('messages.videos_never_autoplay_and_responsive_images_load_only_wh_d0707972aa'),
            ],
        ];
    }
}
