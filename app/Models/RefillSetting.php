<?php
// app/Models/RefillSetting.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefillSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'bikash_number',
        'nagad_number',
        'rocket_number',
        'upay_number',
        'instructions',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];
}
