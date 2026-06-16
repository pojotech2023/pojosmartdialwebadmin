@extends('layouts.app')

@section('content')
<h2>Dashboard</h2>
<p>Welcome, {{ auth()->user()->name }}</p>

{{-- ============ FULL-SCREEN NOTIFICATION PANEL ============ --}}
<div id="notifPanel" style="display:none;position:fixed;inset:0;z-index:9999;background:#f5f7fb;overflow-y:auto">

    {{-- Header --}}
    <div style="background:#0f172a;color:#fff;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:10">
        <div style="display:flex;align-items:center;gap:12px">
            <i class="fas fa-bell" style="font-size:20px"></i>
            <span style="font-size:18px;font-weight:700">Notification List</span>
            <span id="notifPanelSubtitle" style="font-size:13px;color:#94a3b8;margin-left:8px"></span>
        </div>
        <button onclick="closeNotifPanel()" style="background:#334155;color:#fff;border:none;border-radius:8px;padding:8px 16px;cursor:pointer;font-size:14px;font-weight:600">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    {{-- Content --}}
    <div id="notif-content" style="padding:28px;max-width:860px;margin:0 auto">
        <div style="text-align:center;color:#9ca3af;padding:60px">
            <i class="fas fa-spinner fa-spin" style="font-size:28px"></i>
            <p>Loading...</p>
        </div>
    </div>

</div>

{{-- Toast container --}}
<div id="toastContainer" style="position:fixed;bottom:24px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:10px"></div>

