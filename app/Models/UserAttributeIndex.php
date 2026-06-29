<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAttributeIndex extends Model
{
    protected $table = 'user_attribute_index';

    protected $fillable = ['account_id', 'reader_id', 'attributes_snapshot', 'last_seen_at'];

    protected $casts = [
        'attributes_snapshot' => 'array',
        'last_seen_at'        => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
