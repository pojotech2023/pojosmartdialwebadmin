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
                <td class="label">Mobile Number</td>
                <td>
                    <input type="text"
                           name="mobile"
                           placeholder="Enter mobile number"
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
