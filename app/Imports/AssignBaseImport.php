<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\AssignBase;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class AssignBaseImport implements ToCollection
{
    public function collection(Collection $collection)
    {
        foreach ($collection as $index => $row) {

            // Skip header row
            if ($index === 0) {
                continue;
            }

            AssignBase::create([
                'emp_id'        => (int) $row[0],
                'customer_name' => trim($row[1]),
                'company_name'  => trim($row[2]),
                'status'        => 'NEW', // default
                'mobile'        => trim($row[3]),
                'assign_date'   => Date::excelToDateTimeObject($row[4])->format('Y-m-d'),
            ]);
        }
    }
}
