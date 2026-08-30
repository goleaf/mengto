<?php

declare(strict_types=1);

use App\Models\PlaceMergeRedirect;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('place_merge_redirects', function (Blueprint $table): void {
            $table->dropUnique('place_merge_redirects_source_identifier_unique');
            $table->string('active_source_identifier', 190)->nullable()->after('source_identifier');
            $table->timestamp('superseded_at')->nullable()->after('restored_at');
        });

        foreach (PlaceMergeRedirect::query()->whereNull('restored_at')->lazyById(250) as $redirect) {
            $redirect->forceFill(['active_source_identifier' => $redirect->source_identifier])->saveQuietly();
        }

        Schema::table('place_merge_redirects', function (Blueprint $table): void {
            $table->unique('active_source_identifier', 'place_merge_redirects_active_identifier_unique');
        });
    }

    public function down(): void
    {
        $redirectCount = PlaceMergeRedirect::query()->count();
        $distinctIdentifierCount = PlaceMergeRedirect::query()->distinct()->count('source_identifier');

        if ($redirectCount !== $distinctIdentifierCount) {
            throw new LogicException(
                'Place merge redirect generations exist. Preserve their audit history and recover with a forward fix.',
            );
        }

        Schema::table('place_merge_redirects', function (Blueprint $table): void {
            $table->dropUnique('place_merge_redirects_active_identifier_unique');
            $table->dropColumn(['active_source_identifier', 'superseded_at']);
            $table->unique('source_identifier', 'place_merge_redirects_source_identifier_unique');
        });
    }
};
