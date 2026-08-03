<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PhotoReactionType;
use App\Models\PhotoAsset;
use App\Models\PhotoComment;
use App\Models\PhotoReaction;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class PhotoInteractionState
{
    private const COMMENT_LIMIT = 40;

    /**
     * @var array<string, array{
     *     reaction: string|null,
     *     reaction_counts: array<string, int>,
     *     comments: array<int, array{
     *         id: string,
     *         author: string,
     *         initials: string,
     *         body: string,
     *         time: string,
     *         datetime: string,
     *         mine: bool
     *     }>,
     *     comment_count: int
     * }>
     */
    private array $loaded = [];

    public function __construct(
        private readonly AuthFactory $auth,
        private readonly LocaleFormatter $formatter,
    ) {}

    /**
     * Load every interaction needed by one bounded feed in a constant number
     * of queries. Unknown catalogue photos intentionally receive empty state.
     *
     * @param  array<int, string>  $photoKeys
     */
    public function load(array $photoKeys): void
    {
        $keys = array_values(array_unique(array_filter(
            $photoKeys,
            fn (string $key): bool => ! array_key_exists($key, $this->loaded),
        )));

        foreach ($keys as $key) {
            $this->loaded[$key] = $this->emptyState();
        }

        if ($keys === []) {
            return;
        }

        $user = $this->user();
        $relationships = [
            'comments' => fn ($query) => $query
                ->select(['id', 'photo_asset_id', 'user_id', 'body', 'created_at'])
                ->latest('id')
                ->limit(self::COMMENT_LIMIT),
            'comments.user' => fn ($query) => $query->select(['id', 'name']),
        ];

        if ($user !== null) {
            $relationships['reactions'] = fn ($query) => $query
                ->select(['id', 'photo_asset_id', 'user_id', 'reaction'])
                ->where('user_id', $user->id);
        }

        $assets = PhotoAsset::query()
            ->select(['id', 'key', 'post_key', 'position'])
            ->whereIn('key', $keys)
            ->withCount([
                'comments',
                ...$this->reactionCountRelations(),
            ])
            ->with($relationships)
            ->get();

        foreach ($assets as $asset) {
            $selected = $user === null
                ? null
                : $asset->reactions->first()?->reaction?->value;
            $comments = $asset->comments
                ->reverse()
                ->map(fn (PhotoComment $comment): array => $this->presentComment($comment, $user))
                ->values()
                ->all();

            $this->loaded[$asset->key] = [
                'reaction' => $selected,
                'reaction_counts' => $this->countsForAsset($asset),
                'comments' => $comments,
                'comment_count' => (int) $asset->getAttribute('comments_count'),
            ];
        }
    }

    /**
     * @param  array<string, mixed>  $photo
     */
    public function setReaction(array $photo, string $reaction): ?string
    {
        $user = $this->userOrFail();
        Gate::forUser($user)->authorize('create', PhotoReaction::class);
        $reactionType = PhotoReactionType::tryFrom($reaction);

        if (! $reactionType instanceof PhotoReactionType) {
            throw ValidationException::withMessages([
                'reaction' => __(
                    'messages.choose_an_available_reaction_c8a1ac8cff',
                ),
            ]);
        }

        $selected = DB::transaction(function () use ($photo, $reactionType, $user): ?string {
            $asset = $this->asset($photo);
            $existing = PhotoReaction::query()
                ->where('photo_asset_id', $asset->id)
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing?->reaction === $reactionType) {
                Gate::forUser($user)->authorize('delete', $existing);
                $existing->delete();

                return null;
            }

            if ($existing !== null) {
                Gate::forUser($user)->authorize('update', $existing);
                $existing->update(['reaction' => $reactionType]);

                return $reactionType->value;
            }

            $created = PhotoReaction::query()->createOrFirst(
                [
                    'photo_asset_id' => $asset->id,
                    'user_id' => $user->id,
                ],
                ['reaction' => $reactionType],
            );

            if (
                ! $created->wasRecentlyCreated
                && $created->reaction !== $reactionType
            ) {
                Gate::forUser($user)->authorize('update', $created);
                $created->update(['reaction' => $reactionType]);
            }

            return $reactionType->value;
        }, 3);

        unset($this->loaded[(string) $photo['photo_key']]);

        return $selected;
    }

    public function reaction(string $photo): ?string
    {
        $this->load([$photo]);

        return $this->loaded[$photo]['reaction'];
    }

    /**
     * @return array<string, int>
     */
    public function reactionCounts(string $photo): array
    {
        $this->load([$photo]);

        return $this->loaded[$photo]['reaction_counts'];
    }

    /**
     * @param  array<string, mixed>  $photo
     */
    public function addComment(array $photo, string $body, string $idempotencyKey): void
    {
        $user = $this->userOrFail();
        Gate::forUser($user)->authorize('create', PhotoComment::class);

        DB::transaction(function () use ($photo, $body, $idempotencyKey, $user): void {
            $asset = $this->asset($photo);

            PhotoComment::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'idempotency_key' => $idempotencyKey,
                ],
                [
                    'photo_asset_id' => $asset->id,
                    'body' => trim($body),
                ],
            );
        }, 3);

        unset($this->loaded[(string) $photo['photo_key']]);
    }

    /**
     * @return array<int, array{
     *     id: string,
     *     author: string,
     *     initials: string,
     *     body: string,
     *     time: string,
     *     datetime: string,
     *     mine: bool
     * }>
     */
    public function comments(string $photo): array
    {
        $this->load([$photo]);

        return $this->loaded[$photo]['comments'];
    }

    public function commentCount(string $photo): int
    {
        $this->load([$photo]);

        return $this->loaded[$photo]['comment_count'];
    }

    /**
     * @return array<string, \Closure(Builder<PhotoReaction>): Builder<PhotoReaction>>
     */
    private function reactionCountRelations(): array
    {
        $relations = [];

        foreach (PhotoReactionType::values() as $reaction) {
            $relations['reactions as '.$reaction.'_reactions_count'] = static fn (Builder $query): Builder => $query
                ->where('reaction', $reaction);
        }

        return $relations;
    }

    /**
     * @return array<string, int>
     */
    private function countsForAsset(PhotoAsset $asset): array
    {
        $counts = [];

        foreach (PhotoReactionType::values() as $reaction) {
            $counts[$reaction] = (int) $asset->getAttribute($reaction.'_reactions_count');
        }

        return $counts;
    }

    /**
     * @param  array<string, mixed>  $photo
     */
    private function asset(array $photo): PhotoAsset
    {
        return PhotoAsset::query()->firstOrCreate(
            ['key' => (string) $photo['photo_key']],
            [
                'post_key' => (string) $photo['post_key'],
                'position' => (int) $photo['position'],
            ],
        );
    }

    /**
     * @return array{
     *     id: string,
     *     author: string,
     *     initials: string,
     *     body: string,
     *     time: string,
     *     datetime: string,
     *     mine: bool
     * }
     */
    private function presentComment(PhotoComment $comment, ?User $currentUser): array
    {
        $author = $comment->user->name;
        $createdAt = $comment->created_at ?? now();

        return [
            'id' => 'photo-comment-'.$comment->id,
            'author' => $author,
            'initials' => $this->initials($author),
            'body' => $comment->body,
            'time' => $this->formatter->time($createdAt),
            'datetime' => $createdAt->toAtomString(),
            'mine' => $currentUser?->is($comment->user) === true,
        ];
    }

    /**
     * @return array{
     *     reaction: null,
     *     reaction_counts: array<string, int>,
     *     comments: array<int, never>,
     *     comment_count: 0
     * }
     */
    private function emptyState(): array
    {
        return [
            'reaction' => null,
            'reaction_counts' => array_fill_keys(PhotoReactionType::values(), 0),
            'comments' => [],
            'comment_count' => 0,
        ];
    }

    private function initials(string $name): string
    {
        return Str::of($name)
            ->squish()
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(static fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode('');
    }

    private function user(): ?User
    {
        $user = $this->auth->guard()->user();

        return $user instanceof User ? $user : null;
    }

    private function userOrFail(): User
    {
        return $this->user() ?? throw new AuthenticationException;
    }
}
