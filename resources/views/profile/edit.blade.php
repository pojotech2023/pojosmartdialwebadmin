@extends('layouts.app')

@section('page-title', 'Profile Settings')

@section('content')
<div class="page-title">Profile Settings</div>
<div class="page-subtitle">Update your account information and password.</div>

<section class="card-box profile-settings-card">
    @if(session('success'))
        <div class="profile-success"><i class="fas fa-circle-check" aria-hidden="true"></i> {{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="profile-errors" role="alert">
            <strong>Please review the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="profile-settings-intro">
        <span class="topbar-avatar profile-settings-avatar">{{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}</span>
        <div>
            <h2>{{ $user->name }}</h2>
            <span class="profile-role">{{ $user->role ?? ($user->isAdmin() ? 'Admin' : 'Account') }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-form">
        @csrf
        @method('PUT')

        <div class="profile-form-grid">
            <label>
                <span class="form-label">Full name</span>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" autocomplete="name" required>
            </label>
            <label>
                <span class="form-label">Mobile number</span>
                <input type="text" name="mobile" value="{{ old('mobile', $user->mobile) }}" autocomplete="tel">
            </label>
            <label class="profile-form-full">
                <span class="form-label">Email address</span>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email">
            </label>
        </div>

        <div class="profile-password-heading">
            <h3>Change password</h3>
            <p>Leave these fields blank if you do not want to change your password.</p>
        </div>
        <div class="profile-form-grid">
            <label>
                <span class="form-label">Current password</span>
                <input type="password" name="current_password" autocomplete="current-password">
            </label>
            <span></span>
            <label>
                <span class="form-label">New password</span>
                <input type="password" name="password" autocomplete="new-password">
            </label>
            <label>
                <span class="form-label">Confirm new password</span>
                <input type="password" name="password_confirmation" autocomplete="new-password">
            </label>
        </div>

        <div class="profile-form-actions">
            <button type="submit" class="btn-save"><i class="fas fa-check" aria-hidden="true"></i> Save changes</button>
        </div>
    </form>
</section>
@endsection

@push('styles')
<style>
    .profile-settings-card { width: 100%; max-width: 860px; padding: 26px; }
    .profile-success, .profile-errors { margin-bottom: 20px; padding: 12px 14px; border-radius: 10px; font-size: 13px; }
    .profile-success { border: 1px solid #a7f3d0; background: #ecfdf5; color: #047857; font-weight: 650; }
    .profile-errors { border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; }
    .profile-errors ul { margin: 6px 0 0; padding-left: 20px; }
    .profile-settings-intro { display: flex; align-items: center; gap: 14px; padding-bottom: 22px; border-bottom: 1px solid #eef0f4; }
    .profile-settings-avatar { width: 48px; height: 48px; font-size: 17px; }
    .profile-settings-intro h2 { margin: 0 0 5px; color: #172033; font-size: 16px; }
    .profile-role { color: #64748b; font-size: 11px; font-weight: 750; letter-spacing: .04em; text-transform: uppercase; }
    .profile-settings-form { padding-top: 22px; }
    .profile-form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
    .profile-form-grid label { display: block; min-width: 0; }
    .profile-form-grid input { width: 100%; min-height: 42px; background: #fff; }
    .profile-form-full { grid-column: 1 / -1; }
    .profile-password-heading { margin: 26px 0 16px; padding-top: 20px; border-top: 1px solid #eef0f4; }
    .profile-password-heading h3 { margin: 0; color: #172033; font-size: 14px; }
    .profile-password-heading p { margin: 5px 0 0; color: #64748b; font-size: 12px; }
    .profile-form-actions { display: flex; justify-content: flex-end; margin-top: 22px; padding-top: 18px; border-top: 1px solid #eef0f4; }
    @media (max-width: 600px) {
        .profile-settings-card { padding: 18px; }
        .profile-form-grid { grid-template-columns: 1fr; }
        .profile-form-full { grid-column: auto; }
        .profile-settings-form .profile-form-grid > span { display: none; }
    }
</style>
@endpush
