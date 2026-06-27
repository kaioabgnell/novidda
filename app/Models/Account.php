<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Account extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'plan', 'widget_token'];

    protected static function booted(): void
    {
        static::creating(function (Account $account) {
            if (empty($account->slug)) {
                $account->slug = static::uniqueSlug($account->name);
            }
            if (empty($account->widget_token)) {
                $account->widget_token = Str::random(40);
            }
        });
    }

    protected static function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'conta';
        $slug = $base;
        $i = 1;
        while (static::where('slug', $slug)->exists()) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function changelogs(): HasMany
    {
        return $this->hasMany(Changelog::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function widgetSettings(): HasOne
    {
        return $this->hasOne(WidgetSetting::class);
    }
}
