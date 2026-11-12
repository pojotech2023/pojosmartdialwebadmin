@extends('layouts.app')

@section('page-title', 'Settings')

@section('content')
<div class="settings-fullscreen-wrapper">

    {{-- ========================================================================= --}}
    {{-- HERO HEADER (FULL WIDTH) --}}
    {{-- ========================================================================= --}}
    <div class="settings-hero-card">
        <div class="settings-hero-left">
            <div class="settings-hero-icon">
                <i class="fas fa-sliders-h"></i>
            </div>
            <div>
                <h1 class="settings-hero-title">Settings & Configurations</h1>
                <p class="settings-hero-subtitle">
                    Configure Product Types catalog and Telecaller lead statuses for both the mobile application and web portal.
                </p>
            </div>
        </div>

        {{-- Switcher Tabs --}}
        <div class="settings-pill-nav">
            <button type="button" 
                    id="btnTabProducts" 
                    class="settings-nav-pill {{ $activeTab === 'products' ? 'active' : '' }}" 
                    onclick="switchSettingsTab('products')">
                <i class="fas fa-boxes-stacked"></i>
                <span>Product Types</span>
                <span class="pill-badge" id="badgeCountProducts">{{ $products->count() }}</span>
            </button>
            <button type="button" 
                    id="btnTabStatuses" 
                    class="settings-nav-pill {{ $activeTab === 'statuses' ? 'active' : '' }}" 
                    onclick="switchSettingsTab('statuses')">
                <i class="fas fa-headset"></i>
                <span>Telecaller Status</span>
                <span class="pill-badge" id="badgeCountStatuses">{{ $statuses->count() }}</span>
            </button>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="settings-alert-success">
            <i class="fas fa-circle-check"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="settings-alert-error">
            <div style="display:flex;align-items:center;gap:8px;font-weight:700;margin-bottom:6px;">
                <i class="fas fa-triangle-exclamation"></i>
                <span>Please fix the following issues:</span>
            </div>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 1: PRODUCT TYPES --}}
    {{-- ========================================================================= --}}
    <div id="tabProductsView" style="{{ $activeTab === 'products' ? '' : 'display:none;' }}">

        {{-- Toolbar / Action Card --}}
        <div class="settings-toolbar-card">
            <div class="toolbar-search-box">
                <i class="fas fa-search"></i>
                <input type="text" 
                       id="searchProductsInput" 
                       placeholder="Search product name..." 
                       oninput="filterProductsTable(this.value)">
            </div>
            <div class="toolbar-actions">
                <button type="button" class="btn-primary-gradient" onclick="openAddProductModal()">
                    <i class="fas fa-plus-circle"></i> Add New Product
                </button>
            </div>
        </div>

        {{-- Minimal Products Table (Product Name & Action Only) --}}
        <div class="settings-table-card">
            <table class="settings-custom-table" id="productsTable">
                <thead>
                    <tr>
                        <th style="padding-left: 28px;">Product Name</th>
                        <th style="text-align: right; padding-right: 28px; width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr class="product-row" data-search="{{ strtolower($product->name) }}">
                        <td style="padding-left: 28px;">
                            <div class="entity-name-cell">
                                <div class="entity-avatar-icon">
                                    <i class="fas fa-cube"></i>
                                </div>
                                <div class="entity-primary-title" style="font-size: 15px; font-weight: 700; color: #1e293b;">
                                    {{ $product->name }}
                                </div>
                            </div>
                        </td>
                        <td style="text-align: right; padding-right: 28px;">
                            <div class="row-actions-group" style="justify-content: flex-end;">
                                <button type="button" 
                                        class="action-btn-edit" 
                                        onclick="openEditProductModal({{ $product->id }}, '{{ addslashes($product->name) }}', '{{ addslashes($product->description ?? '') }}', {{ $product->is_active ? 1 : 0 }})"
                                        title="Edit Product">
                                    <i class="fas fa-pencil"></i>
                                </button>

                                <form method="POST" action="{{ route('settings.products.destroy', $product->id) }}" onsubmit="return confirm('Are you sure you want to delete product \'{{ addslashes($product->name) }}\'?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn-delete" title="Delete Product">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="table-empty-cell">
                            <i class="fas fa-box-open"></i>
                            <div class="empty-title">No Product Types Found</div>
                            <div class="empty-desc">Create your first product type to start assigning leads.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 2: TELECALLER STATUS --}}
    {{-- ========================================================================= --}}
    <div id="tabStatusesView" style="{{ $activeTab === 'statuses' ? '' : 'display:none;' }}">

        {{-- Toolbar / Action Card --}}
        <div class="settings-toolbar-card">
            <div class="toolbar-search-box">
                <i class="fas fa-search"></i>
                <input type="text" 
                       id="searchStatusesInput" 
                       placeholder="Search status name..." 
                       oninput="filterStatusesTable(this.value)">
            </div>
            <div class="toolbar-actions">
                <button type="button" class="btn-primary-gradient" onclick="openAddStatusModal()">
                    <i class="fas fa-plus-circle"></i> Add New Status
                </button>
            </div>
        </div>

        {{-- Minimal Statuses Table (Status Name & Action Only) --}}
        <div class="settings-table-card">
            <table class="settings-custom-table" id="statusesTable">
                <thead>
                    <tr>
                        <th style="padding-left: 28px;">Status Name</th>
                        <th style="text-align: right; padding-right: 28px; width: 140px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($statuses as $st)
                    <tr class="status-row" data-search="{{ strtolower($st->name) }}">
                        <td style="padding-left: 28px;">
                            <div style="display:flex;align-items:center;gap:12px;">
                                <div class="status-color-circle" style="background: {{ $st->color ?: '#6366f1' }}; width: 12px; height: 12px; border-radius: 50%;"></div>
                                <span class="status-preview-pill" style="border-color: {{ $st->color ?: '#6366f1' }}35; background: {{ $st->color ?: '#6366f1' }}15; color: {{ $st->color ?: '#334155' }}; font-weight: 700; font-size: 13.5px; padding: 6px 16px; border-radius: 999px;">
                                    <i class="fas fa-tag" style="font-size:11px;margin-right:6px;"></i> {{ $st->name }}
                                </span>
                            </div>
                        </td>
                        <td style="text-align: right; padding-right: 28px;">
                            <div class="row-actions-group" style="justify-content: flex-end;">
                                <button type="button" 
                                        class="action-btn-edit" 
                                        onclick="openEditStatusModal({{ $st->id }}, '{{ addslashes($st->name) }}', '{{ $st->color }}', '{{ addslashes($st->description ?? '') }}', {{ $st->requires_appointment ? 1 : 0 }}, {{ $st->requires_location ? 1 : 0 }}, {{ $st->is_active ? 1 : 0 }}, {{ $st->sort_order }})"
                                        title="Edit Status">
                                    <i class="fas fa-pencil"></i>
                                </button>

                                <form method="POST" action="{{ route('settings.statuses.destroy', $st->id) }}" onsubmit="return confirm('Are you sure you want to delete status \'{{ addslashes($st->name) }}\'?');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn-delete" title="Delete Status">
                                        <i class="fas fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="table-empty-cell">
                            <i class="fas fa-tags"></i>
                            <div class="empty-title">No Telecaller Statuses Found</div>
                            <div class="empty-desc">Add statuses like Quotation Shared, Appointment-Online, etc.</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

