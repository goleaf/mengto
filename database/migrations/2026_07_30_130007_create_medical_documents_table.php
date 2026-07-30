<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('medical_event_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vaccination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 60)->index();
            $table->string('title', 180);
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size_bytes');
            $table->string('source_type', 40)->index();
            $table->string('source_name', 160);
            $table->string('verification_status', 40)->index();
            $table->date('expires_on')->nullable()->index();
            $table->string('uploaded_by_key', 80)->index();
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamps();

            $table->index(
                ['medical_record_id', 'type', 'created_at'],
                'medical_documents_record_type_idx',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_documents');
    }
};
