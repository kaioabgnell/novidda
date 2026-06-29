<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangelogSegmentRule extends Model
{
    protected $fillable = ['changelog_id', 'attribute', 'operator', 'value', 'position'];

    protected $casts = ['value' => 'array'];

    public function changelog(): BelongsTo
    {
        return $this->belongsTo(Changelog::class);
    }
}
