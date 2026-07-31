<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taxon_names', function (Blueprint $table): void {
            $table->string('import_key', 64)->nullable()->after('source_record_id');
            $table->unique(
                ['taxon_import_id', 'import_key'],
                'taxon_names_import_key_unique',
            );
        });

        Schema::create('taxon_import_issues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('taxon_import_id')
                ->constrained('taxon_imports')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('source_row')->nullable();
            $table->string('source_record_id', 190)->nullable();
            $table->string('severity', 20);
            $table->string('code', 100);
            $table->json('context')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(
                ['taxon_import_id', 'severity', 'source_row'],
                'taxon_import_issues_import_severity_row_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxon_import_issues');

        Schema::table('taxon_names', function (Blueprint $table): void {
            $table->dropUnique('taxon_names_import_key_unique');
            $table->dropColumn('import_key');
        });
    }
};
