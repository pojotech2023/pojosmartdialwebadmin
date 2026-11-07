<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\AssignBase;
use App\Models\Product;
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

            if (empty($row[0]) && empty($row[1])) {
                continue;
            }

            $productName = isset($row[5]) && trim($row[5]) !== '' ? trim($row[5]) : null;
            $productId = null;

            if ($productName) {
                $product = Product::firstOrCreate(
                    ['name' => $productName],
                    ['is_active' => true]
                );
                $productId = $product->id;
            }

            $assignDate = now()->toDateString();
            if (isset($row[4]) && $row[4] !== '') {
                try {
                    $assignDate = Date::excelToDateTimeObject($row[4])->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $assignDate = Carbon::parse($row[4])->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $assignDate = now()->toDateString();
                    }
                }
            }

            AssignBase::create([
                'emp_id'        => (int) $row[0],
                'customer_name' => trim($row[1] ?? ''),
                'company_name'  => trim($row[2] ?? ''),
                'product_id'    => $productId,
                'product_type'  => $productName,
                'status'        => 'NEW', // default
                'mobile'        => trim($row[3] ?? ''),
                'assign_date'   => $assignDate,
            ]);
        }
    }
}
