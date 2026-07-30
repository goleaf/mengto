<?php

namespace App\Models;

use Database\Factories\CareMediaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function careJournal(): BelongsTo
    {
        return $this->belongsTo(CareJournal::class);
    }

    public function entry(): BelongsTo
    {
        return $this->belongsTo(CareEntry::class, 'care_entry_id');
    }
}
