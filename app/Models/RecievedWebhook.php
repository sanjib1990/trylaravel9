<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
