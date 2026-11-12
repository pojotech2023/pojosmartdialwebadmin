@extends('layouts.app')

@section('page-title', 'Settings')

@section('content')
<div class="settings-page-container">

    {{-- Page Header --}}
    <div class="settings-header">
        <h1 class="settings-title">Settings</h1>
        <p class="settings-subtitle">Manage product types and telecaller lead statuses used across the app.</p>

        {{-- Underline Tabs (Like PojoInfra360) --}}
        <div class="settings-tabs-underline">
            <button type="button" 
                    id="btnTabProducts" 
                    class="tab-link {{ $activeTab === 'products' ? 'active' : '' }}" 
                    onclick="switchSettingsTab('products')">
                <i class="fas fa-boxes-stacked"></i> Product Types
            </button>
            <button type="button" 
                    id="btnTabStatuses" 
                    class="tab-link {{ $activeTab === 'statuses' ? 'active' : '' }}" 
                    onclick="switchSettingsTab('statuses')">
                <i class="fas fa-headset"></i> Telecaller Status
            </button>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert-success-banner">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="alert-error-banner">
            <i class="fas fa-triangle-exclamation"></i>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 1: PRODUCT TYPES --}}
    {{-- ========================================================================= --}}
    <div id="tabProductsView" style="{{ $activeTab === 'products' ? '' : 'display:none;' }}">
        <div class="settings-card">
            {{-- Card Header: Title + Add Button --}}
            <div class="settings-card-header">
                <h2 class="card-heading">Product List</h2>
                <div class="card-header-actions">
                    <input type="text" 
                           class="search-mini-input" 
                           placeholder="Search products..." 
                           oninput="filterTable(this.value, 'productsTable')">
                    <button type="button" class="btn-blue-primary" onclick="openAddProductModal()">
                        <i class="fas fa-plus"></i> Add Product
                    </button>
                </div>
            </div>

            {{-- Table: S.NO | PRODUCT NAME | ACTION --}}
            <div class="settings-table-wrapper">
                <table class="settings-infra-table" id="productsTable">
                    <thead>
                        <tr>
                            <th style="width: 100px;">S.NO</th>
                            <th>PRODUCT NAME</th>
                            <th style="width: 120px; text-align: right;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $idx => $product)
                        <tr class="table-row-item" data-search="{{ strtolower($product->name) }}">
                            <td class="col-sno">{{ $idx + 1 }}</td>
                            <td class="col-name">{{ $product->name }}</td>
                            <td class="col-action" style="text-align: right;">
                                <div class="action-icons-wrap">
                                    <button type="button" 
                                            class="icon-action-btn btn-edit-blue" 
                                            onclick="openEditProductModal({{ $product->id }}, '{{ addslashes($product->name) }}')"
                                            title="Edit Product">
                                        <i class="far fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('settings.products.destroy', $product->id) }}" onsubmit="return confirm('Are you sure you want to delete product \'{{ addslashes($product->name) }}\'?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-action-btn btn-delete-red" title="Delete Product">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="table-empty">
                                No products found. Click "+ Add Product" to create one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 2: TELECALLER STATUS --}}
    {{-- ========================================================================= --}}
    <div id="tabStatusesView" style="{{ $activeTab === 'statuses' ? '' : 'display:none;' }}">
        <div class="settings-card">
            {{-- Card Header: Title + Add Button --}}
            <div class="settings-card-header">
                <h2 class="card-heading">Status List</h2>
                <div class="card-header-actions">
                    <input type="text" 
                           class="search-mini-input" 
                           placeholder="Search statuses..." 
                           oninput="filterTable(this.value, 'statusesTable')">
                    <button type="button" class="btn-blue-primary" onclick="openAddStatusModal()">
                        <i class="fas fa-plus"></i> Add Status
                    </button>
                </div>
            </div>

            {{-- Table: S.NO | STATUS NAME | ACTION --}}
            <div class="settings-table-wrapper">
                <table class="settings-infra-table" id="statusesTable">
                    <thead>
                        <tr>
                            <th style="width: 100px;">S.NO</th>
                            <th>STATUS NAME</th>
                            <th style="width: 120px; text-align: right;">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statuses as $idx => $st)
                        <tr class="table-row-item" data-search="{{ strtolower($st->name) }}">
                            <td class="col-sno">{{ $idx + 1 }}</td>
                            <td class="col-name">{{ $st->name }}</td>
                            <td class="col-action" style="text-align: right;">
                                <div class="action-icons-wrap">
                                    <button type="button" 
                                            class="icon-action-btn btn-edit-blue" 
                                            onclick="openEditStatusModal({{ $st->id }}, '{{ addslashes($st->name) }}')"
                                            title="Edit Status">
                                        <i class="far fa-edit"></i>
                                    </button>
                                    <form method="POST" action="{{ route('settings.statuses.destroy', $st->id) }}" onsubmit="return confirm('Are you sure you want to delete status \'{{ addslashes($st->name) }}\'?');" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="icon-action-btn btn-delete-red" title="Delete Status">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="table-empty">
                                No statuses found. Click "+ Add Status" to create one.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ========================================================================= --}}
{{-- MODALS: ADD / EDIT PRODUCT --}}
{{-- ========================================================================= --}}

