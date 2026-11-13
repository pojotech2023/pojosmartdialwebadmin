<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'emp_id',
        'assign_base_id',
        'notification_date',
        'notification_time',
        'remarks',
        'is_sent',
        'status',
        'appointment_date',
        'appointment_time',
        'location',
        'alternate_number',
    ];
    public function assignBase()
    {
        return $this->belongsTo(AssignBase::class, 'assign_base_id');
    }

    protected $casts = [
        'notification_date' => 'date',
        'is_sent' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'emp_id');
    }


}
