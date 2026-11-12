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
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    @stack('styles')
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
        <div class="topbar-user">
            <div style="display:flex;flex-direction:column;align-items:flex-end;">
                <span style="font-weight:700;color:#0f172a;">{{ auth()->user()->name ?? '' }}</span>
                <span style="font-size:10.5px;font-weight:800;color:{{ auth()->user() && auth()->user()->isAccount() ? '#047857' : (auth()->user() && auth()->user()->isAdmin() ? '#b45309' : '#4338ca') }};text-transform:uppercase;letter-spacing:0.04em;">
                    {{ auth()->user()->role ?? (auth()->user() && auth()->user()->isAdmin() ? 'Admin' : 'Account') }}
                </span>
            </div>
            <div class="topbar-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
        </div>
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
