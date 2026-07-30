<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call(ForumSeeder::class);
        $this->call(ExpertSeeder::class);
        $this->call(ListingSeeder::class);
        $this->call(MarketplaceExpansionSeeder::class);
        $this->call(SearchSeeder::class);
        $this->call(MedicalRecordSeeder::class);
    }
}
