<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WidgetEvent extends Model
{
    use HasFactory, BelongsToAccount;

    // Só created_at (eventos imutáveis).
    public $timestamps = false;

    protected $fillable = ['account_id', 'changelog_id', 'reader_id', 'type', 'created_at'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
