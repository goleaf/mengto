<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SearchUpdateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string $author_key
 * @property string $author_name
 * @property string|null $body
 * @property Carbon|null $created_at
 * @property int $id
 * @property Carbon $occurred_at
 * @property string|null $public_area
 * @property-read SearchCase|null $searchCase
 * @property int $search_case_id
 * @property string $title
 * @property string $type
 * @property Carbon|null $updated_at
 * @property string $visibility
 */
class SearchUpdate extends Model
{
    /** @use HasFactory<SearchUpdateFactory> */
    use HasFactory;

    protected $fillable = [
        'search_case_id', 'author_key', 'author_name', 'type', 'visibility',
        'title', 'body', 'public_area', 'occurred_at',
    ];

    protected $attributes = ['visibility' => 'public'];

    protected function casts(): array
    {
        return ['occurred_at' => 'datetime'];
    }

    /** @return BelongsTo<\App\Models\SearchCase, $this>*/
    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', 'public');
    }
}
