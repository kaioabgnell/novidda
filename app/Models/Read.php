<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Read extends Model
{
    use HasFactory, BelongsToAccount;

    // Tabela só tem read_at (sem created_at/updated_at).
    public $timestamps = false;

    protected $fillable = ['account_id', 'reader_id', 'changelog_id', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];
}
