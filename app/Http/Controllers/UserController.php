<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = \App\Models\User::where('id', '!=', auth()->id())
                    ->orderBy('created_at', 'desc')   
                    ->get();

        return view('employees.index', compact('users'));
    }


    public function create()
    {
        return view('employees.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'mobile'   => 'required|digits_between:8,15|unique:users,mobile',
            'role'     => 'required|in:Telecaller,Account',
            'email'    => 'nullable|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);

        User::create([
            'name'     => trim($request->name),
            'mobile'   => trim($request->mobile),
            'role'     => $request->role,
            'email'    => $request->filled('email') ? trim($request->email) : null,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('employees.index')->with('success', "Employee created successfully with role '{$request->role}'");
    }


    public function edit(User $employee)
    {
        return view('employees.edit', compact('employee'));
    }

   

    public function update(Request $request, $id)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'mobile'   => 'required|numeric|unique:users,mobile,' . $id,
            'role'     => 'required|in:Telecaller,Account,Admin',
            'email'    => 'nullable|email|unique:users,email,' . $id,
            'password' => 'nullable|confirmed|min:6',
        ]);

        $user = User::findOrFail($id);

        $user->name   = trim($request->name);
        $user->mobile = trim($request->mobile);
        if ($request->filled('role')) {
            $user->role = $request->role;
        }
        if ($request->has('email')) {
            $user->email = $request->filled('email') ? trim($request->email) : null;
        }

        // Update password only if filled
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save(); // 🔥 THIS LINE IS CRITICAL

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee updated successfully');
    }


    public function destroy(User $employee)
    {
        if ($employee->id === auth()->id()) {
            return back(); // prevent self delete
        }

        $employee->delete();
        return redirect()->route('employees.index');
    }
   
    public function loanApplicationDetails($assignBaseId)
    {
        // Get customer & lead info
        $assignBase = \App\Models\AssignBase::with(['employee', 'assignedUser', 'product', 'leadDetail'])->find($assignBaseId);
        
        if (!$assignBase) {
            abort(404, 'Customer not found');
        }

        $leadDetail = $assignBase->leadDetail;
        $loanApplication = \App\Models\LoanApplication::where('assign_base_id', $assignBaseId)->first();

        return view('loan-application-details', [
            'customer'        => $assignBase,
            'leadDetail'      => $leadDetail,
            'loanApplication' => $loanApplication
        ]);
    }

}