{{-- ========================================================================= --}}
{{-- MODALS: PRODUCT TYPES --}}
{{-- ========================================================================= --}}

{{-- Add Product Modal --}}
<div id="addProductModal" class="fullscreen-modal-backdrop" onclick="if(event.target.id === 'addProductModal') closeAddProductModal()">
    <div class="fullscreen-modal-window">
        <div class="modal-window-header">
            <div class="modal-header-info">
                <div class="modal-icon-badge"><i class="fas fa-cube"></i></div>
                <div>
                    <h3 class="modal-header-title">Add New Product Type</h3>
                    <p class="modal-header-sub">Create a new product catalog item for lead assignment</p>
                </div>
            </div>
            <button type="button" class="modal-close-icon" onclick="closeAddProductModal()">&times;</button>
        </div>

        <form method="POST" action="{{ route('settings.products.store') }}" class="modal-body-form">
            @csrf
            <div class="form-group-block">
                <label class="modal-form-label">Product Name <span class="required-asterisk">*</span></label>
                <input type="text" name="name" class="modal-input-field" placeholder="e.g. Website Design, Ecommerce, CRM Software..." required>
            </div>

            <div class="form-group-block">
                <label class="modal-form-label">Description (Optional)</label>
                <textarea name="description" rows="3" class="modal-input-field" placeholder="Brief summary of this product package or service..."></textarea>
            </div>

            <div class="modal-toggle-box">
                <label class="toggle-control-label">
                    <input type="checkbox" name="is_active" value="1" checked class="custom-toggle-input">
                    <div class="toggle-text-group">
                        <span class="toggle-title"><i class="fas fa-circle-check" style="color:#10b981;"></i> Active in Mobile App</span>
                        <span class="toggle-help">When enabled, telecallers can select this product when logging calls and leads.</span>
                    </div>
                </label>
            </div>

            <div class="modal-footer-buttons">
                <button type="button" class="btn-secondary-pill" onclick="closeAddProductModal()">Cancel</button>
                <button type="submit" class="btn-primary-gradient"><i class="fas fa-check"></i> Save Product</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Product Modal --}}
