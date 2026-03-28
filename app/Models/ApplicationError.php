<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationError extends Model
{
    protected $table = 'application_errors';

    protected $fillable = [
        'error_reference',
        'message',
        'stack_trace',
        'url',
        'method',
        'user_id',
        'ip_address',
        'user_agent',
        'input_data',
    ];

    protected $casts = [
        'input_data' => 'array',
    ];
}
