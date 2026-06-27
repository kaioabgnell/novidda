<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetSetting extends Model
{
    use HasFactory, BelongsToAccount;

    protected $fillable = [
        'account_id', 'button_text', 'open_mode', 'position',
        'theme', 'custom_css', 'webhook_url', 'feedback_enabled', 'roadmap_enabled', 'feed_limit',
    ];

    protected $casts = [
        'theme'      => 'array',
        'feed_limit' => 'integer',
    ];
}
