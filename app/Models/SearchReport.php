<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SearchReportFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property string|null $details
 * @property int $id
 * @property string $priority
 * @property string $reason
 * @property int|null $reporter_id
 * @property string $reporter_key
 * @property-read SearchCase|null $searchCase
 * @property int $search_case_id
 * @property-read Sighting|null $sighting
 * @property int|null $sighting_id
 * @property string $status
 * @property Carbon|null $updated_at
 */
class SearchReport extends Model
{
    /** @use HasFactory<SearchReportFactory> */
    use HasFactory;

    protected $fillable = [
        'search_case_id', 'sighting_id', 'forum_report_id', 'reporter_id',
        'reporter_key', 'reason', 'details', 'priority', 'status',
    ];

    protected $attributes = [
        'priority' => 'normal',
        'status' => 'open',
    ];

    /** @return BelongsTo<\App\Models\SearchCase, $this>*/
    public function searchCase(): BelongsTo
    {
        return $this->belongsTo(SearchCase::class);
    }

    /** @return BelongsTo<\App\Models\Sighting, $this>*/
    public function sighting(): BelongsTo
    {
        return $this->belongsTo(Sighting::class);
    }

    /** @return BelongsTo<ForumReport, $this> */
    public function forumReport(): BelongsTo
    {
        return $this->belongsTo(ForumReport::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
