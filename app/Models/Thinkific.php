<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thinkific extends Model
{
    use HasFactory;

    protected $fillable = [
        'webhook_data',
        "headers",
        "webhook_url",
    ];

    protected $hidden = [
        "id"
    ];
}
