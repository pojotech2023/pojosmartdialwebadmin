@extends('layouts.app')

@section('page-title', 'Calendar')

@section('content')
<div class="calendar-page-container">

    {{-- Top Header --}}
    <div class="cal-header-row">
        <div>
            <div class="page-title" style="margin-bottom:4px;">
                <i class="fas fa-calendar-alt" style="color:var(--primary);margin-right:8px;"></i> Activity Calendar
            </div>
            <div class="page-subtitle">
                Track scheduled reminders and WhatsApp notifications by date
                @if(!$isAdmin)
                    <span class="badge-role-pill"><i class="fas fa-lock"></i> Showing your entries only ({{ $currentUser->name }})</span>
                @else
                    <span class="badge-role-pill badge-admin-pill"><i class="fas fa-shield-alt"></i> Admin View (All Employees)</span>
                @endif
            </div>
        </div>

        {{-- View Switcher --}}
        <div class="cal-view-switch">
            <button type="button" class="view-btn active" id="btnMonthView" onclick="setViewMode('month')">
                <i class="fas fa-calendar-grid-5"></i> Month Grid
            </button>
            <button type="button" class="view-btn" id="btnListView" onclick="setViewMode('list')">
                <i class="fas fa-list-ul"></i> Agenda List
            </button>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="cal-stats-grid">
        <div class="cal-stat-card stat-all">
            <div class="cal-stat-icon" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"><i class="fas fa-layer-group"></i></div>
            <div>
                <div class="cal-stat-value" id="statTotal">0</div>
                <div class="cal-stat-label">Total Activities</div>
            </div>
        </div>
        <div class="cal-stat-card stat-reminders">
            <div class="cal-stat-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fas fa-bell"></i></div>
            <div>
                <div class="cal-stat-value" id="statReminders">0</div>
                <div class="cal-stat-label">Reminders</div>
            </div>
        </div>
        <div class="cal-stat-card stat-whatsapp">
            <div class="cal-stat-icon" style="background:linear-gradient(135deg,#10b981,#059669);"><i class="fab fa-whatsapp"></i></div>
            <div>
                <div class="cal-stat-value" id="statWhatsapp">0</div>
                <div class="cal-stat-label">WhatsApp Notifications</div>
            </div>
        </div>
        <div class="cal-stat-card stat-due">
            <div class="cal-stat-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);"><i class="fas fa-clock"></i></div>
            <div>
                <div class="cal-stat-value" id="statDue">0</div>
                <div class="cal-stat-label">Due / Pending</div>
            </div>
        </div>
    </div>

    {{-- Controls & Filter Bar --}}
    <div class="cal-controls-card">
        <div class="cal-controls-left">
            {{-- Month Navigation --}}
            <div class="cal-nav-group">
                <button type="button" class="cal-nav-btn" onclick="prevMonth()" title="Previous Month">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" class="cal-nav-btn cal-today-btn" onclick="goToToday()" title="Go to current date">
                    Today
                </button>
                <button type="button" class="cal-nav-btn" onclick="nextMonth()" title="Next Month">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <div class="cal-current-title-wrap">
                <h2 id="calendarTitle" class="cal-current-title">November 2026</h2>
                <span id="calActiveFiltersText" class="cal-filter-hint"></span>
            </div>
        </div>

        {{-- Filters --}}
        <div class="cal-controls-right">
            {{-- Employee filter (Only selectable if Admin; locked if Employee) --}}
            @if($isAdmin)
                <div class="cal-filter-group">
                    <label class="cal-filter-label"><i class="fas fa-user"></i> Employee</label>
                    <select id="employeeFilter" class="cal-select" onchange="fetchCalendarEvents()">
                        <option value="all">All Employees</option>
                        @foreach($employees as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            @else
                <input type="hidden" id="employeeFilter" value="{{ $currentUser->id }}">
                <div class="cal-filter-group">
                    <label class="cal-filter-label"><i class="fas fa-user-circle"></i> Logged In</label>
                    <div class="cal-employee-fixed">
                        <i class="fas fa-user"></i> {{ $currentUser->name }}
                    </div>
                </div>
            @endif

            {{-- Type Filter --}}
            <div class="cal-filter-group">
                <label class="cal-filter-label"><i class="fas fa-filter"></i> Type</label>
                <select id="typeFilter" class="cal-select" onchange="fetchCalendarEvents()">
                    <option value="all">All Types</option>
                    <option value="reminder">🔔 Reminders Only</option>
                    <option value="whatsapp">💬 WhatsApp Only</option>
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="cal-filter-group">
                <label class="cal-filter-label"><i class="fas fa-info-circle"></i> Status</label>
                <select id="statusFilter" class="cal-select" onchange="fetchCalendarEvents()">
                    <option value="all">All Statuses</option>
                    <option value="pending">⏳ Pending / Due</option>
                    <option value="sent">✅ Completed / Sent</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Main Calendar Container --}}
    <div class="cal-card-box">
        {{-- Loading overlay --}}
        <div id="calLoader" class="cal-loader" style="display:none;">
            <div class="cal-spinner"><i class="fas fa-spinner fa-spin"></i></div>
            <p>Loading events...</p>
        </div>

        {{-- MONTH GRID VIEW --}}
        <div id="monthViewWrapper">
            <div class="cal-grid-header">
                <div>Sun</div>
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
            </div>
            <div id="calendarGrid" class="cal-grid-body">
                {{-- Injected by JS --}}
            </div>
        </div>

        {{-- AGENDA LIST VIEW --}}
        <div id="listViewWrapper" style="display:none;">
            <div class="cal-list-toolbar">
                <div class="cal-search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="agendaSearch" placeholder="Search customer, mobile, employee, or remarks..." oninput="filterAgendaList()">
                </div>
                <div id="agendaCounter" class="cal-agenda-counter">0 events</div>
            </div>
            <div id="agendaContainer" class="cal-agenda-container">
                {{-- Injected by JS --}}
            </div>
        </div>
    </div>

</div>

{{-- ============ FULL-PAGE DAY / EVENT DETAILS PANEL ============ --}}
<div id="dayDetailModal" class="cal-fullpage-panel">
    {{-- Header Sticky Bar --}}
    <div class="cal-fullpage-header">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="width:44px;height:44px;border-radius:12px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:20px;">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <div class="cal-fullpage-title" id="modalDateTitle">Date Details</div>
                <div class="cal-fullpage-subtitle" id="modalDateSubtitle">0 activities scheduled on this day</div>
            </div>
        </div>

        {{-- Controls --}}
        <div class="cal-fullpage-controls">
            {{-- Quick Search within Day --}}
            <div class="cal-fullpage-search">
                <i class="fas fa-search" style="color:rgba(255,255,255,0.7);"></i>
                <input type="text" id="modalSearchInput" placeholder="Filter by customer, product, mobile, employee..." oninput="filterModalEventsBySearch()">
            </div>

            {{-- Filter Pills --}}
            <div class="cal-fullpage-tabs">
                <button type="button" class="cal-fullpage-tab active" id="modalFilterAll" onclick="filterModalEvents('all')">
                    All (<span id="modalCountAll">0</span>)
                </button>
                <button type="button" class="cal-fullpage-tab" id="modalFilterReminder" onclick="filterModalEvents('reminder')">
                    <i class="fas fa-bell" style="color:#fbbf24"></i> Reminders (<span id="modalCountReminder">0</span>)
                </button>
                <button type="button" class="cal-fullpage-tab" id="modalFilterWhatsapp" onclick="filterModalEvents('whatsapp')">
                    <i class="fab fa-whatsapp" style="color:#34d399"></i> WhatsApp (<span id="modalCountWhatsapp">0</span>)
                </button>
            </div>

            <button type="button" class="cal-fullpage-back-btn" id="modalBackButton" onclick="handleModalBackBtn()" title="Back to Month Calendar">
                <i class="fas fa-arrow-left" id="modalBackBtnIcon"></i> <span id="modalBackBtnText">Back to Calendar</span>
            </button>
        </div>
    </div>

    {{-- Employee Filter Row (visible when multiple employees have activities on this day) --}}
    <div id="modalEmployeeFilterRow" class="cal-fullpage-emp-row" style="display:none;">
        <div class="cal-fullpage-emp-inner">
            <span class="cal-fullpage-emp-label"><i class="fas fa-filter"></i> Filter by Employee:</span>
            <div id="modalEmployeePills" class="cal-fullpage-emp-tabs">
                {{-- Injected dynamically --}}
            </div>
        </div>
    </div>

    {{-- Full Page Body --}}
    <div class="cal-fullpage-body">
        <div id="modalEventsBody" class="cal-fullpage-cards-grid">
            {{-- Injected by JS --}}
        </div>
    </div>
</div>

{{-- ============ EMPLOYEE & CUSTOMER FULL DETAILS MODAL ============ --}}
<div id="employeeCustomerDetailModal" class="cal-submodal-overlay" style="display:none;" onclick="if(event.target === this) closeEmployeeCustomerDetailModal()">
    <div class="cal-submodal-dialog">
        <div class="cal-submodal-header">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="cal-submodal-icon">
                    <i class="fas fa-id-card"></i>
                </div>
                <div>
                    <h3 class="cal-submodal-title" id="empModalTitle">Employee & Customer Details</h3>
                    <p class="cal-submodal-subtitle" id="empModalSubtitle">Complete profile, assignment and activity history</p>
                </div>
            </div>
            <button type="button" class="cal-submodal-close" onclick="closeEmployeeCustomerDetailModal()" title="Close details">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="cal-submodal-body" id="empModalBody">
            {{-- Injected dynamically --}}
        </div>

        <div class="cal-submodal-footer" id="empModalFooter">
            {{-- Injected dynamically --}}
        </div>
    </div>
</div>

<style>
/* =========================================================
   CALENDAR PAGE STYLES
   ========================================================= */
