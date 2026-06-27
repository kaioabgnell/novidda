<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoadmapItem extends Model
{
    use BelongsToAccount;

    protected $fillable = [
        'account_id', 'title', 'description', 'status', 'feedback_enabled', 'published_at',
    ];

    protected $casts = [
        'feedback_enabled' => 'boolean',
        'published_at'     => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(RoadmapFeedback::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(RoadmapComment::class);
    }
}
