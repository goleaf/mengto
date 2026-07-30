<?php

namespace App\Actions;

use App\Models\ForumTopic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteTopic
{
    public function handle(ForumTopic $topic): void
    {
        DB::transaction(function () use ($topic): void {
            $paths = collect($topic->media ?? [])
                ->pluck('path')
                ->filter()
                ->all();

            $topic->delete();
            Storage::disk('public')->delete($paths);
        });
    }
}
