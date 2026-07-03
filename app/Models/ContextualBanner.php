<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContextualBanner extends Model
{
    protected $table = 'changelog_contextual_banners';

    protected $fillable = [
        'changelog_id', 'enabled', 'style', 'position',
        'frequency', 'frequency_cap', 'auto_dismiss_seconds',
        'expires_at', 'custom_copy', 'bg_color', 'text_color', 'description',
        'cta_text', 'cta_url', 'cta_color', 'cta_new_tab',
        'countdown_enabled', 'countdown_target_at', 'title_align', 'description_align',
    ];

    protected $casts = [
        'enabled'      => 'boolean',
        'cta_new_tab'  => 'boolean',
        'expires_at'   => 'datetime',
        'frequency_cap' => 'integer',
        'auto_dismiss_seconds' => 'integer',
        'countdown_enabled'   => 'boolean',
        'countdown_target_at' => 'datetime',
    ];

    public function changelog(): BelongsTo
    {
        return $this->belongsTo(Changelog::class);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ContextualRule::class, 'banner_id');
    }

    public function includeRules(): HasMany
    {
        return $this->rules()->where('type', 'include');
    }

    public function excludeRules(): HasMany
    {
        return $this->rules()->where('type', 'exclude');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('enabled', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function isActive(): bool
    {
        return $this->enabled && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
