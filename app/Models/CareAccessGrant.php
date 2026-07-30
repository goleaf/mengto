<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CareAccessGrantFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property bool $allow_add
 * @property bool $allow_location
 * @property bool $allow_media
 * @property-read CareJournal|null $careJournal
 * @property int $care_journal_id
 * @property Carbon|null $created_at
 * @property Carbon $expires_at
 * @property string $granted_by_key
 * @property int $id
 * @property string $label
 * @property Carbon|null $last_opened_at
 * @property int $max_views
 * @property array<array-key, mixed> $permissions
 * @property string|null $recipient_key
 * @property string $recipient_name
 * @property string $recipient_role
 * @property Carbon|null $revoked_at
 * @property array<array-key, mixed> $sections
 * @property string $token_hash
 * @property Carbon|null $updated_at
 * @property int $views_used
 */
class CareAccessGrant extends Model
{
    /** @use HasFactory<CareAccessGrantFactory> */
    use HasFactory;

    protected $fillable = [
        'care_journal_id', 'granted_by_key', 'recipient_key',
        'recipient_name', 'recipient_role', 'label', 'token_hash', 'sections',
        'permissions', 'allow_add', 'allow_location', 'allow_media',
        'max_views', 'views_used', 'expires_at', 'last_opened_at',
        'revoked_at',
    ];

    protected $hidden = ['token_hash'];

    protected function casts(): array
    {
        return [
            'sections' => 'array',
            'permissions' => 'array',
            'allow_add' => 'boolean',
            'allow_location' => 'boolean',
            'allow_media' => 'boolean',
            'expires_at' => 'datetime',
            'last_opened_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        return parent::resolveRouteBindingQuery($query, $value, $field)
            ->select([
                'id', 'care_journal_id', 'granted_by_key', 'recipient_key',
                'recipient_name', 'recipient_role', 'label', 'token_hash',
                'sections', 'permissions', 'allow_add', 'allow_location',
                'allow_media', 'max_views', 'views_used', 'expires_at',
                'last_opened_at', 'revoked_at', 'created_at', 'updated_at',
            ]);
    }

    /** @return BelongsTo<\App\Models\CareJournal, $this>*/
    public function careJournal(): BelongsTo
    {
        return $this->belongsTo(CareJournal::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->whereColumn('views_used', '<', 'max_views');
    }

    public function canViewSection(string $section): bool
    {
        return in_array($section, $this->sections ?? [], true);
    }

    public function canBeOpened(): bool
    {
        return $this->revoked_at === null
            && $this->expires_at?->isFuture()
            && $this->views_used < $this->max_views;
    }

    public function canAdd(): bool
    {
        return $this->allow_add
            && in_array('add', $this->permissions ?? [], true)
            && $this->canBeOpened();
    }
}