{{-- Add Product Modal --}}
<div id="addProductModal" class="simple-modal-backdrop" onclick="if(event.target.id === 'addProductModal') closeAddProductModal()">
    <div class="simple-modal-box">
        <div class="simple-modal-header">
            <h3 class="simple-modal-title">Add Product</h3>
            <button type="button" class="simple-close-btn" onclick="closeAddProductModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('settings.products.store') }}">
            @csrf
            <input type="hidden" name="is_active" value="1">
            <div class="simple-modal-body">
                <label class="simple-field-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" 
                       name="name" 
                       class="simple-field-input" 
                       placeholder="Enter product name" 
                       required 
                       autofocus>
            </div>
            <div class="simple-modal-footer">
                <button type="button" class="btn-cancel-flat" onclick="closeAddProductModal()">Cancel</button>
                <button type="submit" class="btn-blue-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Product Modal --}}
<div id="editProductModal" class="simple-modal-backdrop" onclick="if(event.target.id === 'editProductModal') closeEditProductModal()">
    <div class="simple-modal-box">
        <div class="simple-modal-header">
            <h3 class="simple-modal-title">Edit Product</h3>
            <button type="button" class="simple-close-btn" onclick="closeEditProductModal()">&times;</button>
        </div>
        <form id="editProductForm" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" id="editProductIsActive" name="is_active" value="1">
            <div class="simple-modal-body">
                <label class="simple-field-label">Product Name <span class="text-danger">*</span></label>
                <input type="text" 
                       id="editProductName" 
                       name="name" 
                       class="simple-field-input" 
                       placeholder="Enter product name" 
                       required 
                       autofocus>
            </div>
            <div class="simple-modal-footer">
                <button type="button" class="btn-cancel-flat" onclick="closeEditProductModal()">Cancel</button>
                <button type="submit" class="btn-blue-primary">Update Product</button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODALS: ADD / EDIT TELECALLER STATUS --}}
{{-- ========================================================================= --}}

{{-- Add Status Modal --}}
<div id="addStatusModal" class="simple-modal-backdrop" onclick="if(event.target.id === 'addStatusModal') closeAddStatusModal()">
    <div class="simple-modal-box">
        <div class="simple-modal-header">
            <h3 class="simple-modal-title">Add Status</h3>
            <button type="button" class="simple-close-btn" onclick="closeAddStatusModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('settings.statuses.store') }}">
            @csrf
            <input type="hidden" name="color" value="#2563eb">
            <input type="hidden" name="is_active" value="1">
            <div class="simple-modal-body">
                <label class="simple-field-label">Status Name <span class="text-danger">*</span></label>
                <input type="text" 
                       id="addStatusNameInput" 
                       name="name" 
                       class="simple-field-input" 
                       placeholder="Enter status name" 
                       required 
                       autofocus>
            </div>
            <div class="simple-modal-footer">
                <button type="button" class="btn-cancel-flat" onclick="closeAddStatusModal()">Cancel</button>
                <button type="submit" class="btn-blue-primary">Save Status</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Status Modal --}}
