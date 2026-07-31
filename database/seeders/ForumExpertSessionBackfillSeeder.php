<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ForumExpertSession;
use Illuminate\Database\Seeder;

final class ForumExpertSessionBackfillSeeder extends Seeder
{
    public function run(): void
    {
        ForumExpertSession::query()
            ->where('disclaimer_version', '')
            ->update(['disclaimer_version' => '2026-07']);
    }
}
