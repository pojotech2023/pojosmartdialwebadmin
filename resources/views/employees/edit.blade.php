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
