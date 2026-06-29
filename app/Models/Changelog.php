<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Changelog extends Model
{
    use HasFactory, BelongsToAccount;

    protected $fillable = [
        'account_id', 'title', 'slug', 'description',
        'type', 'status', 'segment_enabled', 'reaction_emoji', 'published_at',
    ];

    protected $casts = [
        'published_at'    => 'datetime',
        'segment_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Changelog $changelog) {
            if (empty($changelog->slug)) {
                $changelog->slug = Str::slug($changelog->title) . '-' . Str::random(6);
            }
        });
    }

    /** Publicado e com data já alcançada (exclui agendados no futuro). */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'changelog_category');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ChangelogMedia::class)->orderBy('position');
    }

    public function widgetSettings(): HasOne
    {
        return $this->hasOne(ChangelogWidgetSetting::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function feedbacks(): HasMany
    {
        return $this->hasMany(ChangelogFeedback::class);
    }

    public function contextualBanner(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ContextualBanner::class);
    }

    public function segmentRules(): HasMany
    {
        return $this->hasMany(ChangelogSegmentRule::class)->orderBy('position');
    }
}
