<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function getCustomerNotes(Request $request, $assignBaseId = null)
    {
        $assignBaseId = $assignBaseId ?? $request->input('assign_base_id');

        $notes = Notification::with('employee:id,name')
                    ->where('assign_base_id', $assignBaseId)
                    ->orderBy('id', 'desc')
                    ->get()
                    ->map(function ($n) {
                        return [
                            'id'                => $n->id,
                            'assign_base_id'    => $n->assign_base_id,
                            'emp_id'            => $n->emp_id,
                            'notification_date' => $n->notification_date ? Carbon::parse($n->notification_date)->format('d/m/Y') : null,
                            'notification_time' => $n->notification_time,
                            'remarks'           => $n->remarks,
                            'is_sent'           => (bool) $n->is_sent,
                            'status'            => $n->status,
                            'appointment_date'  => $n->appointment_date,
                            'appointment_time'  => $n->appointment_time,
                            'location'          => $n->location,
                            'alternate_number'  => $n->alternate_number,
                            'employee'          => $n->employee ? [
                                'id'   => $n->employee->id,
                                'name' => $n->employee->name,
                            ] : null,
                            'created_at'        => $n->created_at ? $n->created_at->toDateTimeString() : null,
                            'updated_at'        => $n->updated_at ? $n->updated_at->toDateTimeString() : null,
                        ];
                    });

        return response()->json([
            'status' => true,
            'data'   => $notes
        ]);
    }
}
