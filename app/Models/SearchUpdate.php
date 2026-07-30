<?php

namespace App\Models;

use Database\Factories\SearchUpdateFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', 'public');
    }
}