<div id="editProductModal" class="fullscreen-modal-backdrop" onclick="if(event.target.id === 'editProductModal') closeEditProductModal()">
    <div class="fullscreen-modal-window">
        <div class="modal-window-header">
            <div class="modal-header-info">
                <div class="modal-icon-badge"><i class="fas fa-pencil"></i></div>
                <div>
                    <h3 class="modal-header-title">Edit Product Type</h3>
                    <p class="modal-header-sub">Update product name, description, or mobile visibility</p>
                </div>
            </div>
            <button type="button" class="modal-close-icon" onclick="closeEditProductModal()">&times;</button>
        </div>

        <form id="editProductForm" method="POST" action="" class="modal-body-form">
            @csrf
            @method('PUT')
            <div class="form-group-block">
                <label class="modal-form-label">Product Name <span class="required-asterisk">*</span></label>
                <input type="text" id="editProductName" name="name" class="modal-input-field" required>
            </div>

            <div class="form-group-block">
                <label class="modal-form-label">Description</label>
                <textarea id="editProductDesc" name="description" rows="3" class="modal-input-field"></textarea>
            </div>

            <div class="modal-toggle-box">
                <label class="toggle-control-label">
                    <input type="checkbox" id="editProductIsActive" name="is_active" value="1" class="custom-toggle-input">
                    <div class="toggle-text-group">
                        <span class="toggle-title"><i class="fas fa-circle-check" style="color:#10b981;"></i> Active in Mobile App</span>
                        <span class="toggle-help">Toggle visibility for telecallers on the mobile application.</span>
                    </div>
                </label>
            </div>

            <div class="modal-footer-buttons">
                <button type="button" class="btn-secondary-pill" onclick="closeEditProductModal()">Cancel</button>
                <button type="submit" class="btn-primary-gradient"><i class="fas fa-floppy-disk"></i> Update Product</button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODALS: TELECALLER STATUS --}}
{{-- ========================================================================= --}}

