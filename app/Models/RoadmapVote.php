<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadmapVote extends Model
{
    protected $table = 'roadmap_votes';

    protected $fillable = ['roadmap_item_id', 'reader_id', 'vote'];

    public function roadmapItem(): BelongsTo
    {
        return $this->belongsTo(RoadmapItem::class);
    }
}
