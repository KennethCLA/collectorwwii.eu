<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $fillable = ['name', 'email', 'message'];

    protected function casts(): array
    {
        return [
            'spam_reasons' => 'array',
            'mail_suppressed_at' => 'datetime',
        ];
    }
}
