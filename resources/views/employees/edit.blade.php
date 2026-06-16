@extends('layouts.app')

@section('content')

<style>
    .card-box {
        background: #ffffff;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        width: 60%;
    }

    .form-label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        display: block;
        color: #374151;
    }

    input {
        width: 100%;
        padding: 9px 10px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        margin-bottom: 12px;
    }

    .btn-save {
        background: #2563eb;
        color: #fff;
        border: none;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
    }

    .btn-cancel {
        background: #e5e7eb;
        color: #111827;
        border: none;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        margin-left: 8px;
    }
</style>

<h3>Edit Employee</h3>

<div class="card-box">

<form method="POST" action="{{ route('employees.update', $employee->id) }}">
    @csrf
    @method('PUT')

    {{-- Name --}}
    <label class="form-label">Full Name</label>
    <input type="text" name="name"
           value="{{ $employee->name }}" required>

    {{-- Mobile --}}
    <label class="form-label">Mobile Number</label>
    <input type="text" name="mobile"
           value="{{ $employee->mobile }}" required>

    {{-- Password (optional) --}}
    <label class="form-label">New Password (optional)</label>
    <input type="password" name="password"
           placeholder="Leave blank to keep current password">

    {{-- Confirm Password --}}
    <label class="form-label">Confirm Password</label>
    <input type="password" name="password_confirmation"
           placeholder="Confirm new password">

    <div style="margin-top:15px">
        <a href="{{ route('employees.index') }}">
            <button type="button" class="btn-cancel">Cancel</button>
        </a>

        <button type="submit" class="btn-save">Update</button>
    </div>
</form>

</div>

@endsection
