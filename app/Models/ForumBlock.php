<?php

namespace App\Models;

use Database\Factories\ForumBlockFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumBlock extends Model
{
    /** @use HasFactory<ForumBlockFactory> */
    use HasFactory;

    protected $fillable = [
        'user_key',
        'blocked_author_key',
        'reason',
    ];
}
