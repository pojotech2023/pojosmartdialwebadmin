<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappNotification extends Model
{
    protected $fillable = [
        'emp_id',
        'assign_base_id',
        'template_name',
        'template_language',
        'message',
        'template_params',
        'header_image_path',
        'source',
        'target_type',
        'scheduled_at',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'template_params' => 'array',
    ];

    public function recipients()
    {
        return $this->hasMany(WhatsappNotificationRecipient::class);
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'emp_id');
    }

    public function assignBase()
    {
        return $this->belongsTo(AssignBase::class, 'assign_base_id');
    }
}
