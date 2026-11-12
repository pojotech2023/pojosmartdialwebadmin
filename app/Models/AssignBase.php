<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignBase extends Model
{
    protected $fillable = [
        'emp_id',
        'assigned_to',
        'customer_name',
        'company_name',
        'product_id',
        'product_type',
        'status',
        'account_status',
        'account_remarks',
        'account_updated_by',
        'account_updated_at',
        'mobile',
        'assign_date',
    ];

    protected $casts = [
        'assign_date'        => 'date',
        'account_updated_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'emp_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function accountUpdatedBy()
    {
        return $this->belongsTo(User::class, 'account_updated_by');
    }

    public function leadDetail()
    {
        return $this->hasOne(LeadDetail::class, 'assign_base_id');
    }

    public function loanApplication()
    {
        return $this->hasOne(LoanApplication::class, 'assign_base_id');
    }
}
