<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AssignBaseImport;
use App\Models\User;
use App\Models\AssignBase;
use Carbon\Carbon;

class AssignBaseController extends Controller
{
    /* ================= ASSIGN BASE LIST ================= */
    public function index(Request $request)
    {
        // Employees dropdown
        $employees = User::orderBy('name')->pluck('name', 'id');

        // Base DB query
        $query = AssignBase::query();

        /* ========== EMPLOYEE FILTER ========== */
        if ($request->filled('employee_id')) {
            $query->where('emp_id', $request->employee_id);
        }

        /* ========== STATUS FILTER ========== */
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        /* ========== DATE FILTER ========== */
        if ($request->filled('from_date')) {
            $query->whereDate('assign_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('assign_date', '<=', $request->to_date);
        }

        // Pagination (DB level – BEST)
        $paginatedRows = $query
            ->orderBy('assign_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('assign-base.index', compact(
            'employees',
            'paginatedRows'
        ));
    }

    /* ================= IMPORT EXCEL ================= */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        // Import new data (keeping existing data)
        Excel::import(new AssignBaseImport, $request->file('file'));

        return redirect()
            ->route('assign-base.index')
            ->with('success', 'Excel imported successfully! New data added to existing records.');
    }

    /* ================= DELETE SINGLE RECORD ================= */
    public function delete(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        AssignBase::where('id', $request->id)->delete();

        return redirect()
            ->route('assign-base.index')
            ->with('success', 'Record deleted successfully!');
    }
}
