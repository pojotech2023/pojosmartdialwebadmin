<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadDetail extends Model
{
    use HasFactory;

    protected $table = 'lead_details';

    protected $fillable = [
        'assign_base_id',
        'emp_id',
        'assigned_to',
        'project_type',
        'cus_company_name',
        'cus_mail',
        'price',
        'gst',
        'gst_no',
    ];

    public function assignBase()
    {
        return $this->belongsTo(AssignBase::class, 'assign_base_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'emp_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
