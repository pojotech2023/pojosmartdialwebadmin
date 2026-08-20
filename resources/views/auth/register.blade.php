<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create your profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background-color: #f5f7fb;
            padding: 40px 16px;
            display: flex;
            justify-content: center;
        }

        .page {
            max-width: 900px;
            width: 100%;
        }

        .top-login {
            text-align: right;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .top-login a {
            color: #0073e6;
            text-decoration: none;
            font-weight: 500;
        }

        .card {
            background: #ffffff;
            border-radius: 16px;
            padding: 32px 32px 28px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 32px;
        }

        @media (max-width: 768px) {
            .card {
                grid-template-columns: 1fr;
            }
        }

        .card-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .card-subtitle {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .field {
            margin-bottom: 16px;
        }

        .label {
            font-size: 13px;
            margin-bottom: 6px;
            display: block;
            font-weight: 500;
        }

        .label span.req {
            color: #ef4444;
        }

        .input, .select {
            width: 100%;
            padding: 11px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            font-size: 14px;
            outline: none;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            background-color: #f9fafb;
        }

        .input:focus, .select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 1px rgba(37,99,235,0.25);
            background-color: #ffffff;
        }

        .hint {
            font-size: 11px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .mobile-wrapper {
            display: flex;
            gap: 8px;
        }

        .mobile-prefix {
            padding: 11px 12px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #f3f4f6;
            font-size: 14px;
            min-width: 70px;
            text-align: center;
        }

        .status-wrapper {
            display: flex;
            gap: 12px;
            margin-top: 4px;
            flex-wrap: wrap;
        }

        .status-option {
            flex: 1 1 150px;
            border-radius: 12px;
            border: 1px solid #d1d5db;
            padding: 10px 12px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            cursor: pointer;
            background: #f9fafb;
            transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
        }

        .status-option input {
            margin-top: 3px;
        }

        .status-option-title {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .status-option-text {
            font-size: 11px;
            color: #6b7280;
        }

        .status-option:hover {
            border-color: #2563eb;
            background: #ffffff;
            box-shadow: 0 0 0 1px rgba(37,99,235,0.07);
        }

        .checkbox-row {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 12px;
            margin: 12px 0 16px;
        }

        .checkbox-row input {
            margin-top: 2px;
        }

        .btn-primary {
            display: inline-block;
            background: #2563eb;
            color: white;
            border-radius: 999px;
            padding: 10px 24px;
            border: none;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 8px;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .terms {
            font-size: 11px;
            color: #6b7280;
            margin-top: 10px;
        }

        .terms a {
            color: #2563eb;
            text-decoration: none;
        }

        .right-panel {
            border-left: 1px solid #e5e7eb;
            padding-left: 24px;
        }

        @media (max-width: 768px) {
            .right-panel {
                border-left: none;
                border-top: 1px solid #e5e7eb;
                padding-left: 0;
                padding-top: 20px;
                margin-top: 12px;
            }
        }

        .right-or {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: #9ca3af;
            margin-bottom: 12px;
        }

        .right-or::before,
        .right-or::after {
            content: "";
            flex: 1;
            height: 1px;
            background: #e5e7eb;
        }

        .google-btn {
            border-radius: 999px;
            border: 1px solid #d1d5db;
            padding: 9px 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            cursor: pointer;
            background: #ffffff;
        }

        .google-circle {
            width: 18px;
            height: 18px;
            border-radius: 999px;
            border: 2px solid #2563eb;
            display: inline-block;
        }

        .alert-success {
            background: #ecfdf3;
            border: 1px solid #22c55e;
            color: #166534;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 10px;
        }

        .alert-error ul {
            list-style: disc;
            padding-left: 18px;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #f87171;
            color: #b91c1c;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="page">

    <div class="top-login">
        Already Registered?
        <a href="{{ url('/login') }}">Login here</a>
    </div>

    <div class="card">

        {{-- LEFT: FORM --}}
        <div>
            <div class="card-title">Create your profile</div>
            <div class="card-subtitle">Search &amp; apply to jobs with your account.</div>

            {{-- Success message --}}
            @if(session('success'))
                <div class="alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Validation errors --}}
            @if($errors->any())
                <div class="alert-error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.post') }}">
                @csrf

                {{-- Full name --}}
                <div class="field">
                    <label class="label">
                        Full name <span class="req">*</span>
                    </label>
                    <input class="input" type="text" name="name"
                           value="{{ old('name') }}" placeholder="What is your name?" required>
                </div>

                {{-- Email --}}
                <div class="field">
                    <label class="label">
                        Email ID <span class="req">*</span>
                    </label>
                    <input class="input" type="email" name="email"
                           value="{{ old('email') }}" placeholder="Tell us your Email ID" required>
                    <div class="hint">We'll send relevant updates to this email.</div>
                </div>

                {{-- Password --}}
                <div class="field">
                    <label class="label">
                        Password <span class="req">*</span>
                    </label>

                    <div style="position: relative;">
                        <input class="input" 
                            type="password" 
                            id="reg_password"
                            name="password"
                            placeholder="Minimum 6 characters" 
                            required>

                        <span onclick="toggleRegPassword()" 
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
                                    cursor: pointer; font-size: 13px; color: #2563eb;">
                            Show
                        </span>
                    </div>
                </div>


                {{-- Confirm Password --}}
                <div class="field">
                    <label class="label">
                        Confirm Password <span class="req">*</span>
                    </label>

                    <div style="position: relative;">
                        <input class="input" 
                            type="password" 
                            id="reg_confirm_password"
                            name="password_confirmation"
                            placeholder="Re-enter your password" 
                            required>

                        <span onclick="toggleRegConfirmPassword()" 
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
                                    cursor: pointer; font-size: 13px; color: #2563eb;">
                            Show
                        </span>
                    </div>
                </div>


                {{-- Mobile --}}
                <div class="field">
                    <label class="label">
                        Mobile number <span class="req">*</span>
                    </label>
                    <div class="mobile-wrapper">
                        <div class="mobile-prefix">+91</div>
                        <input class="input" type="text" name="mobile"
                               value="{{ old('mobile') }}" placeholder="Enter your mobile number">
                    </div>
                    <div class="hint">Recruiters will contact you on this number.</div>
                </div>

                {{-- Work status --}}
                <div class="field">
                    <label class="label">
                        Work status <span class="req">*</span>
                    </label>

                    <div class="status-wrapper">
                        <label class="status-option">
                            <input type="radio" name="work_status" value="experienced"
                                   {{ old('work_status') === 'experienced' ? 'checked' : '' }}>
                            <div>
                                <div class="status-option-title">I'm experienced</div>
                                <div class="status-option-text">
                                    I have work experience (excluding internships)
                                </div>
                            </div>
                        </label>

                        <label class="status-option">
                            <input type="radio" name="work_status" value="fresher"
                                   {{ old('work_status') === 'fresher' ? 'checked' : '' }}>
                            <div>
                                <div class="status-option-title">I'm a fresher</div>
                                <div class="status-option-text">
                                    I am a student / haven't worked after graduation
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- Updates checkbox --}}
                <div class="checkbox-row">
                    <input type="checkbox" name="updates" value="1"
                           {{ old('updates') ? 'checked' : '' }}>
                    <span>Send me important updates &amp; promotions via SMS, email, and WhatsApp.</span>
                </div>

                {{-- Button --}}
                <button type="submit" class="btn-primary">
                    Register now
                </button>

                <div class="terms">
                    By clicking Register, you agree to the
                    <a href="#">Terms and Conditions</a> &amp;
                    <a href="#">Privacy Policy</a>.
                </div>
            </form>
        </div>

        {{-- RIGHT: CONTINUE WITH GOOGLE --}}
        <div class="right-panel">
            <div class="right-or">Or</div>
            <button type="button" class="google-btn">
                <span class="google-circle"></span>
                <span>Continue with Google</span>
            </button>
            <div class="hint" style="margin-top: 8px;">
                (Button for design only – functionality can be added later.)
            </div>
        </div>

    </div>
</div>
<script>
    function toggleRegPassword() {
        const input = document.getElementById('reg_password');
        const toggle = event.target;
        if (input.type === 'password') {
            input.type = 'text';
            toggle.innerText = 'Hide';
        } else {
            input.type = 'password';
            toggle.innerText = 'Show';
        }
    }

    function toggleRegConfirmPassword() {
        const input = document.getElementById('reg_confirm_password');
        const toggle = event.target;
        if (input.type === 'password') {
            input.type = 'text';
            toggle.innerText = 'Hide';
        } else {
            input.type = 'password';
            toggle.innerText = 'Show';
        }
    }
</script>

</body>
</html>
