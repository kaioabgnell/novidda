<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory, BelongsToAccount;

    protected $fillable = ['account_id', 'name', 'color', 'icon'];

    public function changelogs(): BelongsToMany
    {
        return $this->belongsToMany(Changelog::class, 'changelog_category');
    }
}
