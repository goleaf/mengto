<?php

namespace App\Services;

use Illuminate\Support\Str;

final class FeedPresenter
{
    private const PER_PAGE = 4;

    public function __construct(
        private readonly FeedCatalog $catalog,
        private readonly PrototypeState $state,
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
                    'recommended' => 'Recommended',
                    'latest' => 'Newest first',
                ],
                'type_options' => $this->typeOptions(),
                'pet_options' => [
                    'all' => 'All pets',
                    'dogs' => 'Dogs',
                    'cats' => 'Cats',
                    'scout' => 'Scout',
                    'nori' => 'Nori',
                ],
                'stories' => $this->catalog->stories(),
                'posts' => $visiblePosts,
                'total' => count($posts),
                'showing' => count($visiblePosts),
                'next_url' => $visibleCount < count($posts)
                    ? route('pet-social.preview', [
                        'feed' => $mode,
                        'sort' => $sort,
                        'type' => $type,
                        'pet' => $pet,
                        'page' => $page + 1,
                    ])
                    : null,
                'new_posts_url' => route('pet-social.preview', [
                    'feed' => $mode,
                    'sort' => 'latest',
                    'type' => $type,
                    'pet' => $pet,
                ]),
                'composer_url' => route('pet-social.compose', ['kind' => 'post']),
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
            'page_title' => $this->summary($mode, $sort, count($posts))['title'].' | PawCircle',
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
            'label' => $post['title'] ?: $post['represented'].' post',
            'route' => 'pet-social.posts.show',
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

        $topic = $this->catalog->topics()[$record['topic'] ?? 'community'] ?? 'Community';
        $location = $this->catalog->safePlaces()[$record['location'] ?? 'none'] ?? 'Place hidden';
        $audience = $this->catalog->audiences()[$record['audience'] ?? 'public'] ?? 'Everyone';
        $comments = $this->catalog->commentPolicies()[$record['comment_policy'] ?? 'all'] ?? 'Everyone';

