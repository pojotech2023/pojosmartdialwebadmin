<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignBase extends Model
{
    protected $fillable = [
        'emp_id',
        'customer_name',
        'company_name',
        'status',
        'mobile',
        'assign_date',
    ];

    protected $casts = [
        'assign_date' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'emp_id');
    }

    public function loanApplication()
    {
        return $this->hasOne(LoanApplication::class, 'assign_base_id');
    }
}
