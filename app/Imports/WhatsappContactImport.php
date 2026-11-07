<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\WhatsappContact;

class WhatsappContactImport implements ToCollection
{
    public function collection(Collection $collection)
    {
        foreach ($collection as $index => $row) {

            // Skip header row
            if ($index === 0) {
                continue;
            }

            $mobile = trim((string) $row[0]);

            if ($mobile === '') {
                continue;
            }

            WhatsappContact::updateOrCreate(
                ['mobile' => $mobile],
                ['name' => isset($row[1]) ? trim((string) $row[1]) : null]
            );
        }
    }
}
