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
        'expires_at', 'custom_copy',
        'cta_text', 'cta_url', 'cta_new_tab',
    ];

    protected $casts = [
        'enabled'      => 'boolean',
        'cta_new_tab'  => 'boolean',
        'expires_at'   => 'datetime',
        'frequency_cap' => 'integer',
        'auto_dismiss_seconds' => 'integer',
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
