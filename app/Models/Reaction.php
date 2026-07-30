<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    use HasFactory;

    protected $fillable = ['changelog_id', 'reader_id', 'emoji', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function changelog(): BelongsTo
    {
        return $this->belongsTo(Changelog::class);
    }
}
