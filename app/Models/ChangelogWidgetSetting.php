<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChangelogWidgetSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'changelog_id', 'fire_webhook', 'show_comments', 'allow_comments',
        'show_reactions', 'feedback_enabled', 'cta_text', 'cta_url', 'cta_color', 'cta_new_tab',
    ];

    protected $casts = [
        'fire_webhook'     => 'boolean',
        'show_comments'    => 'boolean',
        'allow_comments'   => 'boolean',
        'show_reactions'   => 'boolean',
        'feedback_enabled' => 'boolean',
        'cta_new_tab'      => 'boolean',
    ];

    public function changelog(): BelongsTo
    {
        return $this->belongsTo(Changelog::class);
    }
}