<div id="editStatusModal" class="simple-modal-backdrop" onclick="if(event.target.id === 'editStatusModal') closeEditStatusModal()">
    <div class="simple-modal-box">
        <div class="simple-modal-header">
            <h3 class="simple-modal-title">Edit Status</h3>
            <button type="button" class="simple-close-btn" onclick="closeEditStatusModal()">&times;</button>
        </div>
        <form id="editStatusForm" method="POST" action="">
            @csrf
            @method('PUT')
            <input type="hidden" id="editStatusColorPicker" name="color" value="#2563eb">
            <input type="hidden" id="editStatusIsActive" name="is_active" value="1">
            <div class="simple-modal-body">
                <label class="simple-field-label">Status Name <span class="text-danger">*</span></label>
                <input type="text" 
                       id="editStatusNameInput" 
                       name="name" 
                       class="simple-field-input" 
                       placeholder="Enter status name" 
                       required 
                       autofocus>
            </div>
            <div class="simple-modal-footer">
                <button type="button" class="btn-cancel-flat" onclick="closeEditStatusModal()">Cancel</button>
                <button type="submit" class="btn-blue-primary">Update Status</button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- STYLES: CLEAN, MINIMAL TAB & TABLE DESIGN --}}
{{-- ========================================================================= --}}
<style>
.settings-page-container {
    width: 100%;
    padding: 10px 10px 40px;
}

/* Page Header */
.settings-header {
    margin-bottom: 20px;
}
.settings-title {
    font-size: 26px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 6px 0;
    letter-spacing: -0.01em;
}
.settings-subtitle {
    font-size: 14px;
    color: #64748b;
    margin: 0 0 20px 0;
}

/* Underline Tab Navigation (PojoInfra360 style) */
.settings-tabs-underline {
    display: flex;
    gap: 30px;
    border-bottom: 1px solid #e2e8f0;
    margin-top: 16px;
}
.tab-link {
    background: transparent;
    border: none;
    padding: 10px 4px 14px 4px;
    font-size: 14px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    margin-bottom: -1px;
}
.tab-link:hover {
    color: #2563eb;
}
.tab-link.active {
    color: #2563eb;
    border-bottom: 2px solid #2563eb;
    font-weight: 700;
}

