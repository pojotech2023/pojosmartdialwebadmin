@extends('layouts.app')

@section('page-title', 'Edit Employee')

@section('content')

@push('styles')
<style>
    .card-box { max-width: 640px; }
    .card-box input { width: 100%; margin-bottom: 14px; }
</style>
@endpush

<div class="page-title">Edit Employee</div>
<div class="page-subtitle">Update employee details below</div>

<div class="card-box">

@if(isset($errors) && $errors->any())
    <div style="background:#fee2e2;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;font-size:13.5px;font-weight:600;">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('employees.update', $employee->id) }}">
    @csrf
    @method('PUT')

    {{-- Name --}}
    <label class="form-label">Full Name</label>
    <input type="text" name="name"
           value="{{ old('name', $employee->name) }}" required>

    {{-- Role --}}
    <label class="form-label">Role</label>
    <select id="employee-role" name="role" class="cal-select" style="width:100%;margin-bottom:14px;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;background:#fff;" required>
        <option value="Telecaller" {{ old('role', $employee->role ?? 'Telecaller') === 'Telecaller' ? 'selected' : '' }}>
            Telecaller
        </option>
        <option value="Account" {{ old('role', $employee->role ?? '') === 'Account' ? 'selected' : '' }}>
            Account
        </option>
        @if($employee->role === 'Admin')
            <option value="Admin" selected>Admin</option>
        @endif
    </select>

    <div id="monthly-target-field">
        <label class="form-label" for="monthly-target">Monthly Target (₹)</label>
        <input type="number"
               id="monthly-target"
               name="monthly_target"
               value="{{ old('monthly_target', $employee->monthly_target) }}"
               min="0"
               step="0.01"
               placeholder="Enter monthly target">
    </div>

    {{-- Mobile --}}
    <label class="form-label">Mobile Number</label>
    <input type="text" name="mobile"
           value="{{ old('mobile', $employee->mobile) }}" required>

    {{-- Email (optional) --}}
    <label class="form-label">Email ID</label>
    <input type="email" name="email"
           value="{{ old('email', $employee->email) }}"
           placeholder="Enter email ID"
           required>

    {{-- Password (optional) --}}
    <label class="form-label">New Password (optional)</label>
    <input type="password" name="password"
           minlength="6" autocomplete="new-password"
           placeholder="Leave blank to keep current password">

    {{-- Confirm Password --}}
    <label class="form-label">Confirm Password</label>
    <input type="password" name="password_confirmation"
           minlength="6" autocomplete="new-password"
           placeholder="Confirm new password">

    <div style="margin-top:15px">
        <a href="{{ route('employees.index') }}">
            <button type="button" class="btn-cancel">Cancel</button>
        </a>

        <button type="submit" class="btn-save">Update</button>
    </div>
</form>

</div>

<script>
    const employeeRole = document.getElementById('employee-role');
    const monthlyTargetField = document.getElementById('monthly-target-field');
    const monthlyTargetInput = document.getElementById('monthly-target');

    function updateMonthlyTargetField() {
        const isTelecaller = employeeRole.value === 'Telecaller';
        monthlyTargetField.hidden = !isTelecaller;
        monthlyTargetInput.required = isTelecaller;
    }

    employeeRole.addEventListener('change', updateMonthlyTargetField);
    updateMonthlyTargetField();
</script>

@endsection
