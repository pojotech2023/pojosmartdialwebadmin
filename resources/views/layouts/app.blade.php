<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('page-title', 'Admin Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Shared Theme -->
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}?v={{ filemtime(public_path('css/theme.css')) }}">

    @stack('styles')
    <style>
        .sidebar {
            width: 250px !important;
            min-width: 250px !important;
        }

        .sidebar .logo-text,
        .sidebar .menu span {
            display: inline !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .content {
            margin-left: 250px !important;
        }

        @media (max-width: 700px) {
            .sidebar {
                position: relative !important;
                width: 100% !important;
                min-width: 0 !important;
                height: auto !important;
            }

            .content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>

<body>

{{-- ================= SIDEBAR ================= --}}
<div class="sidebar">

    <div class="sidebar-header">
        <div class="logo-badge"><i class="fas fa-bolt"></i></div>
        <span class="logo-text">{{ auth()->user() && auth()->user()->isAccount() ? 'Account Panel' : 'Admin Panel' }}</span>
    </div>

    <ul class="menu">
        <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}">
                <i class="fas fa-chart-line"></i>
                <span>Dashboard</span>
            </a>
        </li>

        {{-- Admin-only sections --}}
        @if(auth()->user() && auth()->user()->isAdmin())
        <li class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
            <a href="{{ route('employees.index') }}">
                <i class="fas fa-users"></i>
                <span>Employees</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('assign-base.*') ? 'active' : '' }}">
            <a href="{{ route('assign-base.index') }}">
                <i class="fas fa-database"></i>
                <span>Assign Base</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('whatsapp.*') ? 'active' : '' }}">
            <a href="{{ route('whatsapp.index') }}">
                <i class="fab fa-whatsapp"></i>
                <span>WhatsApp Notification</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('calendar.*') ? 'active' : '' }}">
            <a href="{{ route('calendar.index') }}">
                <i class="fas fa-calendar-alt"></i>
                <span>Calendar</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('settings.*') || request()->routeIs('products.*') ? 'active' : '' }}">
            <a href="{{ route('settings.index') }}">
                <i class="fas fa-sliders-h"></i>
                <span>Settings</span>
            </a>
        </li>
        @endif

        <div class="menu-divider"></div>

        @if(auth()->user() && !auth()->user()->isAccount())
        <li class="{{ request()->routeIs('follow-ups.*') ? 'active' : '' }}">
            <a href="{{ route('follow-ups.index') }}">
                <i class="fas fa-list-check"></i>
                <span>All Follow Up List</span>
            </a>
        </li>
        @endif

        {{-- Accounts Team — Admin view --}}
        @if(auth()->user() && auth()->user()->isAdmin())
        <li class="{{ request()->routeIs('accounts.*') ? 'active' : '' }}">
            <a href="{{ route('accounts.index') }}">
                <i class="fas fa-user-tie"></i>
                <span>Accounts Team</span>
            </a>
        </li>
        @endif

        <li>
            <a href="{{ route('logout') }}"
               onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

{{-- ================= MAIN CONTENT ================= --}}
<div class="content">

    <div class="topbar">
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        <details class="topbar-profile">
            <summary class="topbar-profile-trigger" aria-label="Open profile menu">
                <span class="topbar-profile-identity">
                    <span class="topbar-profile-name">{{ auth()->user()->name ?? '' }}</span>
                    <span class="topbar-profile-role">{{ auth()->user()->role ?? (auth()->user() && auth()->user()->isAdmin() ? 'Admin' : 'Account') }}</span>
                </span>
                <span class="topbar-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                <i class="fas fa-chevron-down topbar-profile-chevron" aria-hidden="true"></i>
            </summary>
            <div class="topbar-profile-menu">
                <div class="topbar-profile-menu-heading">
                    <span class="topbar-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</span>
                    <span>
                        <strong>{{ auth()->user()->name ?? '' }}</strong>
                        <small>{{ auth()->user()->email ?? auth()->user()->mobile ?? '' }}</small>
                    </span>
                </div>
                <a href="{{ route('profile.edit') }}">
                    <i class="fas fa-user-gear" aria-hidden="true"></i>
                    <span>Profile Settings</span>
                </a>
                <button type="button" class="topbar-profile-logout" onclick="document.getElementById('logout-form').submit();">
                    <i class="fas fa-right-from-bracket" aria-hidden="true"></i>
                    <span>Logout</span>
                </button>
            </div>
        </details>
    </div>

    <div class="page-body">
        @yield('content')
    </div>

</div>

<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
    @csrf
</form>

@stack('scripts')

</body>
</html>
