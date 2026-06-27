<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangelogFeedback extends Model
{
    protected $table = 'changelog_feedbacks';

    protected $fillable = ['changelog_id', 'reader_id', 'score', 'comment'];

    public function changelog(): BelongsTo
    {
        return $this->belongsTo(Changelog::class);
    }
}