<style>
    .nl-card {
        background: #fff;
        border-radius: 14px;
        padding: 18px 20px;
        margin-bottom: 12px;
        box-shadow: 0 1px 6px rgba(0,0,0,0.07);
        display: flex;
        align-items: flex-start;
        gap: 16px;
        transition: box-shadow 0.2s;
        border-left: 4px solid #3b82f6;
    }
    .nl-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.10); }

    .nl-icon {
        width: 42px; height: 42px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
        background: #eff6ff; color: #3b82f6;
    }

    .nl-customer   { font-size: 15px; font-weight: 700; color: #0f172a; }
    .nl-employee   { font-size: 12px; color: #6b7280; margin-top: 3px; }
    .nl-datetime   { font-size: 12px; color: #6b7280; margin-top: 3px; }
    .nl-remarks    { font-size: 13px; color: #374151; margin-top: 6px; background:#f8fafc; border-radius:8px; padding:7px 10px; }
    .nl-mobile     { font-size: 12px; color:#6b7280; margin-top:3px; }

    .nl-empty {
        text-align: center; padding: 60px 0; color: #94a3b8;
    }

    .toast-notif {
        background: #1e293b; color: #fff; border-radius: 14px;
        padding: 14px 18px; min-width: 280px; max-width: 360px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.25);
        border-left: 4px solid #ef4444;
        animation: slideInRight 0.3s ease;
    }
    @keyframes slideInRight {
        from { transform: translateX(100px); opacity: 0; }
        to   { transform: translateX(0);     opacity: 1; }
    }
</style>

<script>
const NOTIF_ROUTES = {
    data:     '{{ route("dashboard.notifications.data") }}',
    markSeen: '{{ route("dashboard.notifications.mark-seen") }}',
    csrf:     '{{ csrf_token() }}'
};

let notifData  = { due: [], upcoming: [], history: [] };
let currentAssignBaseId = null;
let shownToastIds = JSON.parse(sessionStorage.getItem('shownToastIds') || '[]');

// ---- Open / Close panel ----
function openNotifPanel(assignBaseId) {
    currentAssignBaseId = assignBaseId || null;
    document.getElementById('notifPanel').style.display = 'block';
    document.body.style.overflow = 'hidden';
    loadNotifications();
}
function closeNotifPanel() {
    document.getElementById('notifPanel').style.display = 'none';
    document.body.style.overflow = '';
}

// ---- Load data from server ----
function loadNotifications(silent) {
    if (!silent) {
        document.getElementById('notif-content').innerHTML =
            '<div style="text-align:center;color:#9ca3af;padding:40px"><i class="fas fa-spinner fa-spin" style="font-size:28px"></i><p>Loading...</p></div>';
    }
    const url = currentAssignBaseId
        ? NOTIF_ROUTES.data + '?assign_base_id=' + currentAssignBaseId
        : NOTIF_ROUTES.data;

    fetch(url)
        .then(r => r.ok ? r.json() : r.json().then(e => Promise.reject(e.error || 'Server error ' + r.status)))
        .then(data => {
            notifData = data;
            updateBadge(data.due_count);
            renderAll();
            updateSubtitle(data);
            checkAndShowToasts(data.due);
        })
        .catch(err => {
            document.getElementById('notif-content').innerHTML =
                `<p style="color:#ef4444;text-align:center;padding:40px"><b>Error:</b> ${err}</p>`;
        });
}

function updateSubtitle(data) {
    const sub = document.getElementById('notifPanelSubtitle');
    if (!sub) return;
    const all = [...(data.due || []), ...(data.upcoming || []), ...(data.history || [])];
    if (currentAssignBaseId && all.length > 0) {
        const first = all[0];
        sub.textContent = '— ' + first.customer_name + (first.company_name !== '—' ? ' (' + first.company_name + ')' : '');
    } else if (currentAssignBaseId) {
        sub.textContent = '';
    } else {
        sub.textContent = '— All Employees';
    }
}

// ---- Badge ----
function updateBadge(count) {
    const badge = document.getElementById('notifBadge');
    if (!badge) return;
    if (count > 0) {
        badge.style.display = 'block';
        badge.textContent = count > 99 ? '99+' : count;
    } else {
        badge.style.display = 'none';
    }
}

// ---- Render all notifications (upcoming + sent) ----
function renderAll() {
    const container = document.getElementById('notif-content');
    const all = [...(notifData.due || []), ...(notifData.upcoming || []), ...(notifData.history || [])];

    if (all.length === 0) {
        container.innerHTML = `<div class="nl-empty">
            <i class="fas fa-bell-slash" style="font-size:48px;color:#cbd5e1"></i>
            <p style="margin-top:14px;font-size:15px;font-weight:600">No notifications found</p>
            <p style="font-size:13px">No reminders have been set for this customer yet.</p>
        </div>`;
        return;
    }

    let html = '';
    all.forEach(n => {
        const sent = n.is_sent;
        const cardStyle = sent
            ? 'border-left:4px solid #10b981;opacity:0.8'
            : 'border-left:4px solid #3b82f6';
        const iconStyle = sent
            ? 'background:#f0fdf4;color:#10b981'
            : 'background:#eff6ff;color:#3b82f6';
        const icon = sent ? 'fa-check-circle' : 'fa-bell';

        html += `<div class="nl-card" style="${cardStyle}">
            <div class="nl-icon" style="${iconStyle}"><i class="fas ${icon}"></i></div>
            <div style="flex:1;min-width:0">
                <div class="nl-customer">${escHtml(n.customer_name)}</div>
                <div class="nl-employee"><i class="fas fa-user" style="width:13px"></i> ${escHtml(n.employee_name)} &nbsp;|&nbsp; <i class="fas fa-building" style="width:13px"></i> ${escHtml(n.company_name)}</div>
                <div class="nl-mobile"><i class="fas fa-phone" style="width:13px"></i> ${escHtml(n.mobile)}</div>
                <div class="nl-datetime"><i class="fas fa-calendar" style="width:13px"></i> ${n.notification_date} &nbsp;&nbsp; <i class="fas fa-clock" style="width:13px"></i> ${n.notification_time}</div>
                <div class="nl-remarks"><i class="fas fa-comment-dots" style="color:#94a3b8;margin-right:6px"></i>${escHtml(n.remarks)}</div>
            </div>
            <div style="flex-shrink:0;text-align:center">
                ${sent
                    ? `<span style="background:#f0fdf4;color:#16a34a;border-radius:50px;font-size:11px;padding:4px 10px;font-weight:700;display:block">Sent</span>`
                    : `<span style="background:#eff6ff;color:#2563eb;border-radius:50px;font-size:11px;padding:4px 10px;font-weight:700;display:block">Pending</span>`
                }
            </div>
        </div>`;
    });

    container.innerHTML = html;
}

// ---- Mark seen ----
function markSeen(ids) {
    fetch(NOTIF_ROUTES.markSeen, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': NOTIF_ROUTES.csrf },
        body: JSON.stringify({ ids })
    }).then(() => loadNotifications(true));
}
function markAllSeen() {
    const ids = notifData.due.map(n => n.id);
    markSeen(ids);
}

// ---- Toast alerts ----
function checkAndShowToasts(dueItems) {
    dueItems.forEach(n => {
        if (!shownToastIds.includes(n.id)) {
            showToast(n);
            shownToastIds.push(n.id);
        }
    });
    sessionStorage.setItem('shownToastIds', JSON.stringify(shownToastIds));
}

function showToast(n) {
    const id = 'toast_' + n.id;
    const div = document.createElement('div');
    div.className = 'toast-notif';
    div.id = id;
    div.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:flex-start">
            <div>
                <div style="font-weight:700;font-size:14px"><i class="fas fa-bell" style="color:#ef4444"></i> Reminder Due</div>
                <div style="margin-top:4px;font-size:13px">${escHtml(n.customer_name)} &mdash; ${escHtml(n.employee_name)}</div>
                <div style="font-size:12px;color:#94a3b8;margin-top:2px">${n.notification_date} ${n.notification_time}</div>
                <div style="font-size:12px;color:#cbd5e1;margin-top:3px;font-style:italic">${escHtml(n.remarks)}</div>
            </div>
            <button onclick="dismissToast('${id}', ${n.id})" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:16px;padding:0 0 0 10px">&times;</button>
        </div>
        <div style="margin-top:10px;display:flex;gap:8px">
            <button class="btn-mark-seen" onclick="markSeen([${n.id}]);dismissToast('${id}', ${n.id})">
                <i class="fas fa-check"></i> Mark Seen
            </button>
            <button onclick="openNotifPanel();dismissToast('${id}', ${n.id})" style="background:#334155;color:#fff;border:none;border-radius:6px;padding:6px 12px;font-size:12px;font-weight:600;cursor:pointer">
                View All
            </button>
        </div>`;
    document.getElementById('toastContainer').appendChild(div);

    // Auto-dismiss after 12 seconds
    setTimeout(() => { if (document.getElementById(id)) div.remove(); }, 12000);
}

function dismissToast(toastId, notifId) {
    const el = document.getElementById(toastId);
    if (el) el.remove();
}

function escHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ---- Auto-poll every 60 seconds ----
document.addEventListener('DOMContentLoaded', function () {
    // Initial load for badge + toasts (silent, panel may be closed)
    fetch(NOTIF_ROUTES.data)
        .then(r => r.json())
        .then(data => {
            notifData = data;
            updateBadge(data.due_count);
            updateTabCounts(data);
            checkAndShowToasts(data.due);
        });

    setInterval(function () {
        fetch(NOTIF_ROUTES.data)
            .then(r => r.json())
            .then(data => {
                notifData = data;
                updateBadge(data.due_count);
                updateTabCounts(data);
                checkAndShowToasts(data.due);
                if (document.getElementById('notifPanel').style.display !== 'none') {
                    renderAll();
                }
            });
    }, 60000);
});
</script>

<style>
    body { background-color: #f5f7fb; }
    .card-box {
        background: #ffffff;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.06);
        width: 98%;
    }
    .form-label {
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 6px;
        display: block;
        color: #374151;
    }
    input[type="date"], select {
        padding: 8px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        font-size: 13px;
        background: #f9fafb;
    }
    .btn-upload {
        background: #2563eb;
        color: #ffffff;
        border: none;
        padding: 9px 18px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }
    .btn-cancel-small {
        background: #ef4444;
        color: #ffffff;
        border: none;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    th, td {
        padding: 8px;
        text-align: left;
    }
    thead { border-bottom: 1px solid #e5e7eb; }
    tbody tr { border-bottom: 1px solid #f1f5f9; }
    
    .action-btn {
        padding: 8px;
        margin: 0 2px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        width: 32px;
        height: 32px;
        text-align: center;
    }
    .btn-reminder {
        background: #10b981;
        color: white;
    }
    .btn-eye {
        background: #6366f1;
        color: white;
    }
    
</style>

{{-- EMPLOYEE SUMMARY CARDS --}}
@if(isset($cardData) && $cardData->count() > 0)

<div style="display:flex;gap:15px;flex-wrap:wrap;margin-bottom:20px">

    @foreach($cardData as $card)
        <div class="card-box" style="width:220px;text-align:center">
            <div style="font-size:15px;font-weight:600">
                 {{ $card->name }}
            </div>

            <div style="font-size:26px;font-weight:700;margin-top:8px">
                {{ $card->total }}
            </div>

            <div style="font-size:12px;color:#6b7280">
                Assigned Bases
            </div>
        </div>
    @endforeach

</div>

@endif

{{-- FILTER FORM --}}
<div class="card-box">

<div style="display: flex; justify-content: space-between; align-items: end; gap: 15px;">
    
    <form method="GET" action="{{ route('dashboard') }}" style="flex: 1;">

        <div style="display:flex;gap:15px;align-items:end;flex-wrap:wrap">

            <div>
                <label class="form-label">Employee</label>
                <select name="employee_id">
                    <option value="">All</option>
                    @foreach($employees as $id => $name)
                        <option value="{{ $id }}" {{ request('employee_id') == $id ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="form-label">Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="NEW" {{ request('status') == 'NEW' ? 'selected' : '' }}>NEW</option>
                    <option value="FOLLOW UP" {{ request('status') == 'FOLLOW UP' ? 'selected' : '' }}>FOLLOW UP</option>
                    <option value="CONFIRMED" {{ request('status') == 'CONFIRMED' ? 'selected' : '' }}>CONFIRMED</option>
                    <option value="NOPE" {{ request('status') == 'NOPE' ? 'selected' : '' }}>NOPE</option>
                </select>
            </div>

            
            {{-- From Date --}}
            <div>
                <label class="form-label">From Date</label>
                <input type="date"
                    name="from_date"
                    value="{{ request('from_date', now()->toDateString()) }}"
                    max="{{ now()->toDateString() }}">
            </div>

            {{-- To Date --}}
            <div>
                <label class="form-label">To Date</label>
                <input type="date"
                    name="to_date"
                    value="{{ request('to_date', now()->toDateString()) }}"
                    max="{{ now()->toDateString() }}">
            </div>

            <button type="submit" class="btn-upload">Filter</button>

            <a href="{{ route('dashboard') }}" class="btn-cancel-small">Cancel</a>

        </div>

    </form>

    <form method="GET" action="{{ route('dashboard.export') }}" id="exportForm" onsubmit="return confirmExport()">
        <input type="hidden" name="employee_id" id="export_employee_id" value="{{ request('employee_id') }}">
        <input type="hidden" name="status" id="export_status" value="{{ request('status') }}">
        <input type="hidden" name="from_date" id="export_from_date" value="{{ request('from_date') }}">
        <input type="hidden" name="to_date" id="export_to_date" value="{{ request('to_date') }}">
        <button type="submit" class="btn-upload" style="background: #059669; white-space: nowrap;">
            📥 Export
        </button>
    </form>

<script>
function confirmExport() {
    const employeeId = '{{ request("employee_id") }}';
    const status = '{{ request("status") }}';
    const fromDate = '{{ request("from_date") }}';
    const toDate = '{{ request("to_date") }}';
    
    // Get actual values from form inputs
    const employeeSelect = document.querySelector('select[name="employee_id"]');
    const statusSelect = document.querySelector('select[name="status"]');
    const fromDateInput = document.querySelector('input[name="from_date"]');
    const toDateInput = document.querySelector('input[name="to_date"]');
    
    // Update hidden fields with current form values
    document.getElementById('export_employee_id').value = employeeSelect ? employeeSelect.value : '';
    document.getElementById('export_status').value = statusSelect ? statusSelect.value : '';
    document.getElementById('export_from_date').value = fromDateInput ? fromDateInput.value : '';
    document.getElementById('export_to_date').value = toDateInput ? toDateInput.value : '';
    
    const actualEmployeeId = employeeSelect ? employeeSelect.value : '';
    const actualStatus = statusSelect ? statusSelect.value : '';
    const actualFromDate = fromDateInput ? fromDateInput.value : '';
    const actualToDate = toDateInput ? toDateInput.value : '';
    
    // Convert date format from YYYY-MM-DD to DD-MM-YYYY
    function formatDate(dateStr) {
        if (!dateStr) return 'Not specified';
        const parts = dateStr.split('-');
        if (parts.length === 3) {
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }
        return dateStr;
    }
    
    // Get employee name
    let employeeName = 'All';
    if (actualEmployeeId && employeeSelect) {
        const selectedOption = employeeSelect.querySelector(`option[value="${actualEmployeeId}"]`);
        if (selectedOption) {
            employeeName = selectedOption.text;
        }
    }
    
    const statusText = actualStatus || 'All';
    const fromDateText = formatDate(actualFromDate);
    const toDateText = formatDate(actualToDate);
    
    const message = `Employee: ${employeeName}\nStatus: ${statusText}\nFrom Date: ${fromDateText}\nTo Date: ${toDateText}\n\nDo you want to download the above categories?`;
    
    return confirm(message);
}
</script>

</div>

</div>

{{-- TABLE --}}
@if(isset($paginatedRows) && $paginatedRows->count() > 0)

<div class="card-box" style="margin-top:12px">

<table>
    <thead>
        <tr>
            <th>Emp ID</th>
            <th>Employee Name</th>
            <th>Customer Name</th>
            <th>Company Name</th>
            <th>Status</th>
            <th>Mobile</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @foreach($paginatedRows as $row)
        <tr>
            <td>{{ $row->emp_id }}</td>
            <td>{{ $row->employee->name ?? '' }}</td>
            <td>{{ $row->customer_name }}</td>
            <td>{{ $row->company_name }}</td>
            <td>{{ $row->status }}</td>
            <td>{{ $row->mobile }}</td>
            <td>{{ \Carbon\Carbon::parse($row->assign_date)->format('d/m/Y') }}</td>
            <td>
                <button class="action-btn btn-reminder" onclick="openNotifPanel({{ $row->id }})" title="View Notifications">
                    📅
                </button>
                @if($row->employee && $row->employee->application_id == 1)
                    <button class="action-btn btn-eye" onclick="viewDetails({{ $row->id }})" title="View Details">
                        👁️
                    </button>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

</div>
<div style="margin-top:15px">
    {{ $paginatedRows->onEachSide(1)->links('vendor.pagination.custom') }}
</div>
@endif

<script>
function viewDetails(assignBaseId) {
    window.location.href = `/loan-application-details/${assignBaseId}`;
}
</script>

@endsection