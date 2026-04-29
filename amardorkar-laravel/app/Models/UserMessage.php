<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMessage extends Model
{
    protected $fillable = [
        'subject', 'body', 'channel', 'recipient_user_ids',
        'sent_to_all', 'sent_by', 'total_sent', 'total_failed', 'notes',
    ];

    protected $casts = [
        'recipient_user_ids' => 'array',
        'sent_to_all'        => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'sent_by');
    }
}