.calendar-page-container {
    max-width: 1380px;
    margin: 0 auto;
    padding-bottom: 50px;
}

.cal-header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 20px;
}

.badge-role-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    background: #e0e7ff;
    color: #4338ca;
    margin-left: 8px;
}
.badge-admin-pill {
    background: #ecfdf5;
    color: #065f46;
}

.cal-view-switch {
    display: inline-flex;
    background: #e2e8f0;
    padding: 4px;
    border-radius: 12px;
    gap: 4px;
}
.view-btn {
    border: none;
    background: transparent;
    padding: 8px 16px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 7px;
}
.view-btn.active {
    background: #ffffff;
    color: var(--primary);
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* Stats Grid */
.cal-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
    gap: 14px;
    margin-bottom: 18px;
}
.cal-stat-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    box-shadow: 0 2px 10px rgba(15,23,42,0.04);
    border: 1px solid #eef2f6;
    transition: transform 0.2s, box-shadow 0.2s;
}
.cal-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(15,23,42,0.08);
}
.cal-stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.cal-stat-value {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}
.cal-stat-label {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
    margin-top: 3px;
}

/* Controls Card */
.cal-controls-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 16px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    box-shadow: 0 2px 12px rgba(15,23,42,0.04);
    border: 1px solid #eef2f6;
    margin-bottom: 18px;
}
.cal-controls-left {
    display: flex;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
}
.cal-nav-group {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    border-radius: 10px;
    padding: 3px;
    gap: 3px;
}
.cal-nav-btn {
    border: none;
    background: transparent;
    padding: 7px 12px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    transition: background 0.15s;
}
.cal-nav-btn:hover { background: #e2e8f0; color: #0f172a; }
.cal-today-btn {
    padding: 7px 14px;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    color: var(--primary);
}
.cal-current-title-wrap {
    display: flex;
    flex-direction: column;
}
.cal-current-title {
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.3px;
}
.cal-filter-hint {
    font-size: 11.5px;
    color: #94a3b8;
    margin-top: 2px;
}

.cal-controls-right {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.cal-filter-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.cal-filter-label {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
}
.cal-select {
    padding: 8px 12px;
    font-size: 13px;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    background: #ffffff;
    color: #1e293b;
    outline: none;
    transition: border-color 0.15s, box-shadow 0.15s;
    min-width: 145px;
}
.cal-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
}
.cal-employee-fixed {
    padding: 8px 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Main Calendar Box */
.cal-card-box {
    background: #ffffff;
    border-radius: 18px;
    padding: 16px;
    box-shadow: 0 4px 20px rgba(15,23,42,0.05);
    border: 1px solid #eef2f6;
    position: relative;
    min-height: 550px;
}
.cal-loader {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(2px);
    z-index: 50;
    border-radius: 18px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #64748b;
    font-weight: 600;
}
.cal-spinner {
    font-size: 32px;
    color: var(--primary);
    margin-bottom: 10px;
}

/* Grid Header */
.cal-grid-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    background: #f8fafc;
    border-radius: 12px;
    padding: 10px 0;
    margin-bottom: 8px;
    border: 1px solid #e2e8f0;
}
.cal-grid-header > div {
    text-align: center;
    font-size: 12.5px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}

/* Grid Body */
.cal-grid-body {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 7px;
}
.cal-day-cell {
    min-height: 120px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    transition: all 0.18s ease;
    cursor: pointer;
    position: relative;
}
.cal-day-cell:hover {
    border-color: #cbd5e1;
    box-shadow: 0 6px 16px rgba(15,23,42,0.06);
    transform: translateY(-1px);
}
.cal-day-other-month {
    background: #fbfcfe;
    opacity: 0.45;
}
.cal-day-today {
    border-color: var(--primary) !important;
    background: #fbfbfe;
    box-shadow: 0 0 0 1.5px var(--primary);
}
.cal-day-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}
.cal-day-number {
    font-size: 13.5px;
    font-weight: 700;
    color: #334155;
    width: 26px;
    height: 26px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.cal-day-today .cal-day-number {
    background: linear-gradient(135deg, var(--primary), #6366f1);
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(79,70,229,0.35);
}
.cal-day-badge-count {
    font-size: 10.5px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    background: #f1f5f9;
    color: #475569;
}

/* Events Inside Day Cells */
.cal-day-events {
    display: flex;
    flex-direction: column;
    gap: 4px;
    flex: 1;
    overflow: hidden;
}
.cal-event-pill {
    padding: 3px 6px;
    border-radius: 7px;
    font-size: 11px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    border-left: 3px solid transparent;
    transition: transform 0.15s ease, filter 0.15s ease;
}
.cal-event-pill:hover {
    transform: scale(1.02);
    filter: brightness(0.96);
}
.cal-pill-content {
    display: flex;
    align-items: center;
    gap: 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}
.cal-pill-title {
    font-weight: 700;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cal-pill-who {
    font-size: 9.5px;
    padding: 1px 4px;
    border-radius: 4px;
    font-weight: 600;
    background: rgba(255,255,255,0.6);
    flex-shrink: 0;
}

/* Color schemes for event pills */
.pill-reminder {
    background: #fef3c7;
    color: #92400e;
    border-left-color: #f59e0b;
}
.pill-reminder.pill-due {
    background: #fee2e2;
    color: #991b1b;
    border-left-color: #ef4444;
}
.pill-reminder.pill-completed {
    background: #e2e8f0;
    color: #475569;
    border-left-color: #94a3b8;
}
.pill-whatsapp {
    background: #d1fae5;
    color: #065f46;
    border-left-color: #10b981;
}

.cal-more-pill {
    font-size: 10.5px;
    font-weight: 700;
    color: var(--primary);
    text-align: center;
    padding: 2px 4px;
    border-radius: 5px;
    background: #eef2ff;
    margin-top: 2px;
}

/* List / Agenda View */
.cal-list-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 14px;
    margin-bottom: 16px;
    border-bottom: 1px solid #eef2f6;
    flex-wrap: wrap;
    gap: 12px;
}
.cal-search-wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    padding: 7px 14px;
    width: 320px;
    max-width: 100%;
}
.cal-search-wrap i { color: #94a3b8; }
.cal-search-wrap input {
    border: none;
    background: transparent;
    outline: none;
    font-size: 13px;
    color: #1e293b;
    width: 100%;
}
.cal-agenda-counter {
    font-size: 13px;
    font-weight: 700;
    color: #64748b;
}

.cal-agenda-group {
    margin-bottom: 22px;
}
.cal-agenda-date-header {
    font-size: 14px;
    font-weight: 800;
    color: #1e293b;
    padding: 8px 12px;
    background: #f1f5f9;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
}
.cal-agenda-date-header i { color: var(--primary); }

.cal-agenda-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 18px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.15s, box-shadow 0.15s;
    cursor: pointer;
}
.cal-agenda-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(15,23,42,0.06);
    border-color: #cbd5e1;
}
.cal-agenda-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.icon-rem { background: #fef3c7; color: #d97706; }
.icon-wa  { background: #d1fae5; color: #059669; }

.cal-agenda-info { flex: 1; min-width: 0; }
.cal-agenda-title {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
}
.cal-agenda-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 16px;
    margin-top: 4px;
    font-size: 12px;
    color: #64748b;
}
.cal-agenda-meta span {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.cal-agenda-who {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-weight: 700;
    color: var(--primary);
    background: #eef2ff;
    padding: 2px 8px;
    border-radius: 6px;
}
.cal-agenda-remarks {
    margin-top: 6px;
    font-size: 12.5px;
    color: #475569;
    background: #f8fafc;
    padding: 6px 10px;
    border-radius: 8px;
    border-left: 3px solid #cbd5e1;
}

/* Full-Page Day Detail Panel */
.cal-fullpage-panel {
    display: none;
    position: fixed;
    inset: 0;
    width: 100vw;
    height: 100vh;
    z-index: 99999;
    background: radial-gradient(circle at 15% 0%, #eef2ff 0%, #f5f7fb 40%, #f5f7fb 100%);
    overflow-y: auto;
    animation: fadeIn 0.2s ease;
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.cal-fullpage-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
    color: #ffffff;
    padding: 18px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: 0 8px 24px rgba(30,27,75,0.25);
    flex-wrap: wrap;
    gap: 15px;
}
.cal-fullpage-title {
    font-size: 20px;
    font-weight: 800;
    letter-spacing: -0.2px;
}
.cal-fullpage-subtitle {
    font-size: 13px;
    color: #cbd5e1;
    margin-top: 2px;
}
.cal-fullpage-controls {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.cal-fullpage-search {
    display: flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 10px;
    padding: 7px 14px;
    width: 260px;
    max-width: 100%;
}
.cal-fullpage-search input {
    border: none;
    background: transparent;
    color: #ffffff;
    font-size: 13px;
    outline: none;
    width: 100%;
}
.cal-fullpage-search input::placeholder {
    color: rgba(255,255,255,0.6);
}

.cal-fullpage-tabs {
    display: inline-flex;
    background: rgba(255,255,255,0.12);
    padding: 3px;
    border-radius: 10px;
    gap: 4px;
}
.cal-fullpage-tab {
    border: none;
    background: transparent;
    color: rgba(255,255,255,0.85);
    padding: 7px 14px;
    border-radius: 8px;
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.cal-fullpage-tab.active {
    background: #ffffff;
    color: var(--primary);
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}

.cal-fullpage-back-btn {
    background: rgba(255,255,255,0.18);
    border: 1px solid rgba(255,255,255,0.3);
    color: #ffffff;
    padding: 9px 18px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.15s;
}
.cal-fullpage-back-btn:hover {
    background: rgba(255,255,255,0.32);
    transform: translateY(-1px);
}

.cal-fullpage-body {
    max-width: 1200px;
    margin: 0 auto;
    padding: 28px 24px 60px 24px;
}

.cal-fullpage-cards-grid {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.fullpage-event-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 20px 24px;
    box-shadow: 0 4px 18px rgba(15,23,42,0.05);
    position: relative;
    border-left: 6px solid transparent;
    transition: transform 0.2s, box-shadow 0.2s;
    animation: slideUp 0.25s ease;
}
@keyframes slideUp { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.fullpage-event-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(15,23,42,0.09);
}
.card-rem { border-left-color: #f59e0b; }
.card-wa  { border-left-color: #10b981; }

.fullpage-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
}
.badge-type-pill {
    padding: 3px 10px;
    border-radius: 8px;
    font-size: 11.5px;
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    letter-spacing: 0.3px;
    text-transform: uppercase;
}
.badge-type-pill.rem { background: #fef3c7; color: #92400e; }
.badge-type-pill.wa  { background: #d1fae5; color: #065f46; }

.badge-product-pill {
    background: #eef2ff;
    color: #4338ca;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 11.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.fullpage-customer-title {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    margin-top: 12px;
    margin-bottom: 8px;
}
.fullpage-meta-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 8px 16px;
    font-size: 13px;
    color: #475569;
    margin-bottom: 12px;
}
.fullpage-meta-grid div {
    display: flex;
    align-items: center;
    gap: 8px;
}
.fullpage-meta-grid i {
    width: 16px;
    color: #94a3b8;
    text-align: center;
}
.fullpage-remarks-box {
    background: #f8fafc;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 13px;
    color: #334155;
    border-left: 3px solid #cbd5e1;
    margin-bottom: 14px;
}
.fullpage-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    flex-wrap: wrap;
    margin-top: 6px;
}
.btn-action-lg {
    padding: 7px 16px;
    border-radius: 9px;
    font-size: 12.5px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-action-lg:hover {
    filter: brightness(0.94);
    transform: translateY(-1px);
}
.btn-action-small {
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: none;
    cursor: pointer;
    transition: opacity 0.15s;
}
.btn-action-small:hover { opacity: 0.9; }
.btn-call { background: #e0f2fe; color: #0369a1; }
.btn-wa-action { background: #d1fae5; color: #047857; }
.btn-seen-action { background: #f1f5f9; color: #475569; }
.btn-view-emp-detail {
    background: #6366f1;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(99, 102, 241, 0.3);
}
.btn-view-emp-detail:hover {
    background: #4f46e5;
    color: #ffffff;
    transform: translateY(-1px);
}

/* =========================================================
   EMPLOYEE & CUSTOMER DETAIL MODAL
   ========================================================= */
.cal-submodal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(6px);
    z-index: 1000000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: fadeIn 0.2s ease;
}
.cal-submodal-dialog {
    background: #ffffff;
    border-radius: 20px;
    max-width: 860px;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.4);
    overflow: hidden;
    animation: popIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes popIn {
    from { transform: scale(0.92) translateY(20px); opacity: 0; }
    to { transform: scale(1) translateY(0); opacity: 1; }
}

/* Employee Filter Bar on Full Page */
.cal-fullpage-emp-row {
    background: #1e1b4b;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    padding: 10px 32px;
}
.cal-fullpage-emp-inner {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.cal-fullpage-emp-label {
    font-size: 12px;
    font-weight: 700;
    color: #cbd5e1;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.cal-fullpage-emp-tabs {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.cal-emp-tab {
    background: rgba(255, 255, 255, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #ffffff;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.cal-emp-tab:hover {
    background: rgba(255, 255, 255, 0.22);
}
.cal-emp-tab.active {
    background: #ffffff;
    color: #312e81;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

/* Card Activity List Inside Consolidated Customer Card */
.card-activities-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin: 14px 0;
    background: #f8fafc;
    border-radius: 12px;
    padding: 12px;
    border: 1px solid #e2e8f0;
}
.card-activity-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    gap: 14px;
    flex-wrap: wrap;
    transition: border-color 0.15s;
}
.card-activity-item:hover {
    border-color: #cbd5e1;
}
.card-activity-item.item-rem {
    border-left: 4px solid #f59e0b;
}
.card-activity-item.item-wa {
    border-left: 4px solid #10b981;
}
.act-time-col {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 170px;
}
.act-time {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.badge-type-mini {
    font-size: 10.5px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.badge-type-mini.rem { background: #fef3c7; color: #92400e; }
.badge-type-mini.wa { background: #d1fae5; color: #065f46; }

.act-desc-col {
    flex: 1;
    min-width: 200px;
    font-size: 12.5px;
    color: #475569;
}
.act-remarks {
    color: #1e293b;
    font-weight: 600;
}
.act-meta-val {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}
.act-status-col {
    display: flex;
    align-items: center;
    gap: 8px;
}

.modal-who-badge-lg {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 700;
    color: #312e81;
    background: #eef2ff;
    padding: 4px 12px;
    border-radius: 8px;
}
.schedule-count-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 8px;
}
.more-activities-clickable {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 6px;
    color: #475569;
    font-size: 13px;
}
.more-activities-clickable:hover {
    background: #eef2ff;
    border-color: #818cf8;
    color: #3730a3;
    transform: translateX(2px);
}
.more-activities-left {
    display: flex;
    align-items: center;
    gap: 8px;
}
.more-activities-right {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: var(--primary);
}
.fullpage-customer-sub {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
    font-size: 13px;
    color: #64748b;
    margin-top: 4px;
}
.fullpage-customer-sub span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.more-schedules-notice {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    background: #eef2ff;
    border: 1px dashed #a5b4fc;
    border-radius: 10px;
    margin-top: 8px;
    cursor: pointer;
    transition: all 0.15s ease;
}
.more-schedules-notice:hover {
    background: #e0e7ff;
    border-color: #818cf8;
    transform: translateY(-1px);
}
.badge-more-count {
    background: #4f46e5;
    color: #ffffff;
    font-size: 11px;
    font-weight: 800;
    padding: 2px 7px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.btn-link-more {
    font-size: 12px;
    font-weight: 700;
    color: #4f46e5;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.cal-submodal-header {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #ffffff;
    padding: 20px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.cal-submodal-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.cal-submodal-title {
    font-size: 18px;
    font-weight: 800;
    margin: 0;
    color: #ffffff;
}
.cal-submodal-subtitle {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.85);
    margin: 2px 0 0 0;
}
.cal-submodal-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #ffffff;
    width: 36px;
    height: 36px;
    border-radius: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: background 0.15s;
}
.cal-submodal-close:hover {
    background: rgba(255, 255, 255, 0.35);
}

.cal-submodal-body {
    padding: 22px 24px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.submodal-section-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px 20px;
}
.submodal-section-card.highlight {
    background: #eef2ff;
    border-color: #c7d2fe;
}
.submodal-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e2e8f0;
}
.submodal-card-title {
    font-size: 13.5px;
    font-weight: 800;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.submodal-grid-2 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 10px 16px;
    font-size: 13px;
}
.submodal-field-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.submodal-label {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.submodal-val {
    font-size: 13.5px;
    font-weight: 700;
    color: #0f172a;
}

.emp-profile-header {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 14px;
    flex-wrap: wrap;
}
.emp-avatar-lg {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
    color: #ffffff;
    font-size: 22px;
    font-weight: 800;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}
.emp-name-lg {
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-mini-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 700;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: opacity 0.15s;
}
.btn-mini-action:hover { opacity: 0.9; }

.cal-submodal-footer {
    padding: 14px 24px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

/* Status Badges */
.status-pill {
    padding: 3px 9px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.status-due { background: #fee2e2; color: #dc2626; }
.status-upcoming { background: #e0e7ff; color: #4338ca; }
.status-completed { background: #ecfdf5; color: #059669; }
.status-pending { background: #fef3c7; color: #d97706; }
.status-failed { background: #fee2e2; color: #dc2626; }

.empty-state {
    text-align: center;
    padding: 50px 20px;
    color: #94a3b8;
}
.empty-state i {
    font-size: 40px;
    margin-bottom: 12px;
    color: #cbd5e1;
}

/* Responsive adjustments */
@media (max-width: 900px) {
    .cal-day-cell { min-height: 90px; padding: 5px; }
    .cal-pill-who { display: none; }
    .cal-controls-card { flex-direction: column; align-items: stretch; }
    .cal-controls-right { justify-content: space-between; }
    .cal-select { flex: 1; min-width: 120px; }
}

@media (max-width: 600px) {
    .cal-grid-header > div { font-size: 11px; }
    .cal-day-cell { min-height: 75px; }
    .cal-event-pill { font-size: 10px; padding: 2px 4px; }
    .cal-pill-title { font-size: 10px; }
    .cal-view-switch { width: 100%; justify-content: center; }
    .view-btn { flex: 1; justify-content: center; }
}
</style>

<script>
// =========================================================
// CALENDAR APPLICATION LOGIC
// =========================================================

const CAL_ROUTES = {
    events: "{{ route('calendar.events') }}",
    markSeen: "{{ route('dashboard.notifications.mark-seen') }}",
    csrf: "{{ csrf_token() }}",
};

let currentYear = {{ $initialDate->year }};
let currentMonth = {{ $initialDate->month }}; // 1-12
let viewMode = 'month'; // 'month' or 'list'
let calendarEventsData = {}; // events_by_date
let calendarAllEvents = [];
let activeModalDate = null;
let activeModalFilter = 'all';
let activeModalEmpFilter = 'all';
let activeDetailGroupKey = null;

const MONTH_NAMES = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

function formatTime12(t) {
    if (!t || t === '—' || t === 'null' || t === 'undefined') return '—';
    if (/am|pm/i.test(t)) return t;
    const parts = t.trim().split(':');
    if (parts.length < 2) return t;
    let h = parseInt(parts[0], 10);
    const m = parts[1].substring(0, 2);
    if (isNaN(h)) return t;
    const ampm = h >= 12 ? 'PM' : 'AM';
    h = h % 12;
    h = h ? h : 12;
    const strH = h < 10 ? '0' + h : h;
    return `${strH}:${m} ${ampm}`;
}

function timeToMinutes(t) {
    if (!t || t === '—') return 9999;
    const match = String(t).match(/(\d{1,2}):(\d{2})\s*(AM|PM)?/i);
    if (!match) return 9999;
    let h = parseInt(match[1], 10);
    const m = parseInt(match[2], 10);
    const ampm = match[3];
    if (ampm) {
        if (ampm.toUpperCase() === 'PM' && h < 12) h += 12;
        if (ampm.toUpperCase() === 'AM' && h === 12) h = 0;
    }
    return h * 60 + m;
}

document.addEventListener('DOMContentLoaded', function() {
    updateTitle();
    fetchCalendarEvents();
});

// View mode switcher
function setViewMode(mode) {
    viewMode = mode;
    document.getElementById('btnMonthView').classList.toggle('active', mode === 'month');
    document.getElementById('btnListView').classList.toggle('active', mode === 'list');
    document.getElementById('monthViewWrapper').style.display = (mode === 'month') ? 'block' : 'none';
    document.getElementById('listViewWrapper').style.display = (mode === 'list') ? 'block' : 'none';

    if (mode === 'list') {
        renderAgendaList();
    }
}

// Navigation
function prevMonth() {
    currentMonth--;
    if (currentMonth < 1) {
        currentMonth = 12;
        currentYear--;
    }
    updateTitle();
    fetchCalendarEvents();
}

function nextMonth() {
    currentMonth++;
    if (currentMonth > 12) {
        currentMonth = 1;
        currentYear++;
    }
    updateTitle();
    fetchCalendarEvents();
}

function goToToday() {
    const today = new Date();
    currentYear = today.getFullYear();
    currentMonth = today.getMonth() + 1;
    updateTitle();
    fetchCalendarEvents();
}

function updateTitle() {
    document.getElementById('calendarTitle').textContent = `${MONTH_NAMES[currentMonth - 1]} ${currentYear}`;
}

// Fetch events from server
function fetchCalendarEvents() {
    const loader = document.getElementById('calLoader');
    loader.style.display = 'flex';

    const empSelect = document.getElementById('employeeFilter');
    const employeeId = empSelect ? empSelect.value : 'all';
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;

    const params = new URLSearchParams({
        year: currentYear,
        month: currentMonth,
        employee_id: employeeId,
        type: typeFilter,
        status: statusFilter
    });

    fetch(`${CAL_ROUTES.events}?${params.toString()}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        loader.style.display = 'none';
        if (data.success) {
            calendarEventsData = data.events_by_date || {};
            calendarAllEvents = data.events || [];

            // Update stats
            document.getElementById('statTotal').textContent = data.counts.total || 0;
            document.getElementById('statReminders').textContent = data.counts.reminders || 0;
            document.getElementById('statWhatsapp').textContent = data.counts.whatsapp || 0;
            document.getElementById('statDue').textContent = data.counts.due || 0;

            // Render view
            renderMonthGrid();
            if (viewMode === 'list') {
                renderAgendaList();
            }
        } else {
            console.error('Server error:', data.error);
        }
    })
    .catch(err => {
        loader.style.display = 'none';
        console.error('Failed to load calendar events:', err);
    });
}

// Render Month Grid
function renderMonthGrid() {
    const grid = document.getElementById('calendarGrid');
    grid.innerHTML = '';

    // First day of month and total days
    const firstDayIndex = new Date(currentYear, currentMonth - 1, 1).getDay(); // 0 = Sun
    const totalDaysInMonth = new Date(currentYear, currentMonth, 0).getDate();
    const prevMonthTotalDays = new Date(currentYear, currentMonth - 1, 0).getDate();

    const today = new Date();
    const todayStr = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

    // 1. Prev month trailing days
    for (let i = firstDayIndex - 1; i >= 0; i--) {
        const dayNum = prevMonthTotalDays - i;
        const prevMonthVal = currentMonth === 1 ? 12 : currentMonth - 1;
        const prevYearVal = currentMonth === 1 ? currentYear - 1 : currentYear;
        const dateStr = `${prevYearVal}-${String(prevMonthVal).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;

        const cell = createDayCell(dayNum, dateStr, true, false);
        grid.appendChild(cell);
    }

    // 2. Current month days
    for (let day = 1; day <= totalDaysInMonth; day++) {
        const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = (dateStr === todayStr);

        const cell = createDayCell(day, dateStr, false, isToday);
        grid.appendChild(cell);
    }

    // 3. Next month trailing days to complete full weeks
    const totalRendered = firstDayIndex + totalDaysInMonth;
    const remainingDays = (7 - (totalRendered % 7)) % 7;
    for (let day = 1; day <= remainingDays; day++) {
        const nextMonthVal = currentMonth === 12 ? 1 : currentMonth + 1;
        const nextYearVal = currentMonth === 12 ? currentYear + 1 : currentYear;
        const dateStr = `${nextYearVal}-${String(nextMonthVal).padStart(2, '0')}-${String(day).padStart(2, '0')}`;

        const cell = createDayCell(day, dateStr, true, false);
        grid.appendChild(cell);
    }
}

// Create a single Day Cell DOM element
function createDayCell(dayNum, dateStr, isOtherMonth, isToday) {
    const cell = document.createElement('div');
    cell.className = 'cal-day-cell' + (isOtherMonth ? ' cal-day-other-month' : '') + (isToday ? ' cal-day-today' : '');
    cell.onclick = () => openDayModal(dateStr, 'all');

    const events = calendarEventsData[dateStr] || [];

    // Header
    const header = document.createElement('div');
    header.className = 'cal-day-header';

    const numSpan = document.createElement('div');
    numSpan.className = 'cal-day-number';
    numSpan.textContent = dayNum;
    header.appendChild(numSpan);

    if (events.length > 0) {
        const countSpan = document.createElement('span');
        countSpan.className = 'cal-day-badge-count';
        countSpan.textContent = events.length;
        header.appendChild(countSpan);
    }
    cell.appendChild(header);

    // Events container
    const eventsContainer = document.createElement('div');
    eventsContainer.className = 'cal-day-events';

    // Group events on this day by (Customer + Employee)
    // so if Nirmal & Divya both have schedules, both employees appear clearly in the cell
    const dayGroups = {};
    events.forEach(evt => {
        const k = `${evt.customer_name}___${evt.added_by}`;
        if (!dayGroups[k]) {
            dayGroups[k] = {
                customer_name: evt.customer_name,
                added_by: evt.added_by,
                count: 0,
                hasDue: false,
                type: evt.type
            };
        }
        dayGroups[k].count++;
        if (evt.status_class === 'due') dayGroups[k].hasDue = true;
    });

    const groupList = Object.values(dayGroups);
    const maxDisplay = 2;
    const displayGroups = groupList.slice(0, maxDisplay);

    displayGroups.forEach(grp => {
        const pill = document.createElement('div');
        let pillClass = (grp.type === 'reminder') ? 'cal-event-pill pill-reminder' : 'cal-event-pill pill-whatsapp';
        if (grp.hasDue) pillClass += ' pill-due';

        pill.className = pillClass;
        pill.title = `${grp.customer_name} | Added by: ${grp.added_by} (${grp.count} ${grp.count > 1 ? 'schedules' : 'schedule'})`;

        const countBadge = grp.count > 1 ? `<span style="background:rgba(0,0,0,0.14);padding:1px 5px;border-radius:10px;font-size:10px;font-weight:800;margin-left:4px;">${grp.count}</span>` : '';

        pill.innerHTML = `
            <div class="cal-pill-content">
                <i class="${grp.type === 'reminder' ? 'fas fa-bell' : 'fab fa-whatsapp'}"></i>
                <span class="cal-pill-title">${escapeHtml(grp.customer_name)}${countBadge}</span>
            </div>
            <span class="cal-pill-who">${escapeHtml(grp.added_by)}</span>
        `;
        pill.onclick = (e) => {
            e.stopPropagation();
            openDayModal(dateStr, grp.added_by);
        };
        eventsContainer.appendChild(pill);
    });

    if (groupList.length > maxDisplay || events.length > displayGroups.reduce((acc, g) => acc + g.count, 0)) {
        const shownCount = displayGroups.reduce((acc, g) => acc + g.count, 0);
        const remaining = events.length - shownCount;
        const morePill = document.createElement('div');
        morePill.className = 'cal-more-pill';
        morePill.innerHTML = `<i class="fas fa-list-ul"></i> +${remaining} View More (${events.length} Total)`;
        morePill.onclick = (e) => {
            e.stopPropagation();
            openDayModal(dateStr, 'all');
        };
        eventsContainer.appendChild(morePill);
    }

    cell.appendChild(eventsContainer);
    return cell;
}

// Render Agenda List View
function renderAgendaList() {
    const container = document.getElementById('agendaContainer');
    const counter = document.getElementById('agendaCounter');
    const searchVal = (document.getElementById('agendaSearch').value || '').toLowerCase().trim();

    // Group events by date
    let filteredEvents = calendarAllEvents;
    if (searchVal) {
        filteredEvents = filteredEvents.filter(e =>
            (e.customer_name && e.customer_name.toLowerCase().includes(searchVal)) ||
            (e.company_name && e.company_name.toLowerCase().includes(searchVal)) ||
            (e.mobile && e.mobile.toLowerCase().includes(searchVal)) ||
            (e.added_by && e.added_by.toLowerCase().includes(searchVal)) ||
            (e.remarks && e.remarks.toLowerCase().includes(searchVal))
        );
    }

    counter.textContent = `${filteredEvents.length} activities found`;

    if (filteredEvents.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No activities found matching your filters for this period.</p>
            </div>
        `;
        return;
    }

    // Group by date string
    const grouped = {};
    filteredEvents.forEach(e => {
        if (!grouped[e.date]) grouped[e.date] = [];
        grouped[e.date].push(e);
    });

    let html = '';
    const sortedDates = Object.keys(grouped).sort();

    sortedDates.forEach(dateStr => {
        const items = grouped[dateStr];
        const dateObj = new Date(dateStr + 'T00:00:00');
        const formattedDate = dateObj.toLocaleDateString('en-GB', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });

        html += `
            <div class="cal-agenda-group">
                <div class="cal-agenda-date-header">
                    <i class="fas fa-calendar-day"></i>
                    <span>${formattedDate}</span>
                    <span style="font-size:12px;color:#64748b;font-weight:600;margin-left:auto;">(${items.length} items)</span>
                </div>
        `;

        items.forEach(e => {
            const isRem = (e.type === 'reminder');
            const iconClass = isRem ? 'icon-rem' : 'icon-wa';
            const iconHtml = isRem ? '<i class="fas fa-bell"></i>' : '<i class="fab fa-whatsapp"></i>';

            html += `
                <div class="cal-agenda-card" onclick="openDayModal('${e.date}', '${escapeHtml(e.added_by)}')">
                    <div class="cal-agenda-icon ${iconClass}">
                        ${iconHtml}
                    </div>
                    <div class="cal-agenda-info">
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                            <div class="cal-agenda-title">${escapeHtml(e.customer_name)}</div>
                            <div style="display:flex;align-items:center;gap:6px;">
                                ${e.product_type ? `<span class="badge-product-pill"><i class="fas fa-cubes"></i> ${escapeHtml(e.product_type)}</span>` : ''}
                                <span class="status-pill status-${e.status_class}">${e.status}</span>
                                <button type="button" class="btn-action-small btn-view-emp-detail" style="padding:4px 10px;font-size:11.5px;margin-left:4px;" onclick="event.stopPropagation();openScheduleDetailModal('${e.id}')" title="View schedule details">
                                    <i class="fas fa-eye"></i> View Details
                                </button>
                            </div>
                        </div>
                        <div class="cal-agenda-meta">
                            <span><i class="fas fa-clock"></i> ${formatTime12(e.time)}</span>
                            <span><i class="fas fa-phone"></i> ${escapeHtml(e.mobile)}</span>
                            ${e.company_name && e.company_name !== '—' ? `<span><i class="fas fa-building"></i> ${escapeHtml(e.company_name)}</span>` : ''}
                            <span class="cal-agenda-who"><i class="fas fa-user-edit"></i> Added by: ${escapeHtml(e.added_by)}</span>
                        </div>
                        ${e.remarks ? `<div class="cal-agenda-remarks"><i class="fas fa-comment-dots" style="color:#94a3b8;margin-right:5px;"></i>${escapeHtml(e.remarks)}</div>` : ''}
                        ${e.amount ? `<div style="margin-top:4px;font-size:12px;color:#059669;font-weight:600;"><i class="fas fa-money-bill-wave"></i> Amount: ₹${e.amount} &bull; Due: ${e.due_date || '—'}</div>` : ''}
                    </div>
                </div>
            `;
        });

        html += `</div>`;
    });

    container.innerHTML = html;
}

function filterAgendaList() {
    renderAgendaList();
}

// Modal Handlers (Full-Page Activity Viewer)
function openDayModal(dateStr, initialEmp = 'all') {
    activeModalDate = dateStr;
    activeModalFilter = 'all';
    activeModalEmpFilter = initialEmp || 'all';
    activeDetailGroupKey = null; // Always open in overview mode

    const searchInput = document.getElementById('modalSearchInput');
    if (searchInput) searchInput.value = '';

    const events = calendarEventsData[dateStr] || [];
    const dateObj = new Date(dateStr + 'T00:00:00');
    const formattedDate = dateObj.toLocaleDateString('en-GB', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    });

    // Unique employees involved
    const employees = [...new Set(events.map(e => e.added_by).filter(Boolean))];

    document.getElementById('modalDateTitle').textContent = formattedDate;
    updateModalSubtitle(events, employees);

    // Reset pill filters
    document.getElementById('modalFilterAll').classList.add('active');
    document.getElementById('modalFilterReminder').classList.remove('active');
    document.getElementById('modalFilterWhatsapp').classList.remove('active');

    // Counts
    updateFilterCounts(events);

    // Populate Employee Filter Tabs (if multiple employees on this day)
    const empRow = document.getElementById('modalEmployeeFilterRow');
    const empPills = document.getElementById('modalEmployeePills');
    if (employees.length > 1) {
        let empHtml = `
            <button type="button" class="cal-emp-tab ${activeModalEmpFilter === 'all' ? 'active' : ''}" id="modalEmp_all" onclick="filterModalByEmployee('all')">
                <i class="fas fa-users"></i> All Employees (${events.length})
            </button>
        `;
        employees.forEach(empName => {
            const count = events.filter(e => e.added_by === empName).length;
            const safeEmpId = empName.replace(/[^a-zA-Z0-9]/g, '_');
            const isActive = (activeModalEmpFilter === empName);
            empHtml += `
                <button type="button" class="cal-emp-tab ${isActive ? 'active' : ''}" id="modalEmp_${safeEmpId}" onclick="filterModalByEmployee('${escapeHtml(empName)}')">
                    <i class="fas fa-user-circle"></i> ${escapeHtml(empName)} (${count})
                </button>
            `;
        });
        empPills.innerHTML = empHtml;
        empRow.style.display = 'block';
    } else {
        empRow.style.display = 'none';
    }

    renderModalEvents();

    document.getElementById('dayDetailModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function updateModalSubtitle(events, employees) {
    let filtered = events;
    if (activeModalEmpFilter !== 'all') {
        filtered = filtered.filter(e => e.added_by === activeModalEmpFilter);
    }
    const count = filtered.length;
    const empLabel = (activeModalEmpFilter !== 'all') 
        ? ` &bull; Assigned to: <strong>${escapeHtml(activeModalEmpFilter)}</strong>`
        : (employees.length > 0 ? ` &bull; Assigned to: ${employees.map(e => escapeHtml(e)).join(', ')}` : '');

    document.getElementById('modalDateSubtitle').innerHTML = `<strong>${count} activities scheduled</strong>${empLabel}`;
}

function updateFilterCounts(events) {
    let filtered = events;
    if (activeModalEmpFilter !== 'all' && !activeDetailGroupKey) {
        filtered = filtered.filter(e => e.added_by === activeModalEmpFilter);
    }
    const remCount = filtered.filter(e => e.type === 'reminder').length;
    const waCount = filtered.filter(e => e.type === 'whatsapp').length;
    document.getElementById('modalCountAll').textContent = filtered.length;
    document.getElementById('modalCountReminder').textContent = remCount;
    document.getElementById('modalCountWhatsapp').textContent = waCount;
}

function closeDayModal() {
    activeDetailGroupKey = null;
    document.getElementById('dayDetailModal').style.display = 'none';
    document.body.style.overflow = '';
}

function openFullscreenDetailView(groupKey) {
    activeDetailGroupKey = groupKey;
    renderModalEvents();
    const modalEl = document.getElementById('dayDetailModal');
    if (modalEl) modalEl.scrollTo({ top: 0, behavior: 'smooth' });
}

function backToOverview() {
    activeDetailGroupKey = null;
    renderModalEvents();
    const modalEl = document.getElementById('dayDetailModal');
    if (modalEl) modalEl.scrollTo({ top: 0, behavior: 'smooth' });
}

function handleModalBackBtn() {
    if (activeDetailGroupKey !== null) {
        backToOverview();
    } else {
        closeDayModal();
    }
}

function filterModalEvents(type) {
    activeModalFilter = type;
    document.getElementById('modalFilterAll').classList.toggle('active', type === 'all');
    document.getElementById('modalFilterReminder').classList.toggle('active', type === 'reminder');
    document.getElementById('modalFilterWhatsapp').classList.toggle('active', type === 'whatsapp');
    renderModalEvents();
}

function filterModalByEmployee(empName) {
    activeModalEmpFilter = empName;
    document.querySelectorAll('.cal-emp-tab').forEach(el => el.classList.remove('active'));
    const safeEmpId = (empName === 'all') ? 'all' : empName.replace(/[^a-zA-Z0-9]/g, '_');
    const targetTab = document.getElementById(`modalEmp_${safeEmpId}`);
    if (targetTab) targetTab.classList.add('active');

    const events = calendarEventsData[activeModalDate] || [];
    const employees = [...new Set(events.map(e => e.added_by).filter(Boolean))];
    updateModalSubtitle(events, employees);
    updateFilterCounts(events);
    renderModalEvents();
}

function filterModalEventsBySearch() {
    renderModalEvents();
}

function renderModalEvents() {
    const container = document.getElementById('modalEventsBody');
    const all = calendarEventsData[activeModalDate] || [];

    const dateObj = new Date(activeModalDate + 'T00:00:00');
    const formattedDate = dateObj.toLocaleDateString('en-GB', {
        weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
    });

    const backBtnText = document.getElementById('modalBackBtnText');
    const backBtn = document.getElementById('modalBackButton');
    const empRow = document.getElementById('modalEmployeeFilterRow');

    // Group activities on this day by (Customer Name + Employee Name)
    const groups = {};
    all.forEach(e => {
        const key = `${e.added_by || 'Unknown'}___${e.customer_name || 'Customer'}`;
        if (!groups[key]) {
            groups[key] = {
                key: key,
                added_by: e.added_by,
                added_by_avatar: e.added_by_avatar,
                emp_id: e.emp_id,
                emp_mobile: e.emp_mobile,
                emp_email: e.emp_email,
                emp_application: e.emp_application,
                emp_total_bases: e.emp_total_bases,
                customer_name: e.customer_name,
                company_name: e.company_name,
                mobile: e.mobile,
                product_type: e.product_type,
                customer_status: e.customer_status,
                assign_base_id: e.assign_base_id,
                loan_details_url: e.loan_details_url,
                loan_application: e.loan_application,
                activities: []
            };
        }
        groups[key].activities.push(e);
        if (!groups[key].loan_application && e.loan_application) {
            groups[key].loan_application = e.loan_application;
        }
        if (!groups[key].product_type && e.product_type) {
            groups[key].product_type = e.product_type;
        }
    });

    // Sort activities inside each group chronologically
    Object.values(groups).forEach(g => {
        g.activities.sort((a, b) => timeToMinutes(a.time_24 || a.time) - timeToMinutes(b.time_24 || b.time));
    });

    const searchVal = (document.getElementById('modalSearchInput')?.value || '').toLowerCase().trim();

    // =========================================================================
    // MODE A: FULL SCREEN DETAIL VIEW (activeDetailGroupKey !== null)
    // =========================================================================
    if (activeDetailGroupKey && groups[activeDetailGroupKey]) {
        const grp = groups[activeDetailGroupKey];

        // Update Header Controls for Full Screen Detail Mode
        if (backBtnText) backBtnText.textContent = 'Back to Overview';
        if (backBtn) backBtn.title = 'Back to Overview';
        if (empRow) empRow.style.display = 'none';

        document.getElementById('modalDateTitle').innerHTML = `
            ${formattedDate} &nbsp;<span style="opacity:0.4;font-weight:400;">/</span>&nbsp; ${escapeHtml(grp.added_by)} &bull; ${escapeHtml(grp.customer_name)}
        `;

        let activitiesToRender = grp.activities;
        if (activeModalFilter !== 'all') {
            activitiesToRender = activitiesToRender.filter(e => e.type === activeModalFilter);
        }

        if (searchVal) {
            activitiesToRender = activitiesToRender.filter(e =>
                (e.customer_name && e.customer_name.toLowerCase().includes(searchVal)) ||
                (e.company_name && e.company_name.toLowerCase().includes(searchVal)) ||
                (e.product_type && e.product_type.toLowerCase().includes(searchVal)) ||
                (e.mobile && e.mobile.toLowerCase().includes(searchVal)) ||
                (e.added_by && e.added_by.toLowerCase().includes(searchVal)) ||
                (e.remarks && e.remarks.toLowerCase().includes(searchVal))
            );
        }

        document.getElementById('modalDateSubtitle').innerHTML = `
            <strong>${activitiesToRender.length} activities scheduled</strong> &bull; Assigned to: <strong>${escapeHtml(grp.added_by)}</strong> &bull; Customer: <strong>${escapeHtml(grp.customer_name)}</strong>
        `;
        updateFilterCounts(grp.activities);

        if (activitiesToRender.length === 0) {
            container.innerHTML = `
                <div class="empty-state" style="padding:60px 20px;background:#fff;border-radius:18px;">
                    <i class="fas fa-inbox" style="font-size:42px;color:#cbd5e1;margin-bottom:12px;"></i>
                    <h4 style="font-size:16px;font-weight:700;color:#475569;margin:0 0 6px 0;">No activities found</h4>
                    <p style="font-size:13px;color:#94a3b8;margin:0;">No activities match the selected filter for this customer.</p>
                    <div style="margin-top:16px;">
                        <button type="button" class="btn-action-lg" onclick="backToOverview()" style="background:#e0e7ff;color:#4338ca;">
                            <i class="fas fa-arrow-left"></i> Back to Overview
                        </button>
                    </div>
                </div>
            `;
            return;
        }

        // Render each activity as an individual full-width card matching media_1794054073094_e0cfbd52.jpg
        let detailHtml = '';
        activitiesToRender.forEach(act => {
            const isRem = (act.type === 'reminder');
            const typeBadge = isRem
                ? '<span class="badge-type-pill rem"><i class="fas fa-bell"></i> REMINDER</span>'
                : '<span class="badge-type-pill wa"><i class="fab fa-whatsapp"></i> WHATSAPP</span>';
            const productBadge = (act.product_type || grp.product_type)
                ? `<span class="badge-product-pill"><i class="fas fa-cubes"></i> Product: <strong>${escapeHtml(act.product_type || grp.product_type)}</strong></span>`
                : '';
            const statusBadge = `<span class="status-pill status-${act.status_class}">${escapeHtml(act.status)}</span>`;

            detailHtml += `
                <div class="fullpage-event-card ${isRem ? 'card-rem' : 'card-wa'}">
                    <div class="fullpage-card-header">
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                            ${typeBadge}
                            ${statusBadge}
                            ${productBadge}
                        </div>
                        <div class="modal-who-badge">
                            <i class="fas fa-user-circle"></i> Added by: <strong>${escapeHtml(act.added_by || grp.added_by)}</strong>
                        </div>
                    </div>

                    <div class="fullpage-customer-title">${escapeHtml(act.customer_name || grp.customer_name)}</div>

                    <div class="fullpage-meta-grid">
                        <div><i class="fas fa-clock"></i> <span>Time: <strong>${formatTime12(act.time)}</strong></span></div>
                        <div><i class="fas fa-phone"></i> <span>Mobile: <strong><a href="tel:${act.mobile || grp.mobile}" style="color:var(--primary);text-decoration:none;">${escapeHtml(act.mobile || grp.mobile || '—')}</a></strong></span></div>
                        <div><i class="fas fa-building"></i> <span>Company: <strong>${escapeHtml(act.company_name || grp.company_name || '—')}</strong></span></div>
                        ${act.amount ? `<div><i class="fas fa-money-bill-wave" style="color:#059669;"></i> <span>Amount: <strong>₹${act.amount}</strong></span></div>` : ''}
                        ${act.due_date ? `<div><i class="fas fa-calendar-day" style="color:#f59e0b;"></i> <span>Due Date: <strong>${act.due_date}</strong></span></div>` : ''}
                        ${act.template_name ? `<div><i class="fas fa-file-alt"></i> <span>Template: <strong>${escapeHtml(act.template_name)}</strong></span></div>` : ''}
                    </div>

                    ${act.remarks ? `
                        <div class="fullpage-remarks-box">
                            <i class="fas fa-comment-dots" style="color:var(--primary);margin-right:6px;"></i>
                            <strong>Remarks:</strong> ${escapeHtml(act.remarks)}
                        </div>
                    ` : ''}

                    ${act.header_image ? `
                        <div style="margin-bottom:14px;">
                            <img src="${act.header_image}" style="max-height:80px;border-radius:8px;border:1px solid #e2e8f0;">
                        </div>
                    ` : ''}

                    <div class="fullpage-actions">
                        ${(act.mobile || grp.mobile) && (act.mobile || grp.mobile) !== '—' ? `
                            <a href="tel:${act.mobile || grp.mobile}" class="btn-action-lg btn-call">
                                <i class="fas fa-phone"></i> Call Customer
                            </a>
                            <a href="https://wa.me/91${(act.mobile || grp.mobile).replace(/[^0-9]/g, '')}" target="_blank" class="btn-action-lg btn-wa-action">
                                <i class="fab fa-whatsapp"></i> Chat WhatsApp
                            </a>
                        ` : ''}
                        ${isRem ? `
                            ${!act.is_sent ? `
                                <button type="button" class="btn-action-lg btn-seen-action" onclick="markReminderSeenModal(${act.raw_id})" title="Mark complete">
                                    <i class="fas fa-check"></i> Mark Completed
                                </button>
                            ` : `
                                <span class="status-pill status-completed" style="padding:6px 14px;font-size:12px;">
                                    <i class="fas fa-check-circle"></i> Completed
                                </span>
                            `}
                        ` : ''}
                    </div>
                </div>
            `;
        });

        container.innerHTML = detailHtml;
        return;
    }

    // =========================================================================
    // MODE B: OVERVIEW VIEW (activeDetailGroupKey === null)
    // =========================================================================
    // Reset Header Controls for Overview Mode
    if (backBtnText) backBtnText.textContent = 'Back to Calendar';
    if (backBtn) backBtn.title = 'Back to Month Calendar';

    document.getElementById('modalDateTitle').textContent = formattedDate;

    let events = all;
    if (activeModalFilter !== 'all') {
        events = events.filter(e => e.type === activeModalFilter);
    }
    if (activeModalEmpFilter !== 'all') {
        events = events.filter(e => e.added_by === activeModalEmpFilter);
    }

    if (searchVal) {
        events = events.filter(e =>
            (e.customer_name && e.customer_name.toLowerCase().includes(searchVal)) ||
            (e.company_name && e.company_name.toLowerCase().includes(searchVal)) ||
            (e.product_type && e.product_type.toLowerCase().includes(searchVal)) ||
            (e.mobile && e.mobile.toLowerCase().includes(searchVal)) ||
            (e.added_by && e.added_by.toLowerCase().includes(searchVal)) ||
            (e.remarks && e.remarks.toLowerCase().includes(searchVal))
        );
    }

    const employees = [...new Set(all.map(e => e.added_by).filter(Boolean))];
    updateModalSubtitle(events, employees);
    updateFilterCounts(events);

    if (empRow) {
        empRow.style.display = (employees.length > 1) ? 'block' : 'none';
    }

    // Group filtered events
    const filteredGroups = {};
    events.forEach(e => {
        const key = `${e.added_by || 'Unknown'}___${e.customer_name || 'Customer'}`;
        if (!filteredGroups[key]) {
            filteredGroups[key] = {
                key: key,
                added_by: e.added_by,
                added_by_avatar: e.added_by_avatar,
                emp_id: e.emp_id,
                emp_mobile: e.emp_mobile,
                emp_email: e.emp_email,
                emp_application: e.emp_application,
                emp_total_bases: e.emp_total_bases,
                customer_name: e.customer_name,
                company_name: e.company_name,
                mobile: e.mobile,
                product_type: e.product_type,
                customer_status: e.customer_status,
                assign_base_id: e.assign_base_id,
                loan_details_url: e.loan_details_url,
                loan_application: e.loan_application,
                activities: []
            };
        }
        filteredGroups[key].activities.push(e);
        if (!filteredGroups[key].loan_application && e.loan_application) {
            filteredGroups[key].loan_application = e.loan_application;
        }
        if (!filteredGroups[key].product_type && e.product_type) {
            filteredGroups[key].product_type = e.product_type;
        }
    });

    const groupList = Object.values(filteredGroups);

    if (groupList.length === 0) {
        container.innerHTML = `
            <div class="empty-state" style="padding:60px 20px;background:#fff;border-radius:18px;">
                <i class="fas fa-inbox" style="font-size:42px;color:#cbd5e1;margin-bottom:12px;"></i>
                <h4 style="font-size:16px;font-weight:700;color:#475569;margin:0 0 6px 0;">No activities found</h4>
                <p style="font-size:13px;color:#94a3b8;margin:0;">No activities matched for this filter on this date.</p>
            </div>
        `;
        return;
    }

    let html = '';
    groupList.forEach(grp => {
        const productBadge = grp.product_type
            ? `<span class="badge-product-pill"><i class="fas fa-cubes"></i> Product: <strong>${escapeHtml(grp.product_type)}</strong></span>`
            : '';

        // Sort schedules chronologically by time
        grp.activities.sort((a, b) => timeToMinutes(a.time_24 || a.time) - timeToMinutes(b.time_24 || b.time));

        // Pick RECENT / first reminder or activity
        const recentAct = grp.activities.find(a => a.type === 'reminder' && !a.is_sent) || grp.activities[0];
        const isRem = (recentAct.type === 'reminder');
        const typeBadge = isRem 
            ? '<span class="badge-type-mini rem"><i class="fas fa-bell"></i> REMINDER</span>'
            : '<span class="badge-type-mini wa"><i class="fab fa-whatsapp"></i> WHATSAPP</span>';

        const remainingCount = grp.activities.length - 1;

        html += `
            <div class="fullpage-event-card">
                <div class="fullpage-card-header">
                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                        <div class="modal-who-badge-lg">
                            <i class="fas fa-user-circle"></i> Added by: <strong>${escapeHtml(grp.added_by)}</strong>
                            <span class="badge-role-pill" style="font-size:11px;padding:2px 8px;margin-left:6px;">${escapeHtml(grp.emp_application || 'Staff')}</span>
                        </div>
                        ${productBadge}
                    </div>
                    <div class="schedule-count-pill">
                        <i class="fas fa-clock"></i> <strong>${grp.activities.length}</strong> ${grp.activities.length === 1 ? 'Activity' : 'Activities'} Scheduled
                    </div>
                </div>

                <div style="margin-top:14px;margin-bottom:6px;">
                    <div class="fullpage-customer-title" style="margin:0;">${escapeHtml(grp.customer_name)}</div>
                    <div class="fullpage-customer-sub">
                        ${grp.company_name && grp.company_name !== '—' ? `<span><i class="fas fa-building"></i> ${escapeHtml(grp.company_name)}</span>` : ''}
                        ${grp.mobile && grp.mobile !== '—' ? `<span><i class="fas fa-phone"></i> <a href="tel:${grp.mobile}" style="color:var(--primary);font-weight:700;text-decoration:none;">${escapeHtml(grp.mobile)}</a></span>` : ''}
                        ${grp.customer_status && grp.customer_status !== '—' ? `<span class="status-pill status-${grp.customer_status.toLowerCase()}">${escapeHtml(grp.customer_status)}</span>` : ''}
                        ${grp.assign_base_id ? `<span style="font-size:11.5px;color:#94a3b8;">Base Record: #${grp.assign_base_id}</span>` : ''}
                    </div>
                </div>

                <!-- Recent Reminder Section (Only 1 item displayed first, others behind View Details) -->
                <div class="card-activities-list">
                    <div class="card-activity-item ${isRem ? 'item-rem' : 'item-wa'}">
                        <div class="act-time-col">
                            <span class="act-time"><i class="fas fa-clock"></i> ${formatTime12(recentAct.time)}</span>
                            ${typeBadge}
                        </div>
                        <div class="act-desc-col">
                            ${recentAct.remarks ? `<div class="act-remarks"><i class="fas fa-comment-dots" style="color:var(--primary);margin-right:5px;"></i>${escapeHtml(recentAct.remarks)}</div>` : ''}
                            ${recentAct.amount ? `<div class="act-meta-val"><i class="fas fa-money-bill-wave" style="color:#059669;"></i> Amount: ₹${recentAct.amount} &bull; Due: ${recentAct.due_date || '—'}</div>` : ''}
                            ${recentAct.template_name ? `<div class="act-meta-val"><i class="fas fa-file-alt"></i> Template: ${escapeHtml(recentAct.template_name)}</div>` : ''}
                            ${recentAct.header_image ? `<div style="margin-top:6px;"><img src="${recentAct.header_image}" style="max-height:60px;border-radius:6px;border:1px solid #e2e8f0;"></div>` : ''}
                        </div>
                        <div class="act-status-col">
                            <span class="status-pill status-${recentAct.status_class}">${recentAct.status}</span>
                            ${isRem && !recentAct.is_sent ? `
                                <button type="button" class="btn-action-small btn-seen-action" onclick="markReminderSeenModal(${recentAct.raw_id})" title="Mark complete">
                                    <i class="fas fa-check"></i> Complete
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>

                <!-- Action Bar for this customer -->
                <div class="fullpage-actions">
                    <button type="button" class="btn-action-lg btn-view-emp-detail" onclick="openFullscreenDetailView('${grp.key}')">
                        <i class="fas fa-eye"></i> View Details (${escapeHtml(grp.added_by)})
                    </button>
                    ${grp.mobile && grp.mobile !== '—' ? `
                        <a href="tel:${grp.mobile}" class="btn-action-lg btn-call">
                            <i class="fas fa-phone"></i> Call Customer
                        </a>
                        <a href="https://wa.me/91${grp.mobile.replace(/[^0-9]/g, '')}" target="_blank" class="btn-action-lg btn-wa-action">
                            <i class="fab fa-whatsapp"></i> Chat WhatsApp
                        </a>
                    ` : ''}
                    ${grp.loan_details_url ? `
                        <a href="${grp.loan_details_url}" target="_blank" class="btn-action-lg" style="background:#eef2ff;color:#4f46e5;border:1px solid #c7d2fe;">
                            <i class="fas fa-file-invoice-dollar"></i> Loan Application
                        </a>
                    ` : ''}
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}

// Mark reminder as seen from modal
function markReminderSeenModal(notifId) {
    fetch(CAL_ROUTES.markSeen, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CAL_ROUTES.csrf
        },
        body: JSON.stringify({ ids: [notifId] })
    })
    .then(r => r.json())
    .then(data => {
        if (data.status) {
            // Refresh
            fetchCalendarEvents();
            // Update local state if modal is open
            if (activeModalDate && calendarEventsData[activeModalDate]) {
                const item = calendarEventsData[activeModalDate].find(e => e.raw_id === notifId && e.type === 'reminder');
                if (item) {
                    item.is_sent = true;
                    item.status = 'Completed';
                    item.status_class = 'completed';
                    renderModalEvents();
                }
            }
        }
    });
}

// =========================================================
// EMPLOYEE & CUSTOMER FULL DETAILS MODAL
// =========================================================
function openEmployeeCustomerDetailModal(eventId) {
    const e = calendarAllEvents.find(x => String(x.id) === String(eventId));
    if (!e) {
        console.error('Event not found:', eventId);
        return;
    }

    const titleEl = document.getElementById('empModalTitle');
    const subTitleEl = document.getElementById('empModalSubtitle');
    const bodyEl = document.getElementById('empModalBody');
    const footerEl = document.getElementById('empModalFooter');

    titleEl.textContent = `Schedule Details: ${e.customer_name}`;
    subTitleEl.innerHTML = `Customer: <strong>${escapeHtml(e.customer_name)}</strong> &bull; Scheduled by: <strong>${escapeHtml(e.added_by)}</strong> on ${e.display_date}`;

    // Filter all activities created by this employee for this customer in the current loaded calendar view
    const relatedActivities = calendarAllEvents.filter(x => 
        (x.emp_id && e.emp_id && String(x.emp_id) === String(e.emp_id) && x.customer_name === e.customer_name) ||
        (x.added_by === e.added_by && x.customer_name === e.customer_name)
    );

    // Sort related activities chronologically by time
    relatedActivities.sort((a, b) => timeToMinutes(a.time_24 || a.time) - timeToMinutes(b.time_24 || b.time));

    const cleanCustPhone = (e.mobile && e.mobile !== '—') ? e.mobile.replace(/[^0-9]/g, '') : '';

    // Loan Application details block
    let loanHtml = '';
    if (e.loan_application) {
        const la = e.loan_application;
        loanHtml = `
            <div class="submodal-section-card" style="border-left: 4px solid #10b981; background: #f0fdf4;">
                <div class="submodal-card-header" style="border-bottom-color:#bbf7d0;">
                    <span class="submodal-card-title" style="color:#065f46;">
                        <i class="fas fa-file-invoice-dollar" style="color:#10b981;"></i> Customer Loan Application Details
                    </span>
                    <span class="status-pill status-${(la.status || 'pending').toLowerCase()}">${escapeHtml(la.status)}</span>
                </div>
                <div class="submodal-grid-2">
                    <div class="submodal-field-item">
                        <span class="submodal-label">App Number</span>
                        <span class="submodal-val">${escapeHtml(la.app_number || '—')}</span>
                    </div>
                    <div class="submodal-field-item">
                        <span class="submodal-label">Logged Bank</span>
                        <span class="submodal-val">${escapeHtml(la.logged_bank || '—')}</span>
                    </div>
                    <div class="submodal-field-item">
                        <span class="submodal-label">Category</span>
                        <span class="submodal-val">${escapeHtml(la.category || '—')}</span>
                    </div>
                    <div class="submodal-field-item">
                        <span class="submodal-label">Promo Code</span>
                        <span class="submodal-val">${escapeHtml(la.promo_code || '—')}</span>
                    </div>
                    <div class="submodal-field-item">
                        <span class="submodal-label">Requested Amount</span>
                        <span class="submodal-val" style="color:#dc2626;">${la.req_amount !== '—' ? '₹' + la.req_amount : '—'}</span>
                    </div>
                    <div class="submodal-field-item">
                        <span class="submodal-label">Approved Amount</span>
                        <span class="submodal-val" style="color:#059669;">${la.approved_amount !== '—' ? '₹' + la.approved_amount : '—'}</span>
                    </div>
                    <div class="submodal-field-item">
                        <span class="submodal-label">Login Date</span>
                        <span class="submodal-val">${la.login_date || '—'}</span>
                    </div>
                    <div class="submodal-field-item">
                        <span class="submodal-label">Bank Manager / Location</span>
                        <span class="submodal-val">${escapeHtml(la.bank_manager || '—')} &bull; ${escapeHtml(la.location || '—')}</span>
                    </div>
                </div>
                ${e.loan_details_url ? `
                    <div style="margin-top:14px;padding-top:12px;border-top:1px dashed #bbf7d0;display:flex;justify-content:flex-end;">
                        <a href="${e.loan_details_url}" target="_blank" class="btn-action-lg" style="background:#059669;color:#fff;text-decoration:none;">
                            <i class="fas fa-external-link-alt"></i> Open Full Loan Application Details (${escapeHtml(e.customer_name)})
                        </a>
                    </div>
                ` : ''}
            </div>
        `;
    } else if (e.assign_base_id) {
        loanHtml = `
            <div class="submodal-section-card">
                <div class="submodal-card-header">
                    <span class="submodal-card-title">
                        <i class="fas fa-file-invoice"></i> Customer Base Record (#${e.assign_base_id})
                    </span>
                    <span style="font-size:12px;color:#94a3b8;">No loan application filed</span>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:gap:10px;">
                    <span style="font-size:13px;color:#64748b;">Customer file is assigned to <strong>${escapeHtml(e.added_by)}</strong>.</span>
                    <a href="${e.loan_details_url}" target="_blank" class="btn-action-lg" style="background:#475569;color:#fff;text-decoration:none;font-size:12px;">
                        <i class="fas fa-folder-open"></i> View Customer File
                    </a>
                </div>
            </div>
        `;
    }

    // Complete Schedule Details (Timeline of all activities)
    let relatedActivitiesHtml = '';
    if (relatedActivities.length > 0) {
        relatedActivitiesHtml = `
            <div class="submodal-section-card">
                <div class="submodal-card-header">
                    <span class="submodal-card-title">
                        <i class="fas fa-calendar-check" style="color:var(--primary);"></i> Scheduled Activities for ${escapeHtml(e.customer_name)} (${relatedActivities.length} Total)
                    </span>
                    <span style="font-size:11.5px;font-weight:700;color:var(--primary);background:#eef2ff;padding:2px 8px;border-radius:6px;">
                        Scheduled by ${escapeHtml(e.added_by)}
                    </span>
                </div>
                <div style="display:flex;flex-direction:column;gap:10px;">
                    ${relatedActivities.map(act => {
                        const isRem = act.type === 'reminder';
                        return `
                            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-radius:12px;background:#ffffff;border:1px solid #e2e8f0;border-left:4px solid ${isRem ? '#f59e0b' : '#10b981'};gap:14px;flex-wrap:wrap;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
                                <div style="display:flex;align-items:center;gap:12px;min-width:180px;">
                                    <span style="width:36px;height:36px;border-radius:10px;background:${isRem ? '#fef3c7' : '#d1fae5'};color:${isRem ? '#d97706' : '#059669'};display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0;">
                                        <i class="${act.icon}"></i>
                                    </span>
                                    <div>
                                        <div style="font-size:14px;font-weight:800;color:#0f172a;">
                                            <i class="fas fa-clock" style="color:#64748b;font-size:12px;"></i> ${formatTime12(act.time)}
                                        </div>
                                        <div style="font-size:11px;font-weight:700;color:${isRem ? '#d97706' : '#059669'};text-transform:uppercase;">
                                            ${act.type_label} &bull; ${act.display_date}
                                        </div>
                                    </div>
                                </div>

                                <div style="flex:1;min-width:200px;">
                                    ${act.remarks ? `<div style="font-size:13px;color:#1e293b;font-weight:600;"><i class="fas fa-comment-dots" style="color:var(--primary);margin-right:5px;"></i>${escapeHtml(act.remarks)}</div>` : ''}
                                    ${act.amount ? `<div style="font-size:12px;color:#059669;font-weight:600;margin-top:2px;"><i class="fas fa-money-bill-wave"></i> Amount: ₹${act.amount} &bull; Due: ${act.due_date || '—'}</div>` : ''}
                                    ${act.template_name ? `<div style="font-size:12px;color:#64748b;margin-top:2px;"><i class="fas fa-file-alt"></i> Template: ${escapeHtml(act.template_name)}</div>` : ''}
                                    ${act.header_image ? `<div style="margin-top:6px;"><img src="${act.header_image}" style="max-height:60px;border-radius:6px;border:1px solid #e2e8f0;"></div>` : ''}
                                </div>

                                <div style="display:flex;align-items:center;gap:10px;">
                                    <span class="status-pill status-${act.status_class}">${act.status}</span>
                                    ${isRem && !act.is_sent ? `
                                        <button type="button" class="btn-action-small btn-seen-action" onclick="markReminderSeenModal(${act.raw_id})" title="Mark complete">
                                            <i class="fas fa-check"></i> Complete
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }

    bodyEl.innerHTML = `
        <!-- Customer & Assignment Info -->
        <div class="submodal-section-card">
            <div class="submodal-card-header">
                <span class="submodal-card-title">
                    <i class="fas fa-user-tag" style="color:var(--primary);"></i> Customer Information
                </span>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="modal-who-badge-lg" style="font-size:12px;padding:3px 10px;">
                        <i class="fas fa-user-circle"></i> Added by: <strong>${escapeHtml(e.added_by)}</strong>
                    </span>
                    <span class="status-pill status-${e.status_class}">${e.customer_status || e.status}</span>
                </div>
            </div>

            <div class="submodal-grid-2">
                <div class="submodal-field-item">
                    <span class="submodal-label">Customer Name</span>
                    <span class="submodal-val" style="font-size:15px;color:var(--primary);">${escapeHtml(e.customer_name)}</span>
                </div>
                <div class="submodal-field-item">
                    <span class="submodal-label">Company Name</span>
                    <span class="submodal-val">${escapeHtml(e.company_name)}</span>
                </div>
                <div class="submodal-field-item">
                    <span class="submodal-label">Customer Mobile</span>
                    <div style="display:flex;align-items:center;gap:8px;margin-top:3px;">
                        <span class="submodal-val">${escapeHtml(e.mobile)}</span>
                        ${cleanCustPhone ? `
                            <a href="tel:${cleanCustPhone}" class="btn-mini-action btn-call" title="Call Customer">
                                <i class="fas fa-phone"></i> Call
                            </a>
                            <a href="https://wa.me/91${cleanCustPhone}" target="_blank" class="btn-mini-action btn-wa-action" title="WhatsApp Customer">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        ` : ''}
                    </div>
                </div>
                <div class="submodal-field-item">
                    <span class="submodal-label">Product Type</span>
                    <div style="margin-top:3px;">
                        ${e.product_type ? `
                            <span class="badge-product-pill" style="font-size:12px;padding:3px 10px;">
                                <i class="fas fa-cubes"></i> ${escapeHtml(e.product_type)}
                            </span>
                        ` : '<span class="submodal-val">—</span>'}
                    </div>
                </div>
                <div class="submodal-field-item">
                    <span class="submodal-label">Assignment Base ID</span>
                    <span class="submodal-val">#${e.assign_base_id || '—'}</span>
                </div>
                <div class="submodal-field-item">
                    <span class="submodal-label">Total Schedules on This Day</span>
                    <span class="submodal-val">${relatedActivities.length} Activities</span>
                </div>
            </div>
        </div>

        <!-- Loan Application Summary -->
        ${loanHtml}

        <!-- Complete Schedule Details -->
        ${relatedActivitiesHtml}
    `;

    footerEl.innerHTML = `
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
            ${cleanCustPhone ? `
                <a href="tel:${cleanCustPhone}" class="btn-action-lg btn-call">
                    <i class="fas fa-phone"></i> Call Customer
                </a>
                <a href="https://wa.me/91${cleanCustPhone}" target="_blank" class="btn-action-lg btn-wa-action">
                    <i class="fab fa-whatsapp"></i> Chat WhatsApp
                </a>
            ` : ''}
            ${e.loan_details_url ? `
                <a href="${e.loan_details_url}" target="_blank" class="btn-action-lg" style="background:#059669;color:#fff;text-decoration:none;">
                    <i class="fas fa-file-invoice-dollar"></i> Loan Details Page
                </a>
            ` : ''}
        </div>
        <button type="button" class="btn-action-lg" style="background:#e2e8f0;color:#334155;" onclick="closeEmployeeCustomerDetailModal()">
            <i class="fas fa-times"></i> Close
        </button>
    `;

    document.getElementById('employeeCustomerDetailModal').style.display = 'flex';
}

function closeEmployeeCustomerDetailModal() {
    document.getElementById('employeeCustomerDetailModal').style.display = 'none';
}

// Utility: Escape HTML
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const subModal = document.getElementById('employeeCustomerDetailModal');
        if (subModal && subModal.style.display !== 'none') {
            closeEmployeeCustomerDetailModal();
        } else {
            closeDayModal();
        }
    }
});
</script>
@endsection
