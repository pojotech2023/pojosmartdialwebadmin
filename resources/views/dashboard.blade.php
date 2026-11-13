@extends('layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="page-title">Dashboard</div>
<div class="page-subtitle">Welcome back, {{ auth()->user()->name }}</div>

{{-- ============ FULL-SCREEN NOTIFICATION PANEL ============ --}}
<div id="notifPanel" class="notif-panel">

    {{-- Header --}}
    <div class="notif-panel-header">
        <div class="notif-panel-header-left">
            <div class="notif-panel-icon"><i class="fas fa-bell"></i></div>
            <div>
                <div class="notif-panel-title">Notification List</div>
                <div id="notifPanelSubtitle" class="notif-panel-subtitle"></div>
            </div>
        </div>
        <button onclick="closeNotifPanel()" class="notif-close-btn">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    {{-- Content --}}
    <div id="notif-content" class="notif-panel-content">
        <div class="nl-loading">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading...</p>
        </div>
    </div>

</div>

{{-- ============ FULL-SCREEN WHATSAPP NOTIFICATION PANEL (VIEW ONLY) ============ --}}
<div id="waPanel" class="notif-panel">

    <div class="notif-panel-header" style="background:linear-gradient(135deg,#128c4a,#25d366 60%,#34e070);box-shadow:0 8px 24px rgba(18,140,74,0.25);">
        <div class="notif-panel-header-left">
            <div class="notif-panel-icon"><i class="fab fa-whatsapp"></i></div>
            <div>
                <div class="notif-panel-title">WhatsApp Notifications</div>
                <div id="waPanelSubtitle" class="notif-panel-subtitle"></div>
            </div>
        </div>
        <button onclick="closeWaPanel()" class="notif-close-btn">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <div id="wa-content" class="notif-panel-content">
        <div class="nl-loading">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading...</p>
        </div>
    </div>

</div>

{{-- ============ FULL-SCREEN MOBILE LEAD DETAILS PANEL ============ --}}
<div id="leadDetailsPanel" class="notif-panel">

    <div class="notif-panel-header lead-details-panel-header">
        <div class="notif-panel-header-left">
            <div class="notif-panel-icon"><i class="fas fa-user-tie"></i></div>
            <div>
                <div id="leadPanelTitle" class="notif-panel-title">Lead Details</div>
                <div id="leadPanelSubtitle" class="notif-panel-subtitle">Account Status</div>
            </div>
        </div>
        <button onclick="closeLeadDetailsPanel()" class="notif-close-btn">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <div id="lead-details-content" class="notif-panel-content">
        <div class="nl-loading">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Loading mobile details...</p>
        </div>
    </div>

</div>

{{-- Toast container --}}
<div id="toastContainer" style="position:fixed;bottom:24px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:10px"></div>

<style>
    .notif-panel {
        display: none;
        position: fixed; inset: 0; z-index: 9999;
        background: radial-gradient(circle at 15% 0%, #eef2ff 0%, #f5f7fb 40%, #f5f7fb 100%);
        overflow-y: auto;
    }

    .notif-panel-header {
        background: linear-gradient(135deg,#4338ca,#6d28d9 60%,#9333ea);
        color: #fff;
        padding: 20px 28px;
        display: flex; align-items: center; justify-content: space-between;
        position: sticky; top: 0; z-index: 10;
        box-shadow: 0 8px 24px rgba(76,29,149,0.25);
    }
    .notif-panel-header-left { display: flex; align-items: center; gap: 14px; }
    .notif-panel-icon {
        width: 44px; height: 44px; border-radius: 14px;
        background: rgba(255,255,255,0.15);
        backdrop-filter: blur(6px);
        display: flex; align-items: center; justify-content: center;
        font-size: 19px;
    }
    .notif-panel-title { font-size: 18px; font-weight: 800; letter-spacing: 0.2px; }
    .notif-panel-subtitle { font-size: 12.5px; color: rgba(255,255,255,0.75); margin-top: 2px; }

    .notif-close-btn {
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.25);
        color: #fff; border-radius: 10px;
        padding: 9px 18px; cursor: pointer;
        font-size: 13.5px; font-weight: 600;
        transition: background 0.2s, transform 0.15s;
    }
    .notif-close-btn:hover { background: rgba(255,255,255,0.28); transform: translateY(-1px); }

    .notif-panel-content { padding: 30px 30px 30px 40px; max-width: 880px; margin: 0; }
    #leadDetailsPanel {
        background: #f1f5f9;
    }
    #leadDetailsPanel .lead-details-panel-header {
        padding: 12px 24px;
        color: #0f172a;
        background: rgba(255,255,255,.96);
        border-bottom: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(15,23,42,.06);
    }
    #leadDetailsPanel .notif-panel-icon {
        color: #047857;
        background: #d1fae5;
        backdrop-filter: none;
    }
    #leadDetailsPanel .notif-panel-title { font-size: 16px; }
    #leadDetailsPanel .notif-panel-subtitle { color: #64748b; font-size: 12px; }
    #leadDetailsPanel .notif-close-btn {
        padding: 8px 13px;
        border-color: #e2e8f0;
        background: #fff;
        color: #334155;
    }
    #leadDetailsPanel .notif-close-btn:hover { background: #f8fafc; }
    #leadDetailsPanel .notif-panel-content { max-width: 1040px !important; padding: 22px 20px 36px !important; margin: 0 auto !important; }
    .lead-details-card {
        overflow: hidden;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(15,23,42,.06);
    }
    .lead-details-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 18px 22px;
        color: #0f172a;
        background: #fff;
        border-bottom: 1px solid #edf2f7;
    }
    .lead-details-identity { display:flex; align-items:center; gap:14px; min-width:0; }
    .lead-details-avatar {
        display:flex; align-items:center; justify-content:center;
        width:52px; height:52px; flex:none;
        border:0; border-radius:13px;
        background:#fee2e2; color:#ef4444;
        font-size:20px; font-weight:800;
    }
    .lead-details-name { color:#172033; font-size:18px; font-weight:800; line-height:1.25; }
    .lead-details-meta { display:flex; flex-wrap:wrap; gap:6px 12px; margin-top:6px; color:#64748b; font-size:12px; }
    .lead-details-hero-right { display:flex; align-items:center; justify-content:flex-end; flex-wrap:wrap; gap:10px; }
    .lead-status-block { display:flex; flex-direction:column; gap:5px; min-width:150px; }
    .lead-status-label { color:#718096; font-size:10px; font-weight:800; letter-spacing:.06em; text-transform:uppercase; }
    .lead-details-hero .account-status-select { width:100%; min-width:160px; }
    .lead-details-hero .status-badge { align-self:flex-start; }
    .lead-action-link {
        display:inline-flex; align-items:center; justify-content:center; gap:7px;
        padding:9px 13px; border:1px solid #e2e8f0; border-radius:9px;
        color:#0f172a; text-decoration:none; font-size:12px; font-weight:700;
        background:#f1f5f9; transition:background .15s ease, transform .15s ease;
    }
    .lead-action-link:hover { color:#0f172a; background:#e2e8f0; transform:translateY(-1px); }
    .lead-action-link-whatsapp { border-color:#16a34a; color:#fff; background:#16a34a; }
    .lead-action-link-whatsapp:hover { background:#15803d; }
    .lead-details-body { padding:16px 22px 20px; }
    .lead-panel-layout { display:block; }
    .lead-panel-section { min-width:0; padding:0; border:0; border-radius:0; background:transparent; }
    .lead-panel-section-title { display:none; }
    .lead-panel-section-title i { color:#4f46e5; }
    .lead-details-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:9px; }
    .lead-details-grid > div { min-width:0; }
    .lead-account-note { margin:12px 0 16px; }
    .lead-account-note-title { margin-bottom:5px; }
    .lead-activity-section { margin-top:14px; padding-top:14px; border-top:1px solid #edf2f7; }
    .lead-activity-section > div:first-child { margin-bottom:10px !important; }
    .lead-activity-section .lead-remarks-list { max-height:280px; }
    .lead-remarks-list > div { margin:0 !important; padding:11px 13px !important; }
    .lead-assignment-unassigned { color:#b45309; }
    @media (max-width: 860px) {
        .lead-details-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media (max-width: 620px) {
        .lead-details-hero { align-items:flex-start; flex-direction:column; padding:18px; }
        .lead-details-hero-right { justify-content:flex-start; width:100%; }
        .lead-status-block { flex:1; }
        .lead-details-body { padding:13px; }
        .lead-details-grid { grid-template-columns:1fr; }
    }
    .lead-account-note {
        padding: 11px 13px;
        margin-bottom: 18px;
        border: 1px solid #edf2f7;
        border-radius: 10px;
        background: #f8fafc;
    }
    .lead-account-note-title {
        margin-bottom: 5px;
        color: #718096;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }
    .lead-account-note-text {
        color: #1e293b;
        font-size: 13px;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }
    .lead-remarks-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-height: 260px;
        overflow-y: auto;
        padding-right: 4px;
    }
    @media (max-width: 760px) {
        #leadDetailsPanel .notif-panel-content { padding: 14px 12px 24px !important; }
    }

    /* Account Status Select Pills & Badges */
    .account-status-select {
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        cursor: pointer;
        outline: none;
        border: 1.5px solid transparent;
        transition: all 0.2s ease;
        display: inline-block;
        box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    }
    .account-status-select:hover { filter: brightness(0.96); transform: translateY(-1px); }
    .account-status-select:focus { box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25); }

    .account-status-select.status-new,
    .status-badge.status-new {
        background: #e0e7ff; color: #4338ca; border-color: #c7d2fe;
    }
    .account-status-select.status-in-progress,
    .status-badge.status-in-progress {
        background: #fef3c7; color: #b45309; border-color: #fde68a;
    }
    .account-status-select.status-quotation-shared,
    .status-badge.status-quotation-shared {
        background: #f3e8ff; color: #7e22ce; border-color: #e9d5ff;
    }
    .account-status-select.status-client-replied,
    .status-badge.status-client-replied {
        background: #d1fae5; color: #047857; border-color: #a7f3d0;
    }

    .nl-loading { text-align: center; color: #9ca3af; padding: 70px 0; }
    .nl-loading i { font-size: 30px; color: #6d28d9; }

    @keyframes cardIn {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .nl-card {
        background: #fff;
        border-radius: 18px;
        padding: 20px 22px;
        margin-bottom: 16px;
        box-shadow: 0 4px 16px rgba(30,27,75,0.07);
        border: 1px solid #eef0f6;
        display: flex;
        align-items: flex-start;
        gap: 18px;
        transition: box-shadow 0.25s ease, transform 0.25s ease, border-color 0.25s ease;
        position: relative;
        overflow: hidden;
        animation: cardIn 0.4s ease both;
    }
    .nl-card::before {
        content: '';
        position: absolute; left: 0; top: 0; bottom: 0; width: 5px;
        background: linear-gradient(180deg,#6366f1,#8b5cf6);
    }
    .nl-card-due::before      { background: linear-gradient(180deg,#f87171,#dc2626); }
    .nl-card-upcoming::before { background: linear-gradient(180deg,#8b5cf6,#6d28d9); }
    .nl-card:hover { box-shadow: 0 14px 34px rgba(30,27,75,0.14); transform: translateY(-3px); }
    .nl-card-due { border-color: #fecdd3; box-shadow: 0 4px 18px rgba(220,38,38,0.10); }
    .nl-card-due:hover { border-color: #fca5a5; }
    .nl-card-upcoming:hover { border-color: #ddd6fe; }

    .nl-icon {
        width: 48px; height: 48px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 19px; flex-shrink: 0;
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.03);
    }
    .nl-icon-due      { background: linear-gradient(135deg,#fee2e2,#fecaca); color: #dc2626; }
    .nl-icon-upcoming { background: linear-gradient(135deg,#ede9fe,#ddd6fe); color: #6d28d9; }

    .nl-customer   { font-size: 15.5px; font-weight: 800; color: #0f172a; letter-spacing: 0.1px; }
    .nl-meta-row   { display: flex; flex-wrap: wrap; gap: 5px 14px; margin-top: 5px; }
    .nl-employee, .nl-mobile, .nl-datetime { font-size: 12px; color: #6b7280; display: flex; align-items: center; gap: 5px; }
    .nl-employee i, .nl-mobile i, .nl-datetime i { color: #a5adba; width: 12px; text-align: center; }
    .nl-remarks    {
        font-size: 13px; color: #374151; margin-top: 10px;
        background: #f8fafc; border-radius: 11px; padding: 9px 13px;
        display: flex; align-items: flex-start; gap: 6px;
        border: 1px solid #f1f5f9;
    }
    .nl-notification-status {
        display:inline-flex;
        align-items:center;
        gap:5px;
        margin-top:8px;
        padding:4px 9px;
        border:1px solid #e0e7ff;
        border-radius:7px;
        background:#eef2ff;
        color:#4338ca;
        font-size:11px;
        font-weight:700;
    }

    .nl-status-pill {
        border-radius: 50px; font-size: 11px; padding: 6px 13px; font-weight: 700;
        display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.03);
    }
    .nl-status-due      { background: linear-gradient(135deg,#fee2e2,#fecaca); color: #dc2626; }
    .nl-status-upcoming { background: linear-gradient(135deg,#ede9fe,#ddd6fe); color: #6d28d9; }

    .nl-dot {
        width: 7px; height: 7px; border-radius: 50%; background: #dc2626;
        animation: pulseDot 1.2s ease-in-out infinite;
    }
    @keyframes pulseDot {
        0%, 100% { box-shadow: 0 0 0 0 rgba(220,38,38,0.5); }
        50%      { box-shadow: 0 0 0 5px rgba(220,38,38,0); }
    }

    .nl-empty {
        text-align: center; padding: 70px 0; color: #94a3b8;
        background: #fff; border-radius: 16px; border: 1px dashed #e2e8f0;
    }
    .nl-empty i { font-size: 46px; color: #c7d2fe; }

    .toast-notif {
        background: linear-gradient(135deg,#1e1b4b,#312e81);
        color: #fff; border-radius: 16px;
        padding: 16px 18px; min-width: 280px; max-width: 360px;
        box-shadow: 0 12px 32px rgba(30,27,75,0.35);
        border: 1px solid rgba(255,255,255,0.08);
        animation: slideInRight 0.35s cubic-bezier(.2,.8,.2,1);
    }
    @keyframes slideInRight {
        from { transform: translateX(110px); opacity: 0; }
        to   { transform: translateX(0);     opacity: 1; }
    }

    .btn-mark-seen {
        background: linear-gradient(135deg,#6366f1,#8b5cf6);
        color: #fff; border: none; border-radius: 8px;
        padding: 7px 14px; font-size: 12px; font-weight: 600; cursor: pointer;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .btn-mark-seen:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(99,102,241,0.4); }

    .wa-card::before { background: linear-gradient(180deg,#25d366,#128c4a); }
    .wa-thumb {
        width: 48px; height: 48px; border-radius: 12px; object-fit: cover; flex-shrink: 0;
        box-shadow: inset 0 0 0 1px rgba(0,0,0,0.06);
    }
    .wa-thumb-placeholder {
        width: 48px; height: 48px; border-radius: 12px; flex-shrink: 0;
        background: linear-gradient(135deg,#dcfce7,#bbf7d0); color: #128c4a;
        display: flex; align-items: center; justify-content: center; font-size: 18px;
    }
    .wa-status-pending { background: linear-gradient(135deg,#fef3c7,#fde68a); color: #b45309; }
    .wa-status-sent     { background: linear-gradient(135deg,#dcfce7,#bbf7d0); color: #128c4a; }
    .wa-status-failed   { background: linear-gradient(135deg,#fee2e2,#fecaca); color: #dc2626; }
</style>

<script>
const NOTIF_ROUTES = {
    data:     '{{ route("dashboard.notifications.data") }}',
    markSeen: '{{ route("dashboard.notifications.mark-seen") }}',
    csrf:     '{{ csrf_token() }}'
};

const WA_NOTIF_ROUTE = '{{ route("dashboard.whatsapp-notifications.data") }}';
const LEAD_DETAILS_ROUTE = '{{ url("/dashboard/lead-details-data") }}';

// ---- WhatsApp panel (view only) ----
function openWaPanel(assignBaseId) {
    document.getElementById('waPanel').style.display = 'block';
    document.body.style.overflow = 'hidden';
    loadWaNotifications(assignBaseId);
}
function closeWaPanel() {
    document.getElementById('waPanel').style.display = 'none';
    document.body.style.overflow = '';
}
function loadWaNotifications(assignBaseId) {
    document.getElementById('wa-content').innerHTML =
        '<div class="nl-loading"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';

    fetch(WA_NOTIF_ROUTE + '?assign_base_id=' + assignBaseId)
        .then(r => r.ok ? r.json() : r.json().then(e => Promise.reject(e.error || 'Server error ' + r.status)))
        .then(data => renderWaAll(data))
        .catch(err => {
            document.getElementById('wa-content').innerHTML =
                `<p style="color:#ef4444;text-align:center;padding:40px"><b>Error:</b> ${err}</p>`;
        });
}
function renderWaAll(data) {
    const sub = document.getElementById('waPanelSubtitle');
    sub.textContent = '— ' + escHtml(data.customer_name) + ' (' + escHtml(data.mobile) + ')';

    const container = document.getElementById('wa-content');
    const items = data.items || [];

    if (items.length === 0) {
        container.innerHTML = `<div class="nl-empty">
            <i class="fab fa-whatsapp"></i>
            <p style="margin-top:14px;font-size:15px;font-weight:600">No WhatsApp notifications found</p>
            <p style="font-size:13px">No WhatsApp messages have been scheduled for this customer yet.</p>
        </div>`;
        return;
    }

    let html = '';
    items.forEach((n, idx) => {
        const statusClass = 'wa-status-' + n.status;
        const statusLabel = n.status.charAt(0).toUpperCase() + n.status.slice(1);
        const thumb = n.header_image
            ? `<img class="wa-thumb" src="${n.header_image}" alt="header">`
            : `<div class="wa-thumb-placeholder"><i class="fab fa-whatsapp"></i></div>`;

        html += `<div class="nl-card wa-card" style="animation-delay:${idx * 35}ms">
            ${thumb}
            <div style="flex:1;min-width:0">
                <div class="nl-meta-row">
                    <span class="nl-employee"><i class="fas fa-indian-rupee-sign"></i> ${escHtml(n.amount ?? '—')}</span>
                    <span class="nl-employee"><i class="fas fa-calendar-day"></i> Due: ${escHtml(n.due_date ?? '—')}</span>
                    <span class="nl-datetime"><i class="fas fa-clock"></i> Scheduled: ${n.scheduled_at}</span>
                </div>
                ${n.error ? `<div class="nl-remarks"><i class="fas fa-triangle-exclamation"></i>${escHtml(n.error)}</div>` : ''}
            </div>
            <div style="flex-shrink:0">
                <span class="nl-status-pill ${statusClass}">${statusLabel}</span>
            </div>
        </div>`;
    });

    container.innerHTML = html;
}

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
            '<div class="nl-loading"><i class="fas fa-spinner fa-spin"></i><p>Loading...</p></div>';
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
    const all = [...(data.due || []), ...(data.upcoming || [])];
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

// ---- Render all notifications as one flat list ----
function renderAll() {
    const container = document.getElementById('notif-content');
    const due      = notifData.due || [];
    const upcoming = notifData.upcoming || [];
    const all      = [...due.map(n => ({ ...n, kind: 'due' })), ...upcoming.map(n => ({ ...n, kind: 'upcoming' }))];

    if (all.length === 0) {
        container.innerHTML = `<div class="nl-empty">
            <i class="fas fa-bell-slash"></i>
            <p style="margin-top:14px;font-size:15px;font-weight:600">No notifications found</p>
            <p style="font-size:13px">No reminders have been set for this customer yet.</p>
        </div>`;
        return;
    }

    let html = '';
    all.forEach((n, idx) => {
        const kind = n.kind;
        html += `<div class="nl-card nl-card-${kind}" style="animation-delay:${idx * 35}ms">
            <div class="nl-icon nl-icon-${kind}"><i class="fas fa-bell"></i></div>
            <div style="flex:1;min-width:0">
                <div class="nl-customer">${escHtml(n.customer_name)}</div>
                <div class="nl-meta-row">
                    <span class="nl-employee"><i class="fas fa-user"></i> ${escHtml(n.employee_name)}</span>
                    <span class="nl-employee"><i class="fas fa-building"></i> ${escHtml(n.company_name)}</span>
                    <span class="nl-mobile"><i class="fas fa-phone"></i> ${escHtml(n.mobile)}</span>
                    <span class="nl-datetime"><i class="fas fa-calendar"></i> ${n.notification_date} &nbsp;<i class="fas fa-clock"></i> ${n.notification_time}</span>
                </div>
                ${n.notification_status ? `<div class="nl-notification-status"><i class="fas fa-tag"></i> Notification Status: ${escHtml(n.notification_status)}</div>` : ''}
                ${n.remarks ? `<div class="nl-remarks"><i class="fas fa-comment-dots"></i>${escHtml(n.remarks)}</div>` : ''}
            </div>
            <div style="flex-shrink:0;display:flex;flex-direction:column;align-items:flex-end;gap:8px">
                <span class="nl-status-pill nl-status-${kind}">
                    ${kind === 'due' ? '<span class="nl-dot"></span> Due' : '<i class="fas fa-hourglass-half"></i> Upcoming'}
                </span>
                ${kind === 'due' ? `<button class="btn-mark-seen" onclick="markSeen([${n.id}])"><i class="fas fa-check"></i> Mark Seen</button>` : ''}
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

{{-- TELECALLER SUMMARY CARDS --}}
@php
    $statGradients = [
        'linear-gradient(135deg,#6366f1,#4f46e5)',
        'linear-gradient(135deg,#34d399,#10b981)',
        'linear-gradient(135deg,#fbbf24,#f59e0b)',
        'linear-gradient(135deg,#38bdf8,#0ea5e9)',
        'linear-gradient(135deg,#f472b6,#ec4899)',
        'linear-gradient(135deg,#a78bfa,#8b5cf6)',
    ];
@endphp
@if($isAdmin && $monthlyTargetCards->isNotEmpty())

<section class="monthly-target-section" aria-labelledby="monthly-target-title">
    <header class="monthly-target-toolbar">
        <div class="monthly-target-title-group">
            <span class="monthly-target-title-icon" aria-hidden="true"><i class="fas fa-chart-line"></i></span>
            <div>
                <h2 id="monthly-target-title">Monthly Target History</h2>
                <p>Review telecaller targets and confirmed value archived each month.</p>
            </div>
        </div>
        <form method="GET" action="{{ route('dashboard') }}" class="monthly-target-month-form">
            @foreach(['employee_id', 'product_id', 'status', 'account_status', 'from_date', 'to_date'] as $filter)
                @if(request()->filled($filter))
                    <input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">
                @endif
            @endforeach
            <label for="target-month">Archive month</label>
            <div class="monthly-target-month-control">
                <i class="far fa-calendar-alt" aria-hidden="true"></i>
                <input id="target-month" type="month" name="target_month" value="{{ $selectedTargetMonth }}" max="{{ now()->format('Y-m') }}" required>
            </div>
            <button type="submit" class="btn-upload"><i class="fas fa-arrow-right" aria-hidden="true"></i> View</button>
        </form>
    </header>

    <div class="monthly-target-grid">
        @foreach($monthlyTargetCards as $index => $card)
            <article class="monthly-target-employee" data-telecaller-card="{{ $card->name }}">
                <div class="monthly-target-employee-header">
                    <span class="monthly-target-employee-icon" style="--employee-accent: {{ $statGradients[$index % count($statGradients)] }};">
                        <i class="fas fa-user-tie" aria-hidden="true"></i>
                    </span>
                    <div class="monthly-target-employee-info">
                        <h3>{{ $card->name }}</h3>
                        <span>{{ number_format($card->total) }} assigned {{ \Illuminate\Support\Str::plural('customer', $card->total) }}</span>
                    </div>
                </div>
                <div class="monthly-target-metrics">
                    <div class="monthly-target-metric">
                        <span>Monthly target</span>
                        <strong>₹{{ number_format($card->target, 2) }}</strong>
                    </div>
                    <div class="monthly-target-metric monthly-target-metric-archived">
                        <span>Archived</span>
                        <strong>₹{{ number_format($card->archived, 2) }}</strong>
                    </div>
                    <div class="monthly-target-metric monthly-target-metric-balance">
                        <span>Balance to achieve</span>
                        <strong>₹{{ number_format($card->remaining, 2) }}</strong>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
    <footer class="monthly-target-footer">
        Showing archived values for <strong>{{ \Illuminate\Support\Carbon::createFromFormat('!Y-m', $selectedTargetMonth)->format('F Y') }}</strong>
    </footer>
</section>
@elseif(isset($cardData) && $cardData->count() > 0)
    <div class="stat-grid">
        @foreach($cardData as $index => $card)
            <div class="stat-card">
                <div class="stat-icon" style="background:{{ $statGradients[$index % count($statGradients)] }};">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $card->total }}</div>
                    <div class="stat-label">{{ $card->name }} — Assigned Bases</div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<style>
    .monthly-target-section { margin: 0 0 18px; padding: 18px; border: 1px solid #e5eaf2; border-radius: 16px; background: #fff; box-shadow: 0 6px 20px rgba(15, 23, 42, .045); }
    .monthly-target-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 0 2px 16px; border-bottom: 1px solid #edf0f5; }
    .monthly-target-title-group { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .monthly-target-title-icon { display: grid; width: 40px; height: 40px; flex: 0 0 auto; place-items: center; border-radius: 12px; background: #eef2ff; color: #4f46e5; font-size: 16px; }
    .monthly-target-toolbar h2 { margin: 0; color: #172033; font-size: 16px; font-weight: 750; }
    .monthly-target-toolbar p { margin: 4px 0 0; color: #788397; font-size: 12px; }
    .monthly-target-month-form { display: flex; align-items: center; gap: 9px; padding: 8px; border: 1px solid #e8ecf3; border-radius: 12px; background: #f8fafc; }
    .monthly-target-month-form label { padding-left: 5px; color: #526078; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .monthly-target-month-control { position: relative; }
    .monthly-target-month-control > i { position: absolute; top: 50%; left: 10px; transform: translateY(-50%); color: #818ba0; font-size: 12px; pointer-events: none; }
    .monthly-target-month-control input { width: 150px; min-height: 36px; padding: 0 9px 0 30px; border: 1px solid #dfe5ee; border-radius: 8px; background: #fff; color: #253149; font-size: 12px; font-weight: 600; }
    .monthly-target-month-control input:focus { border-color: #818cf8; outline: 3px solid rgba(99, 102, 241, .12); }
    .monthly-target-month-form .btn-upload { display: inline-flex; min-height: 36px; align-items: center; gap: 6px; padding: 8px 13px; border-radius: 8px; }
    .monthly-target-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 340px), 1fr)); gap: 14px; padding-top: 16px; }
    .monthly-target-employee { min-width: 0; padding: 16px; border: 1px solid #e9edf4; border-radius: 13px; background: linear-gradient(145deg, #fff 0%, #fbfcff 100%); transition: border-color .15s ease, box-shadow .15s ease, transform .15s ease; }
    .monthly-target-employee:hover { transform: translateY(-1px); border-color: #d7ddf0; box-shadow: 0 7px 18px rgba(15, 23, 42, .055); }
    .monthly-target-employee-header { display: flex; align-items: center; gap: 11px; }
    .monthly-target-employee-icon { display: grid; width: 42px; height: 42px; flex: 0 0 auto; place-items: center; border-radius: 12px; background: var(--employee-accent); color: #fff; font-size: 16px; }
    .monthly-target-employee-info { min-width: 0; }
    .monthly-target-employee-info h3 { overflow: hidden; margin: 0; color: #172033; font-size: 14px; font-weight: 750; text-overflow: ellipsis; white-space: nowrap; }
    .monthly-target-employee-info span { display: block; margin-top: 4px; color: #788397; font-size: 11px; }
    .monthly-target-metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 15px; padding-top: 13px; border-top: 1px solid #edf0f5; }
    .monthly-target-metric { min-width: 0; }
    .monthly-target-metric span { display: block; min-height: 15px; margin-bottom: 5px; color: #788397; font-size: 10px; line-height: 1.35; }
    .monthly-target-metric strong { color: #253149; font-size: 13px; font-weight: 750; white-space: nowrap; }
    .monthly-target-metric-archived strong { color: #059669; }
    .monthly-target-metric-balance strong { color: #4f46e5; }
    .monthly-target-footer { margin-top: 12px; color: #788397; font-size: 11px; text-align: right; }
    .monthly-target-footer strong { color: #526078; font-weight: 700; }
    @media (max-width: 480px) {
        .monthly-target-section { padding: 14px; }
        .monthly-target-toolbar { align-items: stretch; flex-direction: column; gap: 14px; }
        .monthly-target-month-form { width: 100%; flex-wrap: wrap; }
        .monthly-target-month-control { flex: 1; }
        .monthly-target-month-control input { width: 100%; }
        .monthly-target-month-form .btn-upload { justify-content: center; }
        .monthly-target-grid { grid-template-columns: minmax(0, 1fr); gap: 10px; }
        .monthly-target-employee { padding: 14px; }
        .monthly-target-metrics { gap: 5px; }
        .monthly-target-metric span { font-size: 9px; }
        .monthly-target-metric strong { font-size: 11px; }
    }
</style>

{{-- FILTER FORM --}}
<div class="card-box">

<div style="display: flex; justify-content: space-between; align-items: end; gap: 15px;">
    
    <form method="GET" action="{{ route('dashboard') }}" style="flex: 1;">

        <div style="display:flex;gap:15px;align-items:end;flex-wrap:wrap">

            @if($isAdmin)
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
            @endif
            <div>
                @if($isAccount)
                <label class="form-label">Account Status</label>
                <select name="account_status">
                    <option value="">All</option>
                    <option value="NEW" {{ request('account_status') == 'NEW' ? 'selected' : '' }}>NEW</option>
                    <option value="IN PROGRESS" {{ request('account_status') == 'IN PROGRESS' ? 'selected' : '' }}>IN PROGRESS</option>
                    <option value="QUOTATION SHARED" {{ request('account_status') == 'QUOTATION SHARED' ? 'selected' : '' }}>QUOTATION SHARED</option>
                    <option value="CLIENT REPLIED" {{ request('account_status') == 'CLIENT REPLIED' ? 'selected' : '' }}>CLIENT REPLIED</option>
                    <option value="CONFIRMED" {{ request('account_status') == 'CONFIRMED' ? 'selected' : '' }}>CONFIRMED</option>
                </select>
                @else
                <label class="form-label">Telecaller Status</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="NEW" {{ request('status') == 'NEW' ? 'selected' : '' }}>NEW</option>
                    <option value="FOLLOW UP" {{ request('status') == 'FOLLOW UP' ? 'selected' : '' }}>FOLLOW UP</option>
                    <option value="CONFIRMED" {{ request('status') == 'CONFIRMED' ? 'selected' : '' }}>CONFIRMED</option>
                    <option value="NOPE" {{ request('status') == 'NOPE' ? 'selected' : '' }}>NOPE</option>
                </select>
                @endif
            </div>
            <div>
                <label class="form-label">Product Type</label>
                <select name="product_id">
                    <option value="">All Products</option>
                    @foreach($products as $pId => $pName)
                        <option value="{{ $pId }}" {{ request('product_id') == $pId ? 'selected' : '' }}>
                            {{ $pName }}
                        </option>
                    @endforeach
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

            <button type="submit" class="btn-upload"><i class="fas fa-filter"></i> Filter</button>

            <a href="{{ route('dashboard') }}" class="btn-cancel-small"><i class="fas fa-rotate-left"></i> Cancel</a>

        </div>

    </form>

    <form method="GET" action="{{ route('dashboard.export') }}" id="exportForm" onsubmit="return confirmExport()">
        <input type="hidden" name="employee_id" id="export_employee_id" value="{{ request('employee_id') }}">
        <input type="hidden" name="product_id" id="export_product_id" value="{{ request('product_id') }}">
        <input type="hidden" name="{{ $isAccount ? 'account_status' : 'status' }}" id="export_status" value="{{ $isAccount ? request('account_status') : request('status') }}">
        <input type="hidden" name="from_date" id="export_from_date" value="{{ request('from_date') }}">
        <input type="hidden" name="to_date" id="export_to_date" value="{{ request('to_date') }}">
        <button type="submit" class="btn-upload" style="background: linear-gradient(135deg,#34d399,#059669); white-space: nowrap;">
            <i class="fas fa-download"></i> Export
        </button>
    </form>

<script>
function confirmExport() {
    const employeeId = '{{ request("employee_id") }}';
    const fromDate = '{{ request("from_date") }}';
    const toDate = '{{ request("to_date") }}';
    
    // Get actual values from form inputs
    const employeeSelect = document.querySelector('select[name="employee_id"]');
    const statusSelect = document.querySelector('select[name="{{ $isAccount ? 'account_status' : 'status' }}"]');
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
    
    const message = `Employee: ${employeeName}\n{{ $isAccount ? 'Account Status' : 'Telecaller Status' }}: ${statusText}\nFrom Date: ${fromDateText}\nTo Date: ${toDateText}\n\nDo you want to download the above categories?`;
    
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
            @if($isAdmin)
                <th>Emp ID</th>
                <th>Employee Name</th>
            @else
                <th>Lead ID</th>
                <th>Assigned By (Telecaller)</th>
            @endif
            <th>Customer Name</th>
            <th>Company Name</th>
            <th>Product Type</th>
            <th>{{ $isAccount ? 'Account Status' : 'Telecaller Status' }}</th>
            <th>Mobile</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @foreach($paginatedRows as $row)
        <tr>
            <td>#{{ $row->id }}</td>
            <td>
                <div style="font-weight:600;color:#1e293b;">{{ $row->employee->name ?? '—' }}</div>
                @if(!$isAdmin && $row->assignedUser)
                    <div style="font-size:11px;color:#059669;font-weight:700;">
                        <i class="fas fa-check-circle" style="font-size:10px;"></i> Assigned to you
                    </div>
                @elseif($isAdmin && $row->assignedUser)
                    <div style="font-size:11px;color:#059669;font-weight:700;">
                        <i class="fas fa-user-check" style="font-size:10px;"></i> {{ $row->assignedUser->name }}
                    </div>
                @endif
            </td>
            <td>{{ $row->customer_name }}</td>
            <td>{{ $row->company_name }}</td>
            <td>
                @if($row->product || $row->product_type)
                    <span style="background:#eef2ff;color:#4338ca;padding:3px 9px;border-radius:12px;font-size:11.5px;font-weight:700;">
                        <i class="fas fa-tag" style="font-size:10px;margin-right:3px;"></i> {{ $row->product->name ?? $row->product_type }}
                    </span>
                @else
                    <span style="color:#94a3b8;font-size:12px;">—</span>
                @endif
            </td>
            <td>
                @if($isAccount)
                    @php($displayStatus = $row->account_status ?? $row->leadDetail->account_status ?? 'NEW')
                @else
                    @php($displayStatus = $row->status ?? 'NEW')
                @endif
                <span class="status-badge status-{{ \Illuminate\Support\Str::slug($displayStatus) }}">
                    {{ $displayStatus }}
                </span>
            </td>
            <td>{{ $row->mobile }}</td>
            <td>{{ \Carbon\Carbon::parse($row->assign_date)->format('d/m/Y') }}</td>
            <td>
                @if($isAdmin)
                    <button class="icon-btn icon-btn-reminder" onclick="openNotifPanel({{ $row->id }})" title="View Notifications">
                        <i class="fas fa-bell"></i>
                    </button>
                    <button class="icon-btn icon-btn-whatsapp" onclick="openWaPanel({{ $row->id }})" title="View WhatsApp Notifications">
                        <i class="fab fa-whatsapp"></i>
                    </button>
                @endif
                <button class="icon-btn icon-btn-view" onclick="openLeadDetailsPanel({{ $row->id }})" title="View Project Details">
                    <i class="fas fa-eye"></i>
                </button>
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
const IS_ACCOUNT = {{ $isAccount ? 'true' : 'false' }};

// ---- Project Details Panel (Eye Icon) ----
function openLeadDetailsPanel(assignBaseId) {
    document.getElementById('leadDetailsPanel').style.display = 'block';
    document.body.style.overflow = 'hidden';
    loadLeadDetails(assignBaseId);
}

function closeLeadDetailsPanel() {
    document.getElementById('leadDetailsPanel').style.display = 'none';
    document.body.style.overflow = '';
}

function viewDetails(assignBaseId) {
    openLeadDetailsPanel(assignBaseId);
}

function loadLeadDetails(assignBaseId) {
    document.getElementById('lead-details-content').innerHTML =
        '<div class="nl-loading"><i class="fas fa-spinner fa-spin"></i><p>Loading project details...</p></div>';

    fetch(LEAD_DETAILS_ROUTE + '/' + assignBaseId)
        .then(r => r.ok ? r.json() : r.json().then(e => Promise.reject(e.message || 'Server error ' + r.status)))
        .then(res => {
            if (res.status && res.data) {
                renderLeadDetails(res.data);
            } else {
                throw new Error(res.message || 'Failed to load details');
            }
        })
        .catch(err => {
            document.getElementById('lead-details-content').innerHTML =
                `<p style="color:#ef4444;text-align:center;padding:40px"><b>Error:</b> ${err}</p>`;
        });
}

function renderLeadDetails(d) {
    const title = document.getElementById('leadPanelTitle');
    const sub = document.getElementById('leadPanelSubtitle');
    if (title) title.innerHTML = escHtml(d.customer_name || 'Customer Details');
    if (sub) sub.innerHTML = `<i class="fas fa-phone-alt" style="font-size:11px;margin-right:4px;"></i> ${escHtml(d.mobile || '—')} &bull; Lead #${d.id}`;

    const p = d.project_details || {};
    const appt = d.appointment_details || {};
    const notes = Array.isArray(d.notes) ? d.notes : [];
    const isGstYes = (p.gst === 'Y' || p.gst === 'YES');
    const hasAssignedAccount = Boolean(d.assigned_to?.id);
    const displayStatus = hasAssignedAccount ? (p.account_status || 'NEW') : (d.status || 'NEW');
    const displayStatusLabel = hasAssignedAccount ? 'Account Status' : 'Telecaller Status';
    const statusSlug = escHtml(displayStatus.toLowerCase().replace(/\s+/g, '-'));

    let html = `
    <div class="lead-details-card">

        <div class="lead-details-hero">
            <div class="lead-details-identity">
                <div class="lead-details-avatar">
                    ${escHtml((d.customer_name || 'C').charAt(0).toUpperCase())}
                </div>
                <div>
                    <div class="lead-details-name">${escHtml(d.customer_name || 'Customer')}</div>
                    <div class="lead-details-meta">
                        <span><i class="fas fa-tag"></i> ${escHtml(p.project_type || d.product_type || 'Website')}</span>
                        <span><i class="far fa-building"></i> ${escHtml(p.cus_company_name || d.company_name || '—')}</span>
                        <span><i class="far fa-calendar-alt"></i> ${escHtml(d.assign_date || '—')}</span>
                    </div>
                </div>
            </div>

            <div class="lead-details-hero-right">
                <div class="lead-status-block">
                    <div class="lead-status-label">${displayStatusLabel}</div>
                    ${hasAssignedAccount && IS_ACCOUNT ? `
                        <select id="modal-status-badge-${d.id}"
                                class="account-status-select status-${statusSlug}"
                                data-lead-id="${d.id}"
                                data-current="${escHtml(displayStatus)}"
                                onchange="changeAccountLeadStatus(${d.id}, this.value, this)"
                                style="padding: 8px 12px; font-size: 12px; font-weight: 800;">
                            <option value="NEW" ${String(displayStatus).toUpperCase() === 'NEW' ? 'selected' : ''}>NEW</option>
                            <option value="IN PROGRESS" ${String(displayStatus).toUpperCase() === 'IN PROGRESS' ? 'selected' : ''}>IN PROGRESS</option>
                            <option value="QUOTATION SHARED" ${String(displayStatus).toUpperCase() === 'QUOTATION SHARED' ? 'selected' : ''}>QUOTATION SHARED</option>
                            <option value="CLIENT REPLIED" ${String(displayStatus).toUpperCase() === 'CLIENT REPLIED' ? 'selected' : ''}>CLIENT REPLIED</option>
                            <option value="CONFIRMED" ${String(displayStatus).toUpperCase() === 'CONFIRMED' ? 'selected' : ''}>CONFIRMED</option>
                        </select>
                    ` : `
                        <span id="modal-status-badge-${d.id}" class="status-badge status-${statusSlug}" style="padding: 7px 13px; font-size: 12px;">
                            ${escHtml(displayStatus)}
                        </span>
                    `}
                </div>

                <a class="lead-action-link" href="tel:${escHtml(d.mobile)}">
                    <i class="fas fa-phone-alt"></i> Call
                </a>

                <a class="lead-action-link lead-action-link-whatsapp" href="https://wa.me/${escHtml(d.mobile).replace(/[^\d]/g, '')}" target="_blank" rel="noopener noreferrer">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
            </div>
        </div>

        <div class="lead-details-body">
          <div class="lead-panel-layout">
            <section class="lead-panel-section">
                <h3 class="lead-panel-section-title"><i class="fas fa-circle-info"></i> Lead overview</h3>

            {{-- 1. Unified Project & Assignment Key-Value Grid --}}
            <div class="lead-details-grid" style="margin-bottom:14px;">

                {{-- Origin Telecaller --}}
                <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 14px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px;">
                        <i class="fas fa-headset" style="color: #6366f1; margin-right: 4px;"></i> Origin Telecaller
                    </div>
                    <div style="font-size: 14px; font-weight: 800; color: #0f172a;">
                        ${escHtml(d.telecaller?.name || '—')}
                    </div>
                    ${d.telecaller?.mobile ? `<div style="font-size: 12px; color: #64748b; margin-top: 2px;">${escHtml(d.telecaller.mobile)}</div>` : ''}
                </div>

                {{-- Assigned Account --}}
                <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 14px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px;">
                        <i class="fas fa-user-shield" style="color: #059669; margin-right: 4px;"></i> Assigned Account
                    </div>
                    <div class="lead-assignment-unassigned" style="font-size: 14px; font-weight: 800; color: ${hasAssignedAccount ? '#047857' : '#b45309'};">
                        ${escHtml(hasAssignedAccount ? d.assigned_to.name : 'Not assigned')}
                    </div>
                    ${hasAssignedAccount && d.assigned_to?.mobile ? `<div style="font-size: 12px; color: #64748b; margin-top: 2px;">${escHtml(d.assigned_to.mobile)}</div>` : ''}
                </div>

                {{-- Project Type --}}
                <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 14px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px;">
                        <i class="fas fa-shapes" style="color: #ef4444; margin-right: 4px;"></i> Project Type
                    </div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a;">
                        ${escHtml(p.project_type || d.product_type || '—')}
                    </div>
                </div>

                {{-- Company Name --}}
                <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 14px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px;">
                        <i class="far fa-building" style="color: #0284c7; margin-right: 4px;"></i> Company Name
                    </div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a;">
                        ${escHtml(p.cus_company_name || d.company_name || '—')}
                    </div>
                </div>

                {{-- Customer Email --}}
                <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 14px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px;">
                        <i class="far fa-envelope" style="color: #d97706; margin-right: 4px;"></i> Customer Mail
                    </div>
                    <div style="font-size: 14px; font-weight: 600; color: ${p.cus_mail ? '#2563eb' : '#94a3b8'};">
                        ${p.cus_mail ? `<a href="mailto:${escHtml(p.cus_mail)}" style="color:#2563eb;text-decoration:none;">${escHtml(p.cus_mail)}</a>` : '—'}
                    </div>
                </div>

                {{-- Price / Budget --}}
                <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 14px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px;">
                        <span style="font-weight: 800; color: #16a34a; margin-right: 4px;">₹</span> Price / Budget
                    </div>
                    <div style="font-size: 15px; font-weight: 800; color: ${p.price_raw || (p.price && p.price !== '—') ? '#15803d' : '#94a3b8'};">
                        ${p.price && p.price !== '—' ? escHtml(p.price) : (p.price_raw ? '₹ ' + escHtml(p.price_raw) : '—')}
                    </div>
                </div>

                {{-- GST & GST Number --}}
                <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 14px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px;">
                        <i class="far fa-file-invoice" style="color: #8b5cf6; margin-right: 4px;"></i> GST
                    </div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a;">
                        ${isGstYes ? `<span style="color:#15803d;"><i class="fas fa-check-circle" style="font-size:12px;"></i> Yes</span> ${p.gst_no ? `<span style="font-size:12px;color:#64748b;font-family:monospace;margin-left:6px;">(${escHtml(p.gst_no)})</span>` : ''}` : '<span style="color:#64748b;">No</span>'}
                    </div>
                </div>

                {{-- Assigned Date --}}
                <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 12px 14px;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em; margin-bottom: 4px;">
                        <i class="far fa-calendar-alt" style="color: #64748b; margin-right: 4px;"></i> Assigned Date
                    </div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a;">
                        ${escHtml(d.assign_date || '—')}
                    </div>
                </div>

            </div>

            {{-- Account note --}}
            ${hasAssignedAccount ? `<div class="lead-account-note">
                <div class="lead-account-note-title">
                    <i class="fas fa-sticky-note" style="color:#059669;margin-right:4px;"></i> Account Note
                </div>
                <div class="lead-account-note-text">${p.account_remarks ? escHtml(p.account_remarks) : 'No account note added.'}</div>
                ${d.account_status_details?.updated_by && d.account_status_details.updated_by !== '—' ? `
                    <div style="margin-top:6px;color:#64748b;font-size:11px;">
                        Last updated by <strong>${escHtml(d.account_status_details.updated_by)}</strong>
                        on ${escHtml(d.account_status_details.updated_at || '—')}
                    </div>
                ` : ''}
            </div>` : ''}

            {{-- 3. Appointment / Meeting (if scheduled) --}}
            ${appt && (appt.appointment_date || appt.location || appt.alternate_number) ? `
                <div style="background: #faf5ff; border: 1px solid #f3e8ff; border-radius: 12px; padding: 14px 18px; margin-bottom: 22px;">
                    <div style="font-size: 11.5px; font-weight: 800; color: #7c3aed; text-transform: uppercase; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                        <i class="far fa-calendar-check"></i> Appointment Schedule
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 18px; font-size: 13.5px; color: #0f172a; font-weight: 600;">
                        ${appt.appointment_date ? `<span><i class="far fa-calendar-alt" style="color:#7c3aed; margin-right:4px;"></i> ${escHtml(appt.appointment_date)} ${appt.appointment_time ? 'at ' + escHtml(appt.appointment_time) : ''}</span>` : ''}
                        ${appt.alternate_number ? `<span><i class="fas fa-phone-square" style="color:#059669; margin-right:4px;"></i> <a href="tel:${escHtml(appt.alternate_number)}" style="color:#0f172a;text-decoration:none;">${escHtml(appt.alternate_number)}</a></span>` : ''}
                        ${appt.location ? `<span><i class="fas fa-map-marker-alt" style="color:#ef4444; margin-right:4px;"></i> ${escHtml(appt.location)} <a href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(appt.location)}" target="_blank" style="color:#7c3aed;font-size:12px;margin-left:4px;text-decoration:underline;">(View Map)</a></span>` : ''}
                    </div>
                </div>
            ` : ''}

            </section>

            <aside class="lead-panel-section lead-activity-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                    <div style="font-size: 14px; font-weight: 800; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-history" style="color: #6366f1;"></i> Telecaller Remarks History
                    </div>
                    <span style="background: #f1f5f9; color: #64748b; font-size: 11.5px; font-weight: 700; padding: 2px 10px; border-radius: 12px;">
                        ${notes.length} ${notes.length === 1 ? 'Remark' : 'Remarks'}
                    </span>
                </div>

                ${notes.length === 0 ? `
                    <div style="text-align: center; padding: 18px; color: #94a3b8; font-size: 13px; background: #f8fafc; border-radius: 10px;">
                        No remarks submitted from mobile app yet.
                    </div>
                ` : `
                    <div class="lead-remarks-list">
                        ${notes.map(n => `
                            <div style="background: #f8fafc; border-radius: 10px; padding: 12px 14px; border: 1px solid #f1f5f9;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; font-size: 12px; flex-wrap: wrap; gap: 6px;">
                                    <span style="font-weight: 800; color: #1e293b;">
                                        ${escHtml(n.employee_name)}
                                        ${n.status ? `<span style="background:#e0e7ff;color:#4338ca;padding:1px 6px;border-radius:6px;font-size:10.5px;margin-left:6px;font-weight:700;">${escHtml(n.status)}</span>` : ''}
                                    </span>
                                    <span style="color: #94a3b8; font-size: 11px; font-weight: 600;">${escHtml(n.created_at || (n.notification_date + ' ' + (n.notification_time || '')))}</span>
                                </div>
                                <div style="font-size: 13px; color: #334155; line-height: 1.45;">${escHtml(n.remarks || 'No remarks text')}</div>
                                ${n.location ? `<div style="font-size: 11.5px; color: #64748b; margin-top: 4px;"><i class="fas fa-map-marker-alt" style="color: #ef4444; font-size: 10px;"></i> Location: ${escHtml(n.location)}</div>` : ''}
                                ${n.appointment_date ? `<div style="font-size: 11.5px; color: #7c3aed; margin-top: 3px; font-weight: 600;"><i class="far fa-calendar-alt" style="font-size: 10px;"></i> Appointment: ${escHtml(n.appointment_date)} ${escHtml(n.appointment_time || '')}</div>` : ''}
                            </div>
                        `).join('')}
                    </div>
                `}
            </aside>

          </div>
        </div>

    </div>
    `;

    document.getElementById('lead-details-content').innerHTML = html;
}

const UPDATE_LEAD_STATUS_ROUTE = "{{ route('dashboard.update-lead-status') }}";

function changeAccountLeadStatus(leadId, newStatus, selectElement, remarks = null) {
    if (!leadId || !newStatus) return;

    const oldStatus = selectElement ? (selectElement.getAttribute('data-current') || '') : '';
    if (selectElement) selectElement.disabled = true;

    const remarksInput = document.getElementById('modalRemarks-' + leadId);
    if (remarks === null && remarksInput) remarks = remarksInput.value.trim();
    const payload = { assign_base_id: leadId, account_status: newStatus };
    if (remarks !== null) payload.account_remarks = remarks;

    fetch(UPDATE_LEAD_STATUS_ROUTE, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (selectElement) selectElement.disabled = false;
        if (res.status) {
            showStatusToast('Status Updated', 'Lead #' + leadId + ' is now ' + newStatus, true);

            const slug = newStatus.toLowerCase().replace(/\s+/g, '-');

            // Sync account-status selects without changing the telecaller status.
            document.querySelectorAll(`select[data-lead-id="${leadId}"]`).forEach(el => {
                el.className = 'account-status-select status-' + slug;
                el.setAttribute('data-current', newStatus);
                el.value = newStatus;
            });

            // Update badges if present
            const modalStatus = document.getElementById('modal-status-badge-' + leadId);
            if (modalStatus) {
                modalStatus.className = (modalStatus.tagName === 'SELECT' ? 'account-status-select' : 'status-badge') + ' status-' + slug;
                if (modalStatus.tagName === 'SELECT') {
                    modalStatus.value = newStatus;
                    modalStatus.setAttribute('data-current', newStatus);
                } else {
                    modalStatus.textContent = newStatus;
                }
            }
            const tableBadge = document.getElementById('account-table-status-' + leadId);
            if (tableBadge) {
                tableBadge.className = 'status-badge status-' + slug;
                tableBadge.textContent = newStatus;
            }
        } else {
            throw new Error(res.message || 'Failed to update status');
        }
    })
    .catch(err => {
        if (selectElement) {
            selectElement.disabled = false;
            if (oldStatus) selectElement.value = oldStatus;
        }
        showStatusToast('Update Failed', err.message || err, false);
    });
}

function showStatusToast(title, message, isSuccess = true) {
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const toast = document.createElement('div');
    toast.style.cssText = `
        background: ${isSuccess ? 'linear-gradient(135deg,#059669,#047857)' : 'linear-gradient(135deg,#ef4444,#dc2626)'};
        color: #fff;
        padding: 12px 18px;
        border-radius: 14px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.18);
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13.5px;
        font-weight: 600;
        animation: cardIn 0.3s ease;
        min-width: 280px;
        z-index: 100000;
    `;
    toast.innerHTML = `
        <i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'}" style="font-size: 20px;"></i>
        <div>
            <div style="font-weight: 800; font-size: 13.5px;">${escHtml(title)}</div>
            <div style="font-size: 12px; opacity: 0.92; margin-top: 1px;">${escHtml(message)}</div>
        </div>
    `;
    container.appendChild(toast);
    setTimeout(() => {
        toast.style.transition = 'opacity 0.3s, transform 0.3s';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-6px)';
        setTimeout(() => toast.remove(), 300);
    }, 3200);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (typeof closeNotifPanel === 'function') closeNotifPanel();
        if (typeof closeWaPanel === 'function') closeWaPanel();
        if (typeof closeLeadDetailsPanel === 'function') closeLeadDetailsPanel();
    }
});
</script>

@endsection