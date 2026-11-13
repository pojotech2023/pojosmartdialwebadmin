<?php

namespace App\Http\Controllers;

use App\Models\AssignBase;
use App\Models\User;
use App\Models\Product;
use App\Models\LeadDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountsController extends Controller
{
    /**
     * Accounts Team page — shows all leads that have been assigned to an Account user.
     * Admin sees all; Account user sees only their own assigned leads.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $isAdmin     = $currentUser->isAdmin();
        $isAccount   = $currentUser->isAccount();

        if (!$isAdmin && !$isAccount) {
            return redirect()->route('dashboard')->with('error', 'Access restricted to the Accounts Team.');
        }

        // Product dropdown
        $products = Product::where('is_active', true)->orderBy('name')->pluck('name', 'id');

        // Account users dropdown (for filter)
        $accountUsers = User::whereRaw("LOWER(TRIM(role)) IN ('account', 'accounts')")
            ->orderBy('name')->pluck('name', 'id');

        // Base query: only leads with assigned_to set
        $query = AssignBase::with(['employee', 'product', 'assignedUser', 'leadDetail'])
            ->whereNotNull('assigned_to');

        if (!$isAdmin) {
            // Account user: show only leads assigned to them
            $query->where('assigned_to', $currentUser->id);
        }

        // --- Filters ---
        if ($request->filled('account_user_id') && $isAdmin) {
            $query->where('assigned_to', $request->account_user_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('account_status')) {
            $query->where('account_status', $request->account_status);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('assign_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('assign_date', '<=', $request->to_date);
        }

        $paginatedRows = $query
            ->orderBy('assign_date', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('accounts.index', compact(
            'paginatedRows',
            'products',
            'accountUsers',
            'isAdmin',
            'isAccount'
        ));
    }
}
