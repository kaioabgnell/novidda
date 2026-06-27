<?php

namespace App\Models;

use App\Models\Concerns\BelongsToAccount;
use Illuminate\Database\Eloquent\Model;

class WidgetFeedback extends Model
{
    use BelongsToAccount;

    protected $fillable = ['account_id', 'reader_id', 'score', 'message'];
}