        return [
            'key' => $record['key'],
            'format' => $format,
            'type_label' => $this->formatLabel($format),
            ...$identity,
            'author_parameters' => [],
            'published_at' => $record['updated_at'] ?? $record['created_at'],
            'time' => ($record['status'] ?? 'published') === 'draft' ? 'Draft' : 'Just now',
            'title' => ($record['title'] ?? '') !== '' ? $record['title'] : null,
            'body' => $record['body'],
            'topic' => $topic,
            'location' => $location,
            'audience' => $audience,
            'comment_policy' => $comments,
            'tags' => $this->tags($record['tags'] ?? ''),
            'feeds' => $this->createdFeeds($identity, $format),
            'why' => 'You published this from a profile you manage.',
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
        $media = $post['media'] ?? [];
        $firstMedia = $media[0] ?? null;
        $authorKey = Str::slug((string) ($post['handle'] ?? $post['author']));
        $supportiveOnly = ($post['urgent'] ?? false) || ($post['sensitive'] ?? false);
        $reactionOptions = $this->catalog->reactionOptions($supportiveOnly);
        $reactionItems = [];

        foreach ($reactionOptions as $value => $label) {
            $reactionItems[] = [
                'value' => $value,
                'label' => $label,
                'icon' => $this->reactionIcon($value),
                'count' => (int) ($reactionCounts[$value] ?? 0),
                'selected' => $selectedReaction === $value,
            ];
        }

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
            'saved' => $this->state->isActive('saved', $key),
            'subscribed' => $this->state->isActive('post-subscriptions', $key),
            'hidden' => $this->state->isActive('hidden-posts', $key),
            'muted' => $this->state->isActive('muted-authors', $authorKey),
            'blocked' => $this->state->isActive('blocked-authors', $authorKey),
            'author_key' => $authorKey,
            'connection_targets' => $post['connection_targets'] ?? $this->connectionTargets($post),
            'can_manage' => ($post['created_by_current'] ?? false) && $this->state->post($key) !== null,
            'anchor' => 'post-'.$key,
            'thread_url' => route('pet-social.posts.show', ['post' => $key]),
            'return_url' => route('pet-social.preview').'#post-'.$key,
            'share_url' => route('pet-social.share.show', ['target' => $key]),
            'edit_url' => ($post['created_by_current'] ?? false)
                ? route('pet-social.compose', ['kind' => 'post-edit', 'post' => $key])
                : null,
            'report_url' => route('pet-social.compose', ['kind' => 'report-post', 'target' => $key]),
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
     * @param  array<string, string>  $query
     * @return array<int, array<string, mixed>>
     */
    private function modeOptions(array $query): array
    {
        $options = [];

        foreach ($this->catalog->modes() as $key => $mode) {
            $options[] = [
                ...$mode,
                'href' => route('pet-social.preview', [
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
            'all' => 'All formats',
            'text' => 'Text',
            'photo' => 'Photos',
            'video' => 'Video',
            'question' => 'Questions',
            'poll' => 'Polls',
            'event' => 'Events',
            'lost' => 'Lost pets',
            'adoption' => 'Adoption',
            'expert' => 'Expert notes',
            'group' => 'Groups',
            'repost' => 'Reposts',
        ];
    }

    /**
     * @return array{eyebrow: string, title: string, description: string, count: string}
     */
    private function summary(string $mode, string $sort, int $count): array
    {
        $copy = match ($mode) {
            'following' => ['Following feed', 'Profiles you chose', 'Only posts from people, pets, and organizations you follow.'],
            'friends' => ['Friends feed', 'Your closer circle', 'Updates from owner friends and pet friends.'],
            'pets' => ['Pet feed', 'Published as pets', 'Pet-profile moments stay separate from owner posts.'],
            'local' => ['Local feed', 'Around Portland', 'Posts use safe neighborhoods and public places, never home coordinates.'],
            'groups' => ['Group feed', 'From your communities', 'Updates from groups you joined.'],
            'experts' => ['Expert feed', 'Verified professional notes', 'Professional context stays distinct from ordinary social advice.'],
            'shelters' => ['Shelter feed', 'Adoption and volunteer updates', 'Verified shelter profiles and animals looking for homes.'],
            'alerts' => ['Lost and found', 'Active local alerts', 'Priority notices with approximate locations and in-platform contact.'],
            'video' => ['Video feed', 'Pet videos with controls', 'No autoplay. Captions and native playback remain available.'],
            'photos' => ['Photo feed', 'Photo moments and albums', 'Responsive images with author-provided alternative text.'],
            'saved' => ['Your library', 'Saved posts', 'Private bookmarks collected for later.'],
            'drafts' => ['Private workspace', 'Post drafts', 'Unpublished ideas visible only to you.'],
            'archive' => ['Private archive', 'Archived posts', 'Hidden from public profiles and ready to restore.'],
            default => ['Recommended feed', 'Today around your pack', 'Following, local context, and explained recommendations in one stream.'],
        };

        return [
            'eyebrow' => $copy[0],
            'title' => $copy[1],
            'description' => $copy[2],
            'count' => $count.' '.Str::plural('post', $count).' · '.($sort === 'latest' ? 'newest first' : 'recommended'),
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
            'photo' => 'Photo update',
            'video' => 'Pet video',
            'question' => 'Question',
            'poll' => 'Poll',
            'event' => 'Event',
            'lost' => 'Lost pet alert',
            'adoption' => 'Adoption profile',
            'expert' => 'Expert note',
            'group' => 'Group post',
            'repost' => 'Repost',
            default => 'Text post',
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
                'title' => 'Small dog social hour',
                'place' => 'Laurelhurst Park',
                'time' => 'Sat 10:00',
                'datetime' => '2026-08-01T10:00:00-07:00',
                'date_accessible' => 'Saturday, August 1, 2026 at 10:00 AM',
                'attendees' => '18 neighbors going',
                'detail_route' => 'pet-social.meetups.small_dog_social',
            ],
            [
                'key' => 'foster-coffee-walk',
                'title' => 'Foster coffee walk',
                'place' => 'Tabor Commons',
                'time' => 'Sun 9:30',
                'datetime' => '2026-08-02T09:30:00-07:00',
                'date_accessible' => 'Sunday, August 2, 2026 at 9:30 AM',
                'attendees' => '12 neighbors going',
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
                'name' => 'Apartment Pets PDX',
                'members' => '2.4k members',
                'topic' => 'Small-space routines',
                'detail_route' => 'pet-social.groups.apartment_pets',
            ],
            [
                'key' => 'trail-tails',
                'name' => 'Trail Tails',
                'members' => '8.1k members',
                'topic' => 'Local hikes and safety',
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
                'title' => 'Why this appears',
                'description' => 'Every recommended post includes a specific reason you can use to tune the feed.',
            ],
            [
                'title' => 'Safe location defaults',
                'description' => 'Posts use neighborhoods, parks, and approximate areas instead of home coordinates.',
            ],
            [
                'title' => 'Data saver ready',
                'description' => 'Videos never autoplay and responsive images load only when needed.',
            ],
        ];
    }
}