{{-- Add Status Modal --}}
<div id="addStatusModal" class="fullscreen-modal-backdrop" onclick="if(event.target.id === 'addStatusModal') closeAddStatusModal()">
    <div class="fullscreen-modal-window">
        <div class="modal-window-header">
            <div class="modal-header-info">
                <div class="modal-icon-badge" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);"><i class="fas fa-tag"></i></div>
                <div>
                    <h3 class="modal-header-title">Add Telecaller Status</h3>
                    <p class="modal-header-sub">Configure call outcome options for telecallers</p>
                </div>
            </div>
            <button type="button" class="modal-close-icon" onclick="closeAddStatusModal()">&times;</button>
        </div>

        <form method="POST" action="{{ route('settings.statuses.store') }}" class="modal-body-form">
            @csrf

            {{-- Live Badge Preview --}}
            <div class="live-preview-box">
                <span class="live-preview-label">Live App Badge Preview:</span>
                <span id="addBadgePreview" class="status-preview-pill" style="border-color:#2563eb35;background:#2563eb12;color:#2563eb;font-size:13px;padding:5px 14px;">
                    <i class="fas fa-tag" style="font-size:11px;margin-right:5px;"></i> Quotation Shared
                </span>
            </div>

            <div class="form-group-block">
                <label class="modal-form-label">Status Name <span class="required-asterisk">*</span></label>
                <input type="text" 
                       id="addStatusNameInput" 
                       name="name" 
                       class="modal-input-field" 
                       placeholder="e.g. Quotation Shared, Appointment-Online, Interested..." 
                       oninput="updateAddBadgePreview()"
                       required>
            </div>

            <div class="modal-grid-two">
                <div class="form-group-block">
                    <label class="modal-form-label">Badge Color</label>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <input type="color" 
                               id="addStatusColorPicker" 
                               name="color" 
                               value="#2563eb" 
                               class="color-picker-input" 
                               oninput="document.getElementById('addStatusColorText').value = this.value; updateAddBadgePreview();">
                        <input type="text" 
                               id="addStatusColorText" 
                               class="modal-input-field" 
                               value="#2563eb" 
                               oninput="document.getElementById('addStatusColorPicker').value = this.value; updateAddBadgePreview();">
                    </div>
                </div>

                <div class="form-group-block">
                    <label class="modal-form-label">Display Order</label>
                    <input type="number" name="sort_order" class="modal-input-field" value="0" min="0">
                </div>
            </div>

            {{-- Preset Palette --}}
            <div class="preset-colors-row">
                <span style="font-size:11.5px;font-weight:700;color:#64748b;">Presets:</span>
                <button type="button" class="preset-dot" style="background:#2563eb;" onclick="setAddColor('#2563eb')" title="Blue"></button>
                <button type="button" class="preset-dot" style="background:#7c3aed;" onclick="setAddColor('#7c3aed')" title="Purple"></button>
                <button type="button" class="preset-dot" style="background:#059669;" onclick="setAddColor('#059669')" title="Emerald"></button>
                <button type="button" class="preset-dot" style="background:#d97706;" onclick="setAddColor('#d97706')" title="Amber"></button>
                <button type="button" class="preset-dot" style="background:#dc2626;" onclick="setAddColor('#dc2626')" title="Red"></button>
                <button type="button" class="preset-dot" style="background:#0891b2;" onclick="setAddColor('#0891b2')" title="Cyan"></button>
                <button type="button" class="preset-dot" style="background:#db2777;" onclick="setAddColor('#db2777')" title="Pink"></button>
            </div>

            <div class="form-group-block">
                <label class="modal-form-label">Description (Optional)</label>
                <textarea name="description" rows="2" class="modal-input-field" placeholder="Brief explanation of when this status applies..."></textarea>
            </div>

            {{-- Toggles --}}
            <div class="modal-toggle-box-stacked">
                <label class="toggle-control-label">
                    <input type="checkbox" name="requires_appointment" value="1" class="custom-toggle-input">
                    <div class="toggle-text-group">
                        <span class="toggle-title"><i class="fas fa-calendar-day" style="color:#d97706;"></i> Requires Appointment (Date & Time)</span>
                        <span class="toggle-help">Mobile app prompts telecaller for scheduled date & time (e.g. Appointment-Online/Offline).</span>
                    </div>
                </label>

                <div class="toggle-divider-line"></div>

                <label class="toggle-control-label">
                    <input type="checkbox" name="requires_location" value="1" class="custom-toggle-input">
                    <div class="toggle-text-group">
                        <span class="toggle-title"><i class="fas fa-map-location-dot" style="color:#6366f1;"></i> Requires Location / Meeting URL</span>
                        <span class="toggle-help">Mobile app prompts for in-person address or Google Meet / Zoom link.</span>
                    </div>
                </label>

                <div class="toggle-divider-line"></div>

                <label class="toggle-control-label">
                    <input type="checkbox" name="is_active" value="1" checked class="custom-toggle-input">
                    <div class="toggle-text-group">
                        <span class="toggle-title"><i class="fas fa-circle-check" style="color:#10b981;"></i> Active in Mobile App Remarks Dropdown</span>
                        <span class="toggle-help">Available for immediate selection by all telecallers.</span>
                    </div>
                </label>
            </div>

            <div class="modal-footer-buttons">
                <button type="button" class="btn-secondary-pill" onclick="closeAddStatusModal()">Cancel</button>
                <button type="submit" class="btn-primary-gradient"><i class="fas fa-check"></i> Save Status</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Status Modal --}}
