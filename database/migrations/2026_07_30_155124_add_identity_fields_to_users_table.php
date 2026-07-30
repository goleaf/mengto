<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('actor_key', 96)->nullable()->after('id');
            $table->string('locale', 8)->default('en')->after('password');
            $table->string('timezone', 64)->default('UTC')->after('locale');
            $table->string('status', 24)->default('active')->after('timezone');
            $table->boolean('is_admin')->default(false)->after('status');
            $table->timestamp('last_login_at')->nullable()->after('is_admin');
        });

        User::query()
            ->select(['id', 'name', 'email'])
            ->lazyById()
            ->each(function (User $user): void {
                $actorKey = $user->email === 'test@example.com'
                    ? 'mia-carter'
                    : sprintf(
                        'user-%d-%s',
                        $user->getKey(),
                        Str::slug($user->name) ?: 'member',
                    );

                $user->forceFill(['actor_key' => $actorKey])->saveQuietly();
            });

        Schema::table('users', function (Blueprint $table) {
            $table->string('actor_key', 96)->nullable(false)->after('id')->change();
            $table->unique('actor_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['actor_key']);
            $table->dropColumn([
                'actor_key',
                'locale',
                'timezone',
                'status',
                'is_admin',
                'last_login_at',
            ]);
        });
    }
};
