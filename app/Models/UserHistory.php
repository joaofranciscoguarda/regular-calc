<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserHistory extends Model
{
    protected $table = "user_history";
    public $timestamps = false;

    protected $fillable = [
        'session_id',
        'calculation_id',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(Calculation::class);
    }
}
