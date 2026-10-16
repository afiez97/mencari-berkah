<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressSync extends Model
{
    protected $fillable = ['code', 'data'];

    protected $casts = ['data' => 'array'];
}
