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
        $headerMap = [];

        foreach ($collection as $index => $row) {

            // Detect header mapping on the first row
            if ($index === 0) {
                foreach ($row as $colIdx => $colName) {
                    $norm = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '', (string) $colName)));
                    if (str_contains($norm, 'empid') || $norm === 'emp') {
                        $headerMap['emp_id'] = $colIdx;
                    } elseif (str_contains($norm, 'customer')) {
                        $headerMap['customer_name'] = $colIdx;
                    } elseif (str_contains($norm, 'company')) {
                        $headerMap['company_name'] = $colIdx;
                    } elseif (str_contains($norm, 'product')) {
                        $headerMap['product_type'] = $colIdx;
                    } elseif (str_contains($norm, 'mobile') || str_contains($norm, 'phone')) {
                        $headerMap['mobile'] = $colIdx;
                    } elseif (str_contains($norm, 'date')) {
                        $headerMap['assign_date'] = $colIdx;
                    }
                }
                continue;
            }

            // Determine column indexes with smart fallback
            $empIdCol    = $headerMap['emp_id'] ?? 0;
            $customerCol = $headerMap['customer_name'] ?? 1;
            $companyCol  = $headerMap['company_name'] ?? 2;
            $productCol  = $headerMap['product_type'] ?? (isset($row[5]) ? 3 : null);
            $mobileCol   = $headerMap['mobile'] ?? (isset($headerMap['product_type']) && $headerMap['product_type'] == 3 ? 4 : 3);
            $dateCol     = $headerMap['assign_date'] ?? (isset($headerMap['product_type']) && $headerMap['product_type'] == 3 ? 5 : 4);

            $empIdVal    = isset($row[$empIdCol]) ? trim((string) $row[$empIdCol]) : '';
            $customerVal = isset($row[$customerCol]) ? trim((string) $row[$customerCol]) : '';

            if (empty($empIdVal) && empty($customerVal)) {
                continue;
            }

            // Resolve product
            $productName = ($productCol !== null && isset($row[$productCol]) && trim($row[$productCol]) !== '')
                ? trim($row[$productCol])
                : (isset($row[5]) && trim($row[5]) !== '' ? trim($row[5]) : null);

            $productId = null;
            if ($productName) {
                $product = Product::firstOrCreate(
                    ['name' => $productName],
                    ['is_active' => true]
                );
                $productId = $product->id;
            }

            // Resolve date
            $rawDate = isset($row[$dateCol]) ? $row[$dateCol] : null;
            $assignDate = now()->toDateString();
            if ($rawDate !== null && $rawDate !== '') {
                try {
                    $assignDate = Date::excelToDateTimeObject($rawDate)->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $cleanDate = str_replace('-', '/', (string) $rawDate);
                        $parts = explode('/', $cleanDate);
                        if (count($parts) === 3 && strlen($parts[2]) === 4) {
                            $assignDate = Carbon::createFromFormat('d/m/Y', sprintf('%02d/%02d/%04d', $parts[0], $parts[1], $parts[2]))->format('Y-m-d');
                        } else {
                            $assignDate = Carbon::parse($rawDate)->format('Y-m-d');
                        }
                    } catch (\Exception $e2) {
                        try {
                            $assignDate = Carbon::parse($rawDate)->format('Y-m-d');
                        } catch (\Exception $e3) {
                            $assignDate = now()->toDateString();
                        }
                    }
                }
            }

            AssignBase::create([
                'emp_id'        => (int) $empIdVal,
                'customer_name' => $customerVal,
                'company_name'  => isset($row[$companyCol]) ? trim((string) $row[$companyCol]) : '',
                'product_id'    => $productId,
                'product_type'  => $productName,
                'status'        => 'NEW', // default
                'mobile'        => isset($row[$mobileCol]) ? trim((string) $row[$mobileCol]) : '',
                'assign_date'   => $assignDate,
            ]);
        }
    }
}
