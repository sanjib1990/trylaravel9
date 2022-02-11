<?php

namespace App\Thinkific\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