<div id="editStatusModal" class="fullscreen-modal-backdrop" onclick="if(event.target.id === 'editStatusModal') closeEditStatusModal()">
    <div class="fullscreen-modal-window">
        <div class="modal-window-header">
            <div class="modal-header-info">
                <div class="modal-icon-badge" style="background:linear-gradient(135deg,#6366f1,#4f46e5);"><i class="fas fa-pencil"></i></div>
                <div>
                    <h3 class="modal-header-title">Edit Telecaller Status</h3>
                    <p class="modal-header-sub">Update status label, color, rules, and visibility</p>
                </div>
            </div>
            <button type="button" class="modal-close-icon" onclick="closeEditStatusModal()">&times;</button>
        </div>

        <form id="editStatusForm" method="POST" action="" class="modal-body-form">
            @csrf
            @method('PUT')

            {{-- Live Badge Preview --}}
            <div class="live-preview-box">
                <span class="live-preview-label">Live App Badge Preview:</span>
                <span id="editBadgePreview" class="status-preview-pill" style="font-size:13px;padding:5px 14px;">
                    <i class="fas fa-tag" style="font-size:11px;margin-right:5px;"></i> Status Name
                </span>
            </div>

            <div class="form-group-block">
                <label class="modal-form-label">Status Name <span class="required-asterisk">*</span></label>
                <input type="text" 
                       id="editStatusNameInput" 
                       name="name" 
                       class="modal-input-field" 
                       oninput="updateEditBadgePreview()"
                       required>
            </div>

            <div class="modal-grid-two">
                <div class="form-group-block">
                    <label class="modal-form-label">Badge Color</label>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <input type="color" 
                               id="editStatusColorPicker" 
                               name="color" 
                               class="color-picker-input" 
                               oninput="document.getElementById('editStatusColorText').value = this.value; updateEditBadgePreview();">
                        <input type="text" 
                               id="editStatusColorText" 
                               class="modal-input-field" 
                               oninput="document.getElementById('editStatusColorPicker').value = this.value; updateEditBadgePreview();">
                    </div>
                </div>

                <div class="form-group-block">
                    <label class="modal-form-label">Display Order</label>
                    <input type="number" id="editStatusSortOrder" name="sort_order" class="modal-input-field" min="0">
                </div>
            </div>

            {{-- Preset Palette --}}
            <div class="preset-colors-row">
                <span style="font-size:11.5px;font-weight:700;color:#64748b;">Presets:</span>
                <button type="button" class="preset-dot" style="background:#2563eb;" onclick="setEditColor('#2563eb')" title="Blue"></button>
                <button type="button" class="preset-dot" style="background:#7c3aed;" onclick="setEditColor('#7c3aed')" title="Purple"></button>
                <button type="button" class="preset-dot" style="background:#059669;" onclick="setEditColor('#059669')" title="Emerald"></button>
                <button type="button" class="preset-dot" style="background:#d97706;" onclick="setEditColor('#d97706')" title="Amber"></button>
                <button type="button" class="preset-dot" style="background:#dc2626;" onclick="setEditColor('#dc2626')" title="Red"></button>
                <button type="button" class="preset-dot" style="background:#0891b2;" onclick="setEditColor('#0891b2')" title="Cyan"></button>
                <button type="button" class="preset-dot" style="background:#db2777;" onclick="setEditColor('#db2777')" title="Pink"></button>
            </div>

            <div class="form-group-block">
                <label class="modal-form-label">Description (Optional)</label>
                <textarea id="editStatusDesc" name="description" rows="2" class="modal-input-field"></textarea>
            </div>

            {{-- Toggles --}}
            <div class="modal-toggle-box-stacked">
                <label class="toggle-control-label">
                    <input type="checkbox" id="editStatusRequiresAppointment" name="requires_appointment" value="1" class="custom-toggle-input">
                    <div class="toggle-text-group">
                        <span class="toggle-title"><i class="fas fa-calendar-day" style="color:#d97706;"></i> Requires Appointment (Date & Time)</span>
                        <span class="toggle-help">Prompts telecaller for date & time when logging this status.</span>
                    </div>
                </label>

                <div class="toggle-divider-line"></div>

                <label class="toggle-control-label">
                    <input type="checkbox" id="editStatusRequiresLocation" name="requires_location" value="1" class="custom-toggle-input">
                    <div class="toggle-text-group">
                        <span class="toggle-title"><i class="fas fa-map-location-dot" style="color:#6366f1;"></i> Requires Location / Meeting URL</span>
                        <span class="toggle-help">Prompts telecaller for client visit location or online meeting URL.</span>
                    </div>
                </label>

                <div class="toggle-divider-line"></div>

                <label class="toggle-control-label">
                    <input type="checkbox" id="editStatusIsActive" name="is_active" value="1" class="custom-toggle-input">
                    <div class="toggle-text-group">
                        <span class="toggle-title"><i class="fas fa-circle-check" style="color:#10b981;"></i> Active in Mobile App Remarks Dropdown</span>
                        <span class="toggle-help">Toggle whether telecallers can see and choose this status.</span>
                    </div>
                </label>
            </div>

            <div class="modal-footer-buttons">
                <button type="button" class="btn-secondary-pill" onclick="closeEditStatusModal()">Cancel</button>
                <button type="submit" class="btn-primary-gradient"><i class="fas fa-floppy-disk"></i> Update Status</button>
            </div>
        </form>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- STYLES: FULL-SCREEN MODERN DASHBOARD --}}
{{-- ========================================================================= --}}
<style>
/* Fullscreen wrapper: spans 100% of workspace */
.settings-fullscreen-wrapper {
    width: 100%;
    margin: 0;
    padding: 0 4px 40px;
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* Hero Header Card */
.settings-hero-card {
    background: #ffffff;
    border-radius: 20px;
    padding: 22px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
    border: 1px solid #e2e8f0;
}
.settings-hero-left {
    display: flex;
    align-items: center;
    gap: 16px;
}
.settings-hero-icon {
    width: 50px;
    height: 50px;
    border-radius: 14px;
    background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
}
.settings-hero-title {
    margin: 0;
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.02em;
}
.settings-hero-subtitle {
    margin: 4px 0 0;
    font-size: 13.5px;
    color: #64748b;
    max-width: 600px;
    line-height: 1.4;
}

/* Segmented Tab Navigation Pills */
.settings-pill-nav {
    display: flex;
    background: #f1f5f9;
    padding: 5px;
    border-radius: 14px;
    gap: 6px;
    border: 1px solid #e2e8f0;
}
.settings-nav-pill {
    border: none;
    background: transparent;
    padding: 10px 20px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.22s ease;
}
.settings-nav-pill:hover {
    color: #0f172a;
    background: rgba(255, 255, 255, 0.6);
}
.settings-nav-pill.active {
    background: #ffffff;
    color: #4f46e5;
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08);
}
.pill-badge {
    background: #e2e8f0;
    color: #475569;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 800;
    transition: all 0.2s ease;
}
.settings-nav-pill.active .pill-badge {
    background: #eef2ff;
    color: #4f46e5;
}

