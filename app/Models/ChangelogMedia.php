<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ChangelogMedia extends Model
{
    use HasFactory;

    protected $table = 'changelog_media';

    protected $fillable = ['changelog_id', 'type', 'url', 'path', 'position'];

    public function changelog(): BelongsTo
    {
        return $this->belongsTo(Changelog::class);
    }

    /** URL pública para exibição (imagem local via symlink ou URL do YouTube). */
    public function getDisplayUrlAttribute(): ?string
    {
        if ($this->type === 'image' && $this->path) {
            return Storage::disk('public')->url($this->path);
        }
        return $this->url;
    }
}
