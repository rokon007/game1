<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WelcomeBonusSetting extends Model
{
    use HasFactory;

    protected $fillable = ['is_active', 'amount'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
