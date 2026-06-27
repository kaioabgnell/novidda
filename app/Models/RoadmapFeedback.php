<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoadmapFeedback extends Model
{
    protected $table = 'roadmap_feedbacks';

    protected $fillable = ['roadmap_item_id', 'reader_id', 'score', 'comment'];

    public function roadmapItem(): BelongsTo
    {
        return $this->belongsTo(RoadmapItem::class);
    }
}
