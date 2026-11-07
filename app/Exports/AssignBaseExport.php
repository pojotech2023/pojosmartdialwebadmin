<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class AssignBaseExport implements FromArray
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        
        // Add headers
        $rows[] = [
            'Emp ID',
            'Employee Name',
            'Customer Name',
            'Company Name',
            'Product Type',
            'Status',
            'Mobile',
            'Date',
            'Login Date',
            'App Number',
            'Logged Bank',
            'Promo Code',
            'Requested Amount',
            'Approved Amount',
            'Loan Status',
            'Remarks',
            'Net Salary',
            'Bank A/C',
            'Category',
            'Bank Manager',
            'DSA Name',
            'Reference 1',
            'Reference 2',
            'Tenure',
            'Location',
            'Loan Date',
            'Mode',
            'Aadhar Front',
            'Aadhar Back',
            'PAN Card',
            'Bank Statement',
            'Salary Slip',
        ];
        
        // Add data rows
        foreach ($this->data as $row) {
            $loan = $row->loanApplication;
            
            $rows[] = [
                $row->emp_id,
                $row->employee->name ?? '',
                $row->customer_name,
                $row->company_name,
                $row->product->name ?? $row->product_type ?? '',
                $row->status,
                $row->mobile,
                $row->assign_date ? $row->assign_date->format('d/m/Y') : '',
                $loan ? ($loan->login_date ? \Carbon\Carbon::parse($loan->login_date)->format('d/m/Y') : '') : '',
                $loan->app_number ?? '',
                $loan->logged_bank ?? '',
                $loan->promo_code ?? '',
                $loan ? number_format($loan->req_amount ?? 0) : '',
                $loan ? number_format($loan->approved_amount ?? 0) : '',
                $loan->status ?? '',
                $loan->remarks ?? '',
                $loan ? number_format($loan->net_salary ?? 0) : '',
                $loan->bank_ac ?? '',
                $loan->category ?? '',
                $loan->bank_manager ?? '',
                $loan->dsa_name ?? '',
                $loan->ref_1 ?? '',
                $loan->ref_2 ?? '',
                $loan->tenure ?? '',
                $loan->location ?? '',
                $loan ? ($loan->date ? \Carbon\Carbon::parse($loan->date)->format('d/m/Y') : '') : '',
                $loan->mode ?? '',
                $loan && $loan->aadhar_card_front_upload ? url('storage/' . $loan->aadhar_card_front_upload) : '',
                $loan && $loan->aadhar_card_back_upload ? url('storage/' . $loan->aadhar_card_back_upload) : '',
                $loan && $loan->pan_upload ? url('storage/' . $loan->pan_upload) : '',
                $loan && $loan->bank_statement_upload ? url('storage/' . $loan->bank_statement_upload) : '',
                $loan && $loan->salary_slip_upload ? url('storage/' . $loan->salary_slip_upload) : '',
            ];
        }
        
        return $rows;
    }
}
