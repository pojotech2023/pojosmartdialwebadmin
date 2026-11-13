@extends('layouts.app')

@section('page-title', 'Add Employee')

@section('content')

@push('styles')
<style>
    td.label {
        width: 35%;
        font-weight: 600;
        color: #374151;
        background: #f9fafb;
        border-radius: 6px;
    }

    td input { width: 100%; }

    .input-wrap { position: relative; width: 100%; }

    .input-wrap input { padding-right: 34px; }

    .eye-icon {
        position: absolute;
        top: 50%;
        right: 12px;
        transform: translateY(-50%);
        cursor: pointer;
        color: var(--primary);
        font-size: 14px;
    }
</style>
@endpush

<div class="page-title">Add Employee</div>
<div class="page-subtitle">Fill in employee details below</div>

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

    <form method="POST" action="{{ route('employees.store') }}">
        @csrf

        <table>
            <tr>
                <td class="label">Full Name</td>
                <td>
                    <input type="text"
                           name="name"
                           placeholder="Enter full name"
                           required>
                </td>
            </tr>

            <tr>
                <td class="label">Role</td>
                <td>
                    <select id="employee-role" name="role" class="cal-select" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;background:#fff;" required>
                        <option value="Telecaller" {{ old('role', 'Telecaller') === 'Telecaller' ? 'selected' : '' }}>
                            Telecaller
                        </option>
                        <option value="Account" {{ old('role') === 'Account' ? 'selected' : '' }}>
                            Account
                        </option>
                    </select>
                </td>
            </tr>

            <tr id="monthly-target-row">
                <td class="label">Monthly Target (₹)</td>
                <td>
                    <input type="number"
                           id="monthly-target"
                           name="monthly_target"
                           value="{{ old('monthly_target') }}"
                           min="0"
                           step="0.01"
                           placeholder="Enter monthly target">
                </td>
            </tr>

            <tr>
                <td class="label">Mobile Number</td>
                <td>
                    <input type="text"
                           name="mobile"
                           value="{{ old('mobile') }}"
                           placeholder="Enter mobile number"
                           required>
                </td>
            </tr>

            <tr>
                <td class="label">Email ID</td>
                <td>
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="Enter email ID"
                           required>
                </td>
            </tr>

            <tr>
                <td class="label">Password</td>
                <td>
                    <div class="input-wrap">
                        <input type="password"
                               id="password"
                               name="password"
                               placeholder="Enter password"
                               required>
                        <i class="fa-solid fa-eye eye-icon"
                           id="eye1"
                           onclick="togglePassword('password','eye1')"></i>
                    </div>
                </td>
            </tr>

            <tr>
                <td class="label">Confirm Password</td>
                <td>
                    <div class="input-wrap">
                        <input type="password"
                               id="confirm_password"
                               name="password_confirmation"
                               placeholder="Confirm password"
                               required>
                        <i class="fa-solid fa-eye eye-icon"
                           id="eye2"
                           onclick="togglePassword('confirm_password','eye2')"></i>
                    </div>
                </td>
            </tr>
        </table>

        <div style="margin-top:18px; text-align:right;">
            <a href="{{ route('employees.index') }}">
                <button type="button" class="btn-cancel">Cancel</button>
            </a>
            <button type="submit" class="btn-save">Save Employee</button>
        </div>

    </form>

</div>

<script>
    const employeeRole = document.getElementById('employee-role');
    const monthlyTargetRow = document.getElementById('monthly-target-row');
    const monthlyTargetInput = document.getElementById('monthly-target');

    function updateMonthlyTargetField() {
        const isTelecaller = employeeRole.value === 'Telecaller';
        monthlyTargetRow.hidden = !isTelecaller;
        monthlyTargetInput.required = isTelecaller;
    }

    employeeRole.addEventListener('change', updateMonthlyTargetField);
    updateMonthlyTargetField();

    function togglePassword(inputId, eyeId) {
        const input = document.getElementById(inputId);
        const eye = document.getElementById(eyeId);

        if (input.type === 'password') {
            input.type = 'text';
            eye.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            eye.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>

@endsection
