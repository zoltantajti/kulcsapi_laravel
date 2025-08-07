<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activation extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;

    public function license(): BelongsTo
    {
        return $this->belongsTo(License::class);
    }
}
