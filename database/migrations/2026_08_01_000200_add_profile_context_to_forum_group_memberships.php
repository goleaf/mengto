<?php

declare(strict_types=1);

use App\Enums\SocialActorStatus;
use App\Enums\SocialActorType;
use App\Models\ForumGroupMembership;
use App\Models\SocialActor;
use App\Models\SocialActorSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_groups', function (Blueprint $table): void {
            $table->unsignedInteger('rules_version')->default(1)->after('rules');
            $table->json('allowed_actor_types')->nullable()->after('membership_questions');
        });

        Schema::table('forum_group_memberships', function (Blueprint $table): void {
            $table->dropUnique('forum_group_memberships_group_user_unique');
            $table->foreignId('social_actor_id')
                ->nullable()
                ->after('user_id')
                ->constrained('social_actors')
                ->restrictOnDelete();
            $table->unsignedInteger('accepted_rules_version')->nullable()->after('answers');
            $table->timestamp('accepted_rules_at')->nullable()->after('accepted_rules_version');

            $table->unique(
                ['forum_group_id', 'social_actor_id'],
                'forum_group_memberships_group_actor_unique',
            );
            $table->index(
                ['user_id', 'state', 'social_actor_id', 'id'],
                'forum_group_memberships_user_state_actor_idx',
            );
            $table->index(
                'social_actor_id',
                'forum_group_memberships_actor_fk_idx',
            );
        });

        ForumGroupMembership::query()
            ->select([
                'id',
                'user_id',
                'joined_at',
                'requested_at',
                'created_at',
            ])
            ->orderBy('id')
            ->chunkById(500, function ($memberships): void {
                foreach ($memberships as $membership) {
                    $actor = SocialActor::query()->firstOrCreate(
                        ['user_id' => $membership->user_id],
                        [
                            'actor_key' => (string) Str::uuid(),
                            'actor_type' => SocialActorType::User,
                            'status' => SocialActorStatus::Active,
                            'is_discoverable' => true,
                            'lock_version' => 1,
                        ],
                    );

                    SocialActorSetting::query()->firstOrCreate([
                        'social_actor_id' => $actor->id,
                    ]);

                    $membership->forceFill([
                        'social_actor_id' => $actor->id,
                        'accepted_rules_version' => 1,
                        'accepted_rules_at' => $membership->joined_at
                            ?? $membership->requested_at
                            ?? $membership->created_at,
                    ])->saveQuietly();
                }
            });

        Schema::table('forum_group_memberships', function (Blueprint $table): void {
            $table->unsignedBigInteger('social_actor_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        $previousGroupId = null;
        $previousUserId = null;

        foreach (ForumGroupMembership::query()
            ->select(['id', 'forum_group_id', 'user_id'])
            ->orderBy('forum_group_id')
            ->orderBy('user_id')
            ->orderBy('id')
            ->cursor() as $membership) {
            if ($membership->forum_group_id === $previousGroupId
                && $membership->user_id === $previousUserId
            ) {
                throw new RuntimeException(
                    'Cannot restore account-scoped community membership without losing profile memberships.',
                );
            }

            $previousGroupId = $membership->forum_group_id;
            $previousUserId = $membership->user_id;
        }

        Schema::table('forum_group_memberships', function (Blueprint $table): void {
            $table->dropIndex('forum_group_memberships_actor_fk_idx');
            $table->dropIndex('forum_group_memberships_user_state_actor_idx');
            $table->dropUnique('forum_group_memberships_group_actor_unique');
            $table->dropForeign(['social_actor_id']);
            $table->dropColumn([
                'social_actor_id',
                'accepted_rules_version',
                'accepted_rules_at',
            ]);
            $table->unique(
                ['forum_group_id', 'user_id'],
                'forum_group_memberships_group_user_unique',
            );
        });

        Schema::table('forum_groups', function (Blueprint $table): void {
            $table->dropColumn(['rules_version', 'allowed_actor_types']);
        });
    }
};
