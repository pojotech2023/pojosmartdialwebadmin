<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20|unique:users,mobile,' . $user->id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password|current_password',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = trim($validated['name']);
        $user->mobile = $validated['mobile'] ?? null;
        $user->email = $validated['email'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Profile settings updated successfully.');
    }
}
