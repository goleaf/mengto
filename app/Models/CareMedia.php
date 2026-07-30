<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CareMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property string|null $alt_text
 * @property-read CareJournal|null $careJournal
 * @property int $care_entry_id
 * @property int $care_journal_id
 * @property Carbon|null $created_at
 * @property string $created_by_key
 * @property string $disk
 * @property-read CareEntry|null $entry
 * @property int $id
 * @property string $mime_type
 * @property string $original_name
 * @property string $path
 * @property string $sensitivity
 * @property int $size_bytes
 * @property Carbon|null $updated_at
 */
class CareMedia extends Model
{
    /** @use HasFactory<CareMediaFactory> */
    use HasFactory;

    protected $table = 'care_media';

    protected $fillable = [
        'care_journal_id', 'care_entry_id', 'disk', 'path', 'mime_type',
        'original_name', 'size_bytes', 'alt_text', 'sensitivity',
        'created_by_key',
    ];

    protected $hidden = ['path', 'original_name'];

    protected function casts(): array
    {
        return [
            'original_name' => 'encrypted',
            'alt_text' => 'encrypted',
        ];
    }

    /** @return BelongsTo<\App\Models\CareJournal, $this>*/
    public function careJournal(): BelongsTo
    {
        return $this->belongsTo(CareJournal::class);
    }

    /** @return BelongsTo<\App\Models\CareEntry, $this>*/
    public function entry(): BelongsTo
    {
        return $this->belongsTo(CareEntry::class, 'care_entry_id');
    }
}
