<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadmapComment extends Model
{
    protected $fillable = ['roadmap_item_id', 'reader_id', 'author_name', 'body', 'status'];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function roadmapItem(): BelongsTo
    {
        return $this->belongsTo(RoadmapItem::class);
    }
}
