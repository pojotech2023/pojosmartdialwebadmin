<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AssignBaseImport;
use App\Models\User;
use App\Models\AssignBase;
use App\Models\Product;
use App\Models\Notification;
use App\Models\LoanApplication;
use App\Models\WhatsappNotification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssignBaseController extends Controller
{
    /* ================= ASSIGN BASE LIST ================= */
    public function index(Request $request)
    {
        // Employees dropdown
        $employees = User::orderBy('name')->pluck('name', 'id');

        // Products dropdown
        $products = Product::where('is_active', true)->orderBy('name')->pluck('name', 'id');

        // Base DB query
        $query = AssignBase::with(['employee', 'product']);

        /* ========== EMPLOYEE FILTER ========== */
        if ($request->filled('employee_id')) {
            $query->where('emp_id', $request->employee_id);
        }

        /* ========== PRODUCT TYPE FILTER ========== */
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
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

        /* ========== BULK DELETE FILTERED RECORDS ========== */
        if ($request->input('action') === 'delete') {
            $ids = (clone $query)->pluck('id')->toArray();
            $count = count($ids);

            if ($count === 0) {
                return redirect()
                    ->route('assign-base.index', $request->except('action'))
                    ->with('error', 'No matching records found to delete.');
            }

            DB::beginTransaction();
            try {
                // Delete or disassociate child references first to prevent foreign key errors
                Notification::whereIn('assign_base_id', $ids)->delete();
                LoanApplication::whereIn('assign_base_id', $ids)->delete();
                WhatsappNotification::whereIn('assign_base_id', $ids)->update(['assign_base_id' => null]);

                // Delete the matching AssignBase records
                AssignBase::whereIn('id', $ids)->delete();

                DB::commit();

                return redirect()
                    ->route('assign-base.index', $request->except('action'))
                    ->with('success', "{$count} record(s) deleted successfully!");
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()
                    ->route('assign-base.index', $request->except('action'))
                    ->with('error', 'Failed to delete records: ' . $e->getMessage());
            }
        }

        // Pagination (DB level – BEST)
        $paginatedRows = $query
            ->orderBy('assign_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('assign-base.index', compact(
            'employees',
            'products',
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

        $assignBase = AssignBase::find($request->id);
        if (!$assignBase) {
            return redirect()
                ->route('assign-base.index')
                ->with('error', 'Record not found or already deleted.');
        }

        DB::beginTransaction();
        try {
            // Delete or disassociate child references first to satisfy foreign key constraints
            Notification::where('assign_base_id', $request->id)->delete();
            LoanApplication::where('assign_base_id', $request->id)->delete();
            WhatsappNotification::where('assign_base_id', $request->id)->update(['assign_base_id' => null]);

            $assignBase->delete();

            DB::commit();

            return redirect()
                ->route('assign-base.index')
                ->with('success', 'Record deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->route('assign-base.index')
                ->with('error', 'Failed to delete record: ' . $e->getMessage());
        }
    }
}
