<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContextualRule extends Model
{
    protected $table = 'changelog_contextual_rules';

    protected $fillable = ['banner_id', 'type', 'match_mode', 'pattern'];

    public function banner(): BelongsTo
    {
        return $this->belongsTo(ContextualBanner::class, 'banner_id');
    }
}
