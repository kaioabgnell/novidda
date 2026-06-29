<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttributeDiscoveryCache extends Model
{
    public $timestamps = false;

    protected $table = 'attribute_discovery_cache';

    protected $fillable = [
        'account_id', 'attribute_path', 'detected_type',
        'coverage_count', 'sample_values', 'updated_at',
    ];

    protected $casts = [
        'sample_values' => 'array',
        'updated_at'    => 'datetime',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