/* Alert Notifications */
.settings-alert-success {
    background: #ecfdf5;
    border: 1px solid #a7f3d0;
    color: #065f46;
    padding: 14px 20px;
    border-radius: 14px;
    font-size: 13.5px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.08);
}
.settings-alert-error {
    background: #fef2f2;
    border: 1px solid #fecaca;
    color: #991b1b;
    padding: 14px 20px;
    border-radius: 14px;
    font-size: 13.5px;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.08);
}

/* Stats Row (Full Width Grid) */
.settings-stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 18px;
    width: 100%;
}
@media (max-width: 900px) {
    .settings-stats-row { grid-template-columns: 1fr; }
}
.settings-stat-card {
    background: #ffffff;
    border-radius: 18px;
    padding: 22px 24px;
    display: flex;
    align-items: center;
    gap: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.settings-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
}
.stat-icon-wrap {
    width: 54px;
    height: 54px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #ffffff;
    flex-shrink: 0;
}
.stat-grad-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); box-shadow: 0 6px 16px rgba(99, 102, 241, 0.35); }
.stat-grad-emerald { background: linear-gradient(135deg, #10b981, #059669); box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35); }
.stat-grad-amber { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35); }
.stat-grad-blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); box-shadow: 0 6px 16px rgba(59, 130, 246, 0.35); }
.stat-grad-purple { background: linear-gradient(135deg, #8b5cf6, #6d28d9); box-shadow: 0 6px 16px rgba(139, 92, 246, 0.35); }

.stat-info { display: flex; flex-direction: column; }
.stat-num { font-size: 28px; font-weight: 800; color: #0f172a; line-height: 1.1; }
.stat-label { font-size: 13.5px; font-weight: 700; color: #334155; margin-top: 3px; }
.stat-subtext { font-size: 12px; color: #94a3b8; margin-top: 2px; }

/* Toolbar Card */
.settings-toolbar-card {
    background: #ffffff;
    border-radius: 18px;
    padding: 16px 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 14px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
}
.toolbar-search-box {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 10px 16px;
    flex: 1;
    max-width: 440px;
    transition: all 0.2s ease;
}
.toolbar-search-box:focus-within {
    background: #ffffff;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}
.toolbar-search-box i { color: #94a3b8; font-size: 14px; }
.toolbar-search-box input {
    border: none;
    background: transparent;
    outline: none;
    width: 100%;
    font-size: 13.5px;
    color: #0f172a;
}

/* Action Buttons */
.btn-primary-gradient {
    background: linear-gradient(135deg, #4f46e5 0%, #4338ca 100%);
    color: #ffffff;
    border: none;
    padding: 11px 22px;
    border-radius: 12px;
    font-size: 13.5px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
    transition: all 0.2s ease;
}
.btn-primary-gradient:hover {
    background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%);
    box-shadow: 0 6px 18px rgba(79, 70, 229, 0.45);
    transform: translateY(-1px);
}
.btn-secondary-pill {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}
.btn-secondary-pill:hover {
    background: #e2e8f0;
    color: #0f172a;
}

/* Full Width Table Card */
.settings-table-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.05);
    overflow: hidden;
    width: 100%;
}
.settings-custom-table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
}
.settings-custom-table th {
    background: #f8fafc;
    padding: 15px 20px;
    font-size: 11.5px;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border-bottom: 1px solid #e2e8f0;
}
.settings-custom-table td {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.settings-custom-table tr:hover td {
    background: #f8fafc;
}
.settings-custom-table tr:last-child td {
    border-bottom: none;
}

/* Table Elements */
.id-badge {
    background: #f1f5f9;
    color: #64748b;
    font-size: 12px;
    font-weight: 800;
    padding: 4px 8px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}
.entity-name-cell {
    display: flex;
    align-items: center;
    gap: 12px;
}
.entity-avatar-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    background: #eef2ff;
    color: #4f46e5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}
.entity-primary-title {
    font-size: 14.5px;
    font-weight: 700;
    color: #0f172a;
}
.entity-secondary-text {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 1px;
}
.entity-desc-text {
    font-size: 13px;
    color: #475569;
    max-width: 480px;
    line-height: 1.45;
}
.stat-counter-pill {
    background: #eef2ff;
    color: #4338ca;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12.5px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Badges */
.badge-status-active {
    background: #ecfdf5;
    color: #059669;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: 1px solid #a7f3d0;
}
.badge-status-inactive {
    background: #f1f5f9;
    color: #64748b;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: 1px solid #cbd5e1;
}
.badge-flag-yes-amber {
    background: #fffbeb;
    color: #b45309;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #fde68a;
}
.badge-flag-yes-indigo {
    background: #eef2ff;
    color: #4338ca;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border: 1px solid #c7d2fe;
}
.badge-flag-no {
    color: #94a3b8;
    font-size: 12px;
    font-weight: 600;
}

/* Telecaller Status Badge */
.status-color-circle {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    flex-shrink: 0;
    box-shadow: 0 0 0 2px #ffffff, 0 0 0 3px #cbd5e1;
}
.status-preview-pill {
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 12.5px;
    font-weight: 800;
    border-width: 1px;
    border-style: solid;
    display: inline-flex;
    align-items: center;
}

/* Action Icon Buttons */
.row-actions-group {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}
.action-btn-edit {
    background: #eef2ff;
    color: #4338ca;
    border: 1px solid #c7d2fe;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    transition: all 0.18s ease;
}
.action-btn-edit:hover {
    background: #4f46e5;
    color: #ffffff;
    border-color: #4f46e5;
    transform: scale(1.06);
}
.action-btn-delete {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    transition: all 0.18s ease;
}
.action-btn-delete:hover {
    background: #dc2626;
    color: #ffffff;
    border-color: #dc2626;
    transform: scale(1.06);
}

.table-empty-cell {
    text-align: center;
    padding: 60px 20px !important;
    color: #94a3b8;
}
.table-empty-cell i { font-size: 42px; color: #cbd5e1; margin-bottom: 12px; }
.empty-title { font-size: 16px; font-weight: 700; color: #334155; margin-bottom: 4px; }
.empty-desc { font-size: 13px; color: #94a3b8; }

/* ========================================================================= */
/* MODALS (HIGH-END FLOATING WINDOW) */
/* ========================================================================= */
.fullscreen-modal-backdrop {
    display: none;
    position: fixed; inset: 0; z-index: 99999;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(6px);
    align-items: center; justify-content: center;
    padding: 24px;
}
.fullscreen-modal-window {
    background: #ffffff;
    border-radius: 22px;
    width: 100%;
    max-width: 560px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
    animation: modalPopIn 0.25s cubic-bezier(.4,0,.2,1);
    border: 1px solid rgba(255,255,255,0.2);
}
@keyframes modalPopIn {
    from { opacity: 0; transform: translateY(16px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-window-header {
    background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);
    color: #ffffff;
    padding: 22px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.modal-header-info {
    display: flex;
    align-items: center;
    gap: 14px;
}
.modal-icon-badge {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}
.modal-header-title {
    margin: 0;
    font-size: 17px;
    font-weight: 800;
}
.modal-header-sub {
    margin: 2px 0 0;
    font-size: 12.5px;
    color: #c7d2fe;
}
.modal-close-icon {
    background: transparent;
    border: none;
    color: #ffffff;
    font-size: 26px;
    cursor: pointer;
    opacity: 0.75;
    transition: opacity 0.2s ease;
}
.modal-close-icon:hover { opacity: 1; }

.modal-body-form {
    padding: 26px 28px;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.form-group-block {
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.modal-form-label {
    font-size: 12.5px;
    font-weight: 700;
    color: #334155;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.required-asterisk { color: #dc2626; }

.modal-input-field {
    width: 100%;
    padding: 11px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 11px;
    font-size: 14px;
    color: #0f172a;
    outline: none;
    background: #f8fafc;
    transition: all 0.2s ease;
    font-family: inherit;
}
.modal-input-field:focus {
    background: #ffffff;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.14);
}

.modal-grid-two {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}
.color-picker-input {
    width: 44px;
    height: 42px;
    padding: 2px;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    cursor: pointer;
    background: #ffffff;
    flex-shrink: 0;
}

/* Live Preview */
.live-preview-box {
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.live-preview-label {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
}

/* Preset Palette */
.preset-colors-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: -6px;
}
.preset-dot {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: transform 0.15s ease;
}
.preset-dot:hover {
    transform: scale(1.2);
}

/* Toggles Stack */
.modal-toggle-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 16px;
}
.modal-toggle-box-stacked {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 14px 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.toggle-divider-line {
    height: 1px;
    background: #e2e8f0;
}
.toggle-control-label {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
}
.custom-toggle-input {
    width: 18px;
    height: 18px;
    accent-color: #4f46e5;
    margin-top: 2px;
    cursor: pointer;
}
.toggle-text-group {
    display: flex;
    flex-direction: column;
}
.toggle-title {
    font-size: 13.5px;
    font-weight: 700;
    color: #1e293b;
}
.toggle-help {
    font-size: 12px;
    color: #64748b;
    margin-top: 1px;
}

.modal-footer-buttons {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 8px;
}
</style>

{{-- ========================================================================= --}}
{{-- JAVASCRIPT: INSTANT FULLSCREEN INTERACTIONS --}}
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

// Live Search for Product Types Table
function filterProductsTable(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.product-row').forEach(row => {
        const text = row.getAttribute('data-search') || '';
        row.style.display = text.includes(q) ? '' : 'none';
    });
}

// Live Search for Telecaller Statuses Table
function filterStatusesTable(query) {
    const q = query.toLowerCase().trim();
    document.querySelectorAll('.status-row').forEach(row => {
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
function openEditProductModal(id, name, desc, isActive) {
    document.getElementById('editProductForm').action = '/settings/products/' + id;
    document.getElementById('editProductName').value = name;
    document.getElementById('editProductDesc').value = desc;
    document.getElementById('editProductIsActive').checked = (isActive == 1);
    document.getElementById('editProductModal').style.display = 'flex';
}
function closeEditProductModal() {
    document.getElementById('editProductModal').style.display = 'none';
}

// Status Modals
function openAddStatusModal() {
    document.getElementById('addStatusModal').style.display = 'flex';
    updateAddBadgePreview();
}
function closeAddStatusModal() {
    document.getElementById('addStatusModal').style.display = 'none';
}
function openEditStatusModal(id, name, color, desc, reqApp, reqLoc, isActive, sortOrder) {
    document.getElementById('editStatusForm').action = '/settings/statuses/' + id;
    document.getElementById('editStatusNameInput').value = name;
    document.getElementById('editStatusColorPicker').value = color || '#2563eb';
    document.getElementById('editStatusColorText').value = color || '#2563eb';
    document.getElementById('editStatusDesc').value = desc;
    document.getElementById('editStatusRequiresAppointment').checked = (reqApp == 1);
    document.getElementById('editStatusRequiresLocation').checked = (reqLoc == 1);
    document.getElementById('editStatusIsActive').checked = (isActive == 1);
    document.getElementById('editStatusSortOrder').value = sortOrder || 0;
    document.getElementById('editStatusModal').style.display = 'flex';
    updateEditBadgePreview();
}
function closeEditStatusModal() {
    document.getElementById('editStatusModal').style.display = 'none';
}

// Live Preview Helpers
function updateAddBadgePreview() {
    const name = document.getElementById('addStatusNameInput').value.trim() || 'Status Name';
    const color = document.getElementById('addStatusColorPicker').value || '#2563eb';
    const badge = document.getElementById('addBadgePreview');
    badge.innerHTML = `<i class="fas fa-tag" style="font-size:11px;margin-right:5px;"></i> ` + escapeHtml(name);
    badge.style.color = color;
    badge.style.background = color + '15';
    badge.style.borderColor = color + '40';
}
function updateEditBadgePreview() {
    const name = document.getElementById('editStatusNameInput').value.trim() || 'Status Name';
    const color = document.getElementById('editStatusColorPicker').value || '#2563eb';
    const badge = document.getElementById('editBadgePreview');
    badge.innerHTML = `<i class="fas fa-tag" style="font-size:11px;margin-right:5px;"></i> ` + escapeHtml(name);
    badge.style.color = color;
    badge.style.background = color + '15';
    badge.style.borderColor = color + '40';
}

function setAddColor(hex) {
    document.getElementById('addStatusColorPicker').value = hex;
    document.getElementById('addStatusColorText').value = hex;
    updateAddBadgePreview();
}
function setEditColor(hex) {
    document.getElementById('editStatusColorPicker').value = hex;
    document.getElementById('editStatusColorText').value = hex;
    updateEditBadgePreview();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}
</script>
@endsection