/* White Card Box */
.settings-card {
    background: #ffffff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
    overflow: hidden;
}
.settings-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 24px;
    border-bottom: 1px solid #f1f5f9;
    flex-wrap: wrap;
    gap: 14px;
}
.card-heading {
    font-size: 18px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.card-header-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Search input */
.search-mini-input {
    padding: 8px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
    color: #1e293b;
    outline: none;
    width: 220px;
    transition: border-color 0.2s ease;
}
.search-mini-input:focus {
    border-color: #2563eb;
}

/* Blue Primary Button */
.btn-blue-primary {
    background: #1d4ed8;
    color: #ffffff;
    border: none;
    padding: 8px 18px;
    border-radius: 6px;
    font-size: 13.5px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.18s ease;
}
.btn-blue-primary:hover {
    background: #1e40af;
}

/* Table Styling */
.settings-table-wrapper {
    overflow-x: auto;
}
.settings-infra-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}
.settings-infra-table thead tr {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
}
.settings-infra-table th {
    padding: 16px 24px;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.settings-infra-table tbody tr {
    border-bottom: 1px solid #f1f5f9;
    transition: background 0.15s ease;
}
.settings-infra-table tbody tr:nth-child(even) {
    background: #ffffff;
}
.settings-infra-table tbody tr:nth-child(odd) {
    background: #f8fafc;
}
.settings-infra-table tbody tr:hover {
    background: #f1f5f9;
}
.settings-infra-table td {
    padding: 16px 24px;
    font-size: 14px;
    color: #1e293b;
}

.col-sno {
    color: #475569;
    font-weight: 500;
}
.col-name {
    color: #0f172a;
    font-weight: 500;
}

/* Action Icons */
.action-icons-wrap {
    display: inline-flex;
    align-items: center;
    gap: 16px;
}
.icon-action-btn {
    background: none;
    border: none;
    padding: 4px;
    cursor: pointer;
    font-size: 15px;
    line-height: 1;
    transition: transform 0.15s ease, opacity 0.15s ease;
}
.icon-action-btn:hover {
    transform: scale(1.15);
}
.btn-edit-blue {
    color: #2563eb;
}
.btn-delete-red {
    color: #ef4444;
}

.table-empty {
    text-align: center;
    padding: 40px 20px;
    color: #94a3b8;
    font-size: 14px;
}

/* Banner Alerts */
.alert-success-banner, .alert-error-banner {
    padding: 12px 18px;
    border-radius: 8px;
    margin-bottom: 16px;
    font-size: 13.5px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.alert-success-banner {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.alert-error-banner {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* Simple Modals */
.simple-modal-backdrop {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(2px);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.simple-modal-box {
    background: #ffffff;
    border-radius: 12px;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    overflow: hidden;
    animation: modalPop 0.2s ease-out;
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
.simple-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
}
.simple-modal-title {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.simple-close-btn {
    background: none;
    border: none;
    font-size: 22px;
    color: #94a3b8;
    cursor: pointer;
    line-height: 1;
}
.simple-close-btn:hover {
    color: #0f172a;
}
.simple-modal-body {
    padding: 20px;
}
.simple-field-label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 6px;
}
.simple-field-input {
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 14px;
    color: #0f172a;
    outline: none;
    box-sizing: border-box;
}
.simple-field-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}
.simple-modal-footer {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 10px;
    padding: 14px 20px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
}
.btn-cancel-flat {
    background: transparent;
    border: 1px solid #cbd5e1;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #64748b;
    cursor: pointer;
}
.btn-cancel-flat:hover {
    background: #f1f5f9;
    color: #1e293b;
}
.text-danger {
    color: #ef4444;
}
</style>

{{-- ========================================================================= --}}
{{-- JAVASCRIPT: CLEAN INTERACTIONS --}}
{{-- ========================================================================= --}}
<script>
// Switch Tabs
function switchSettingsTab(tab) {
    const btnProducts = document.getElementById('btnTabProducts');
    const btnStatuses = document.getElementById('btnTabStatuses');
    const viewProducts = document.getElementById('tabProductsView');
    const viewStatuses = document.getElementById('tabStatusesView');

    btnProducts.classList.remove('active');
    btnStatuses.classList.remove('active');
    viewProducts.style.display = 'none';
    viewStatuses.style.display = 'none';

    if (tab === 'products') {
        btnProducts.classList.add('active');
        viewProducts.style.display = 'block';
        history.replaceState(null, '', '?tab=products');
    } else {
        btnStatuses.classList.add('active');
        viewStatuses.style.display = 'block';
        history.replaceState(null, '', '?tab=statuses');
    }
}

// Live Search Filter for Tables
function filterTable(query, tableId) {
    const q = query.toLowerCase().trim();
    const rows = document.querySelectorAll('#' + tableId + ' .table-row-item');
    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        row.style.display = text.includes(q) ? '' : 'none';
    });
}

// Product Modals
function openAddProductModal() {
    document.getElementById('addProductModal').style.display = 'flex';
}
function closeAddProductModal() {
    document.getElementById('addProductModal').style.display = 'none';
}
function openEditProductModal(id, name) {
    document.getElementById('editProductForm').action = '/settings/products/' + id;
    document.getElementById('editProductName').value = name;
    document.getElementById('editProductModal').style.display = 'flex';
}
function closeEditProductModal() {
    document.getElementById('editProductModal').style.display = 'none';
}

// Status Modals
function openAddStatusModal() {
    document.getElementById('addStatusModal').style.display = 'flex';
}
function closeAddStatusModal() {
    document.getElementById('addStatusModal').style.display = 'none';
}
function openEditStatusModal(id, name) {
    document.getElementById('editStatusForm').action = '/settings/statuses/' + id;
    document.getElementById('editStatusNameInput').value = name;
    document.getElementById('editStatusModal').style.display = 'flex';
}
function closeEditStatusModal() {
    document.getElementById('editStatusModal').style.display = 'none';
}
</script>
@endsection
