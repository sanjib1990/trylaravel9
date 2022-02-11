<?php

namespace App\Thinkific\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RecievedWebhook extends Model
{
    use HasFactory;

    protected $fillable = [
        'subdomain',
        'hook_id',
        'resource',
        'action',
        'headers',
        'webhook_data',
    ];
}
