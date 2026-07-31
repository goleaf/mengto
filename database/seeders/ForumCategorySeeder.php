<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\SynchronizeForumCategories;
use Illuminate\Database\Seeder;

final class ForumCategorySeeder extends Seeder
{
    public function run(SynchronizeForumCategories $synchronize): void
    {
        $synchronize->handle();
    }
}
