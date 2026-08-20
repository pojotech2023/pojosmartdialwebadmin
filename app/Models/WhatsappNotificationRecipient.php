<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappNotificationRecipient extends Model
{
    protected $fillable = [
        'whatsapp_notification_id',
        'whatsapp_contact_id',
        'status',
        'error_message',
    ];

    public function notification()
    {
        return $this->belongsTo(WhatsappNotification::class, 'whatsapp_notification_id');
    }

    public function contact()
    {
        return $this->belongsTo(WhatsappContact::class, 'whatsapp_contact_id');
    }
}
