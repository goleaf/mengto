<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserDomainStateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $created_at
 * @property int $id
 * @property string $namespace
 * @property array<string, mixed> $payload
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property int $user_id
 * @property int $version
 */
final class UserDomainState extends Model
{
    /** @use HasFactory<UserDomainStateFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'namespace',
        'version',
        'payload',
    ];

    protected $hidden = ['payload'];

    protected function casts(): array
    {
        return [
            'payload' => 'encrypted:array',
            'version' => 'int',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
