<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;

class CustomerController extends Controller
{
    public function getCustomerNotes(Request $request, $assignBaseId = null)
    {
        $assignBaseId = $assignBaseId ?? $request->input('assign_base_id');

        $notes = Notification::with('employee:id,name')
                    ->where('assign_base_id', $assignBaseId)
                    ->orderBy('id', 'desc')
                    ->get();

        return response()->json([
            'status' => true,
            'data'   => $notes
        ]);
    }
}
