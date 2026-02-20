<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calculation extends Model
{
    protected $fillable = [
        'expression',
        'result',
    ];

    public function userHistory(): HasMany
    {
        return $this->hasMany(UserHistory::class);
    }
}
