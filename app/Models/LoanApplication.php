<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    protected $fillable = [
        'assign_base_id',
        'login_date',
        'app_number',
        'logged_bank',
        'promo_code',
        'req_amount',
        'approved_amount',
        'status',
        'remarks',
        'net_salary',
        'bank_ac',
        'category',
        'bank_manager',
        'dsa_name',
        'ref_1',
        'ref_2',
        'tenure',
        'location',
        'discr',
        'date',
        'mode',
        'aadhar_card_front_upload',
        'aadhar_card_back_upload',
        'pan_upload',
        'bank_statement_upload',
        'salary_slip_upload',
    ];

    protected $casts = [
        'login_date' => 'date',
        'date' => 'date',
        'req_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function assignBase()
    {
        return $this->belongsTo(AssignBase::class, 'assign_base_id');
    }
}