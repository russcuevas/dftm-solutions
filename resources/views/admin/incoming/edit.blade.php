@extends('layouts.app')

@section('title', 'Scan Incoming Transmittal - ' . $transmittal->transmittal_no)
@section('page_title', 'Incoming Barcode Scanner & Transmittal')

@section('content')
    <style>
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .spin-icon { display: inline-block; animation: spin 1s linear infinite; }
        
        @keyframes rowPulse {
            0% { background: #EEF2FF; }
            100% { background: #FFFFFF; }
        }
        .live-row-remote { animation: rowPulse 1.2s ease-out; }

        .row-duplicate {
            background-color: #FEF2F2 !important;
            border-left: 4px solid #EF4444 !important;
        }

        .btn-inspect-dup {
            background: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
            padding: 2px 8px;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .btn-inspect-dup:hover {
            background: #FCA5A5;
            color: #991B1B;
        }

        /* Modal styling */
        .lookup-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(3px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .lookup-modal.active {
            display: flex;
        }
        .lookup-modal-content {
            background: #FFFFFF;
            width: 90%;
            max-width: 650px;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            animation: modalPop 0.2s ease-out;
        }
        @keyframes modalPop {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>

    <!-- Top Alert Banner for Duplicate Notifications -->
    <div id="duplicateAlertBanner" style="display: none; background: #FEF2F2; border: 1px solid #F87171; border-radius: 8px; padding: 12px 16px; margin-bottom: 16px; color: #991B1B;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="bi bi-exclamation-octagon-fill" style="font-size: 1.3rem; color: #EF4444;"></i>
                <div>
                    <strong id="duplicateAlertText">DUPLICATE DETECTED!</strong>
                    <div style="font-size: 0.8rem; color: #B91C1C;" id="duplicateAlertSubtext"></div>
                </div>
            </div>
            <button type="button" class="btn btn-sm" id="btnOpenDupModalFromBanner" style="background: #EF4444; color: #FFFFFF; font-weight: 700; border: none;">
                <i class="bi bi-search"></i> View Status & Details
            </button>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title" style="display: flex; align-items: center; gap: 10px;">
                    <i class="bi bi-upc-scan"></i> Transmittal: <span class="mono" style="color: var(--dftm-accent);">{{ $transmittal->transmittal_no }}</span>
                </div>
                <div class="card-subtitle">
                    Continuous Barcode Scanning • Multi-Employee Consolidated Sync • Auto-Saved Real-Time
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <span class="badge badge-stock" id="liveUnitsBadge" style="font-size: 0.92rem; padding: 7px 16px;">
                    <i class="bi bi-cpu"></i> <span id="totalUnitsCount">{{ $transmittal->total_quantity }}</span> Units Scanned
                </span>
                <span class="live-status-pill" id="syncStatusBadge" style="background: #ECFDF5; color: #059669; border: 1px solid #A7F3D0; font-size: 0.8rem;">
                    <i class="bi bi-broadcast" style="color: #10B981;"></i> Live Sync Active
                </span>
                <a href="{{ route('admin.incoming.print', $transmittal->id) }}" target="_blank" class="btn btn-outline btn-sm">
                    <i class="bi bi-printer"></i> Print Report
                </a>
                <a href="{{ route('admin.incoming.show', $transmittal->id) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-eye"></i> View Transmittal
                </a>
                <a href="{{ route('admin.incoming.index') }}" class="btn btn-outline btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <div class="card-body">
            <!-- Header Row: Transmittal No, Company Name, Date Received, Brand, Model -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700;">Transmittal Number</label>
                    <input type="text" name="transmittal_no" class="form-control mono transmittal-header-input"
                        value="{{ old('transmittal_no', $transmittal->transmittal_no) }}" placeholder="e.g. TR-20260915-001" required>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 700;">Company / Client Name</label>
                    <select name="company_name" class="form-select transmittal-header-input" style="font-weight: 700;">
                        @if($transmittal->company_name && !$clients->contains('company_name', $transmittal->company_name))
                            <option value="{{ $transmittal->company_name }}" selected>{{ $transmittal->company_name }} (Current)</option>
                        @endif
                        @foreach($clients as $c)
                            <option value="{{ $c->company_name }}" {{ $transmittal->company_name == $c->company_name ? 'selected' : '' }}>
                                {{ $c->company_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Received</label>
                    <input type="date" name="date_received" class="form-control transmittal-header-input"
                        value="{{ old('date_received', $transmittal->date_received ? $transmittal->date_received->format('Y-m-d') : '') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Default Brand</label>
                    <input type="text" name="brand" list="brandSuggestions" class="form-control transmittal-header-input"
                        value="{{ old('brand', $transmittal->brand) }}" placeholder="e.g. HUAWEI">
                    <datalist id="brandSuggestions">
                        <option value="HUAWEI">
                        <option value="ZTE">
                        <option value="SKYWORTH">
                        <option value="FIBERHOME">
                        <option value="NOKIA">
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Default Model</label>
                    <input type="text" name="model" class="form-control transmittal-header-input"
                        value="{{ old('model', $transmittal->model) }}" placeholder="e.g. EG8145V5">
                </div>
            </div>

            <!-- Fast Serial Search Bar & Custom Add Row Bar -->
            <div style="background: #F8FAFC; border: 1px solid var(--dftm-border); border-radius: 8px; padding: 12px 16px; margin: 16px 0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
                <!-- Custom Row Generator: input e.g. 100 rows -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-weight: 700; font-size: 0.85rem; color: var(--dftm-navy);">
                        <i class="bi bi-grid-plus"></i> Generate Blank Scan Rows:
                    </span>
                    <input type="number" id="customRowQtyInput" class="form-control" style="width: 85px; text-align: center; font-weight: 700;" value="100" min="1" max="1000">
                    <button type="button" class="btn btn-accent btn-sm" id="btnGenerateCustomRows" style="font-weight: 700;">
                        <i class="bi bi-plus-circle-fill"></i> Add Rows
                    </button>
                    <button type="button" class="btn btn-outline btn-sm" id="btnLiveAddSingleRow" title="Add 1 row">
                        +1 Row
                    </button>
                </div>

                <!-- Quick Serial Lookup tool -->
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="position: relative;">
                        <input type="text" id="quickSerialSearchInput" class="form-control mono" style="width: 260px; font-size: 0.85rem;" placeholder="Search serial / check duplicate...">
                    </div>
                    <button type="button" class="btn btn-outline btn-sm" id="btnSearchSerialModal">
                        <i class="bi bi-search"></i> Search Status
                    </button>
                </div>
            </div>

            <!-- Barcode Scanning Table -->
            <div class="subtable-container" style="margin-top: 10px;">
                <div class="subtable-header">
                    <div>
                        <div class="subtable-title" style="display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-barcode"></i> Continuous Barcode Scanner (Deri-deritso ang Scan)
                        </div>
                        <div style="font-size: 0.78rem; color: var(--dftm-slate);">
                            Itapat ang barcode gun sa <strong>Serial Number</strong>. Kusa itong mag-se-save at lilipat sa <strong>MAC</strong> o sa <strong>susunod na row</strong> nang tuloy-tuloy.
                        </div>
                    </div>
                </div>

                <!-- Column Headers -->
                <div style="display: grid; grid-template-columns: 40px 1.5fr 1.5fr 1fr 1fr 1fr 30px 40px; gap: 8px; padding: 8px 12px; font-size: 0.74rem; font-weight: 800; color: var(--dftm-navy); text-transform: uppercase; background: #F1F5F9; border-radius: 6px 6px 0 0;">
                    <div style="text-align: center;">NO.</div>
                    <div>Serial Number (Scan Barcode)</div>
                    <div>MAC Address</div>
                    <div>Model</div>
                    <div>Brand</div>
                    <div>Box No.</div>
                    <div style="text-align: center;"></div>
                    <div style="text-align: center;">Action</div>
                </div>

                <div id="subtableRows">
                    @forelse($transmittal->items as $item)
                        <div class="subtable-row" data-item-id="{{ $item->id }}" style="grid-template-columns: 40px 1.5fr 1.5fr 1fr 1fr 1fr 30px 40px; gap: 8px;">
                            <input type="hidden" name="item_id[]" class="row-item-id" value="{{ $item->id }}">
                            <div class="subtable-row-num">{{ $loop->iteration }}</div>
                            <div>
                                <input type="text" name="serial_number[]" class="form-control mono row-sn"
                                    value="{{ $item->serial_number }}" placeholder="Scan Serial Number">
                            </div>
                            <div>
                                <input type="text" name="mac_address[]" class="form-control mono row-mac"
                                    value="{{ $item->mac_address }}" placeholder="Scan MAC Address">
                            </div>
                            <div>
                                <input type="text" name="row_model[]" class="form-control row-model"
                                    value="{{ $item->model }}" placeholder="Model (e.g. EG8145V5)">
                            </div>
                            <div>
                                <input type="text" name="row_brand[]" class="form-control row-brand"
                                    value="{{ $item->brand }}" placeholder="Brand (e.g. HUAWEI)">
                            </div>
                            <div>
                                <input type="text" name="box_no[]" class="form-control row-box"
                                    value="{{ $item->box_no }}" placeholder="Box No">
                            </div>
                            <div class="row-status-indicator" style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                <i class="bi bi-check-lg" style="color: #10B981; font-weight: 800;"></i>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <button type="button" class="btn btn-outline btn-icon btn-remove-row" title="Delete Row" style="color: #DC2626; border-color: #FECACA;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="subtable-row" style="grid-template-columns: 40px 1.5fr 1.5fr 1fr 1fr 1fr 30px 40px; gap: 8px;">
                            <input type="hidden" name="item_id[]" class="row-item-id" value="">
                            <div class="subtable-row-num">1</div>
                            <div><input type="text" name="serial_number[]" class="form-control mono row-sn" placeholder="Scan Serial Number"></div>
                            <div><input type="text" name="mac_address[]" class="form-control mono row-mac" placeholder="Scan MAC Address"></div>
                            <div><input type="text" name="row_model[]" class="form-control row-model" value="{{ $transmittal->model }}" placeholder="Model"></div>
                            <div><input type="text" name="row_brand[]" class="form-control row-brand" value="{{ $transmittal->brand }}" placeholder="Brand"></div>
                            <div><input type="text" name="box_no[]" class="form-control row-box" placeholder="Box No"></div>
                            <div class="row-status-indicator" style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;"></div>
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <button type="button" class="btn btn-outline btn-icon btn-remove-row" style="color: #DC2626;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group" style="margin-top: 18px;">
                <label class="form-label">Transmittal Notes</label>
                <textarea name="notes" class="form-control transmittal-header-input" rows="2" placeholder="Optional notes">{{ old('notes', $transmittal->notes) }}</textarea>
            </div>
        </div>

        <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.85rem; color: var(--dftm-slate);">
                <i class="bi bi-info-circle"></i> Ang lahat ng na-scan ay kusa nang nai-save sa database.
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('admin.incoming.show', $transmittal->id) }}" class="btn btn-primary">
                    <i class="bi bi-check2-circle"></i> Done / View Summary
                </a>
                <a href="{{ route('admin.incoming.index') }}" class="btn btn-outline">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <!-- Duplicate & Serial Status Inspector Modal -->
    <div class="lookup-modal" id="serialInspectorModal">
        <div class="lookup-modal-content">
            <div style="background: #00205B; color: #FFFFFF; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;">
                <div style="font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-info-circle-fill" style="color: #38BDF8;"></i> Serial Number Status & Duplicate History
                </div>
                <button type="button" id="btnCloseInspectorModal" style="background: transparent; border: none; color: #FFFFFF; font-size: 1.2rem; cursor: pointer;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div style="padding: 20px; max-height: 520px; overflow-y: auto;">
                <div id="inspectorLoading" style="display: none; text-align: center; padding: 24px;">
                    <i class="bi bi-arrow-repeat spin-icon" style="font-size: 2rem; color: #00205B;"></i>
                    <div style="margin-top: 8px; font-weight: 600;">Searching system database...</div>
                </div>

                <div id="inspectorContent">
                    <!-- Dynamic details will be populated here -->
                </div>
            </div>

            <div style="background: #F8FAFC; border-top: 1px solid var(--dftm-border); padding: 12px 20px; text-align: right;">
                <button type="button" class="btn btn-outline btn-sm" id="btnCloseInspectorFooter">Close</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const getItemsUrl = '{{ route('admin.incoming.items', $transmittal->id) }}';
            const saveItemUrl = '{{ route('admin.incoming.saveItem', $transmittal->id) }}';
            const deleteItemUrl = '{{ url('admin/incoming/' . $transmittal->id . '/item') }}';
            const saveHeaderUrl = '{{ route('admin.incoming.saveHeader', $transmittal->id) }}';
            const checkDuplicateUrl = '{{ route('admin.incoming.checkDuplicate') }}';
            const searchSerialUrl = '{{ route('admin.incoming.searchSerial') }}';
            const csrfToken = '{{ csrf_token() }}';

            const container = document.getElementById('subtableRows');
            const totalUnitsCount = document.getElementById('totalUnitsCount');
            const syncStatusBadge = document.getElementById('syncStatusBadge');
            const btnGenerateCustomRows = document.getElementById('btnGenerateCustomRows');
            const customRowQtyInput = document.getElementById('customRowQtyInput');
            const btnLiveAddSingleRow = document.getElementById('btnLiveAddSingleRow');
            const headerInputs = document.querySelectorAll('.transmittal-header-input');
            const duplicateAlertBanner = document.getElementById('duplicateAlertBanner');
            const duplicateAlertText = document.getElementById('duplicateAlertText');
            const duplicateAlertSubtext = document.getElementById('duplicateAlertSubtext');
            const btnOpenDupModalFromBanner = document.getElementById('btnOpenDupModalFromBanner');
            const modal = document.getElementById('serialInspectorModal');
            const btnCloseInspectorModal = document.getElementById('btnCloseInspectorModal');
            const btnCloseInspectorFooter = document.getElementById('btnCloseInspectorFooter');
            const inspectorLoading = document.getElementById('inspectorLoading');
            const inspectorContent = document.getElementById('inspectorContent');
            const quickSerialSearchInput = document.getElementById('quickSerialSearchInput');
            const btnSearchSerialModal = document.getElementById('btnSearchSerialModal');

            let lastDetectedDuplicateSN = '';
            let isPolling = false;
            const deletedIds = new Set();

            function setSyncStatus(status) {
                if (!syncStatusBadge) return;
                if (status === 'saving') {
                    syncStatusBadge.style.background = '#FEF3C7';
                    syncStatusBadge.style.color = '#D97706';
                    syncStatusBadge.style.borderColor = '#FDE68A';
                    syncStatusBadge.innerHTML = '<i class="bi bi-cloud-arrow-up-fill spin-icon"></i> Saving...';
                } else if (status === 'saved') {
                    syncStatusBadge.style.background = '#ECFDF5';
                    syncStatusBadge.style.color = '#059669';
                    syncStatusBadge.style.borderColor = '#A7F3D0';
                    syncStatusBadge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Saved Real-Time';
                    setTimeout(() => {
                        if (syncStatusBadge.textContent.includes('Saved')) {
                            syncStatusBadge.innerHTML = '<i class="bi bi-broadcast" style="color: #10B981;"></i> Live Sync Active';
                        }
                    }, 2000);
                }
            }

            // Header auto-save
            headerInputs.forEach(input => {
                input.addEventListener('change', function() {
                    setSyncStatus('saving');
                    const data = {
                        _token: csrfToken,
                        transmittal_no: document.querySelector('[name="transmittal_no"]')?.value || '',
                        company_name: document.querySelector('[name="company_name"]')?.value || '',
                        date_received: document.querySelector('[name="date_received"]')?.value || '',
                        brand: document.querySelector('[name="brand"]')?.value || '',
                        model: document.querySelector('[name="model"]')?.value || '',
                        status: document.querySelector('[name="status"]')?.value || '',
                        notes: document.querySelector('[name="notes"]')?.value || '',
                    };
                    fetch(saveHeaderUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(data)
                    }).then(r => r.json()).then(() => {
                        setSyncStatus('saved');
                    }).catch(e => console.error(e));
                });
            });

            // Save row function with duplicate checking
            function saveRow(row) {
                const itemIdInput = row.querySelector('.row-item-id');
                const snInput = row.querySelector('.row-sn');
                const macInput = row.querySelector('.row-mac');
                const modelInput = row.querySelector('.row-model');
                const brandInput = row.querySelector('.row-brand');
                const boxInput = row.querySelector('.row-box');
                const statusIndicator = row.querySelector('.row-status-indicator');

                const itemId = itemIdInput ? itemIdInput.value : '';
                const sn = snInput ? snInput.value.trim() : '';
                const mac = macInput ? macInput.value.trim() : '';
                const model = modelInput ? modelInput.value.trim() : '';
                const brand = brandInput ? brandInput.value.trim() : '';
                const box = boxInput ? boxInput.value.trim() : '';

                if (!itemId && !sn && !mac && !box) return;

                if (statusIndicator) {
                    statusIndicator.innerHTML = '<i class="bi bi-arrow-repeat spin-icon" style="color: var(--dftm-accent);"></i>';
                }
                setSyncStatus('saving');

                fetch(saveItemUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        _token: csrfToken,
                        item_id: itemId || null,
                        serial_number: sn,
                        mac_address: mac,
                        model: model,
                        brand: brand,
                        box_no: box
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.item) {
                        if (itemIdInput) itemIdInput.value = data.item.id;
                        row.setAttribute('data-item-id', data.item.id);

                        if (data.is_duplicate && data.duplicate_info) {
                            row.classList.add('row-duplicate');
                            lastDetectedDuplicateSN = sn;
                            duplicateAlertText.textContent = `DUPLICATE DETECTED: Serial "${sn}" already exists in the system!`;
                            duplicateAlertSubtext.textContent = `Previously registered in ${data.duplicate_info.transmittal_no} | Status: ${data.duplicate_info.repair_status || 'In process'}`;
                            duplicateAlertBanner.style.display = 'block';

                            if (statusIndicator) {
                                statusIndicator.innerHTML = `
                                    <button type="button" class="btn-inspect-dup" onclick="openInspectorFor('${sn}')" title="Inspect duplicate details">
                                        <i class="bi bi-exclamation-triangle-fill"></i> Dup
                                    </button>
                                `;
                            }
                        } else {
                            row.classList.remove('row-duplicate');
                            if (statusIndicator) {
                                statusIndicator.innerHTML = '<i class="bi bi-check-lg" style="color: #10B981; font-weight: 800;"></i>';
                            }
                        }

                        if (totalUnitsCount && data.transmittal) {
                            totalUnitsCount.textContent = data.transmittal.total_quantity;
                        }
                        setSyncStatus('saved');
                    }
                })
                .catch(err => {
                    console.error('Save error:', err);
                    if (statusIndicator) {
                        statusIndicator.innerHTML = '<i class="bi bi-exclamation-triangle" style="color: #EF4444;" title="Save failed"></i>';
                    }
                });
            }

            // Continuous navigation helper
            function moveToNextRowOrAddNew(currentRow) {
                const nextRow = currentRow.nextElementSibling;
                if (nextRow && nextRow.classList.contains('subtable-row')) {
                    const nextSn = nextRow.querySelector('.row-sn');
                    if (nextSn) {
                        nextSn.focus();
                        nextSn.select();
                    }
                } else {
                    addNewRow(true);
                }
            }

            // Attach listeners to a row (Barcode Scanner Continuous Flow)
            function attachRowListeners(row) {
                const snInput = row.querySelector('.row-sn');
                const macInput = row.querySelector('.row-mac');
                const modelInput = row.querySelector('.row-model');
                const brandInput = row.querySelector('.row-brand');
                const boxInput = row.querySelector('.row-box');
                let debounceTimer = null;

                const allInputs = [snInput, macInput, modelInput, brandInput, boxInput].filter(Boolean);

                allInputs.forEach(input => {
                    input.addEventListener('input', function() {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(() => saveRow(row), 450);
                    });

                    input.addEventListener('blur', function() {
                        clearTimeout(debounceTimer);
                        saveRow(row);
                    });

                    input.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    });
                });

                // 1. Serial Number Enter handler (Continuous flow)
                if (snInput) {
                    snInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            e.stopPropagation();
                            clearTimeout(debounceTimer);
                            saveRow(row);

                            // Auto advance to MAC Address in the same row
                            if (macInput) {
                                setTimeout(() => {
                                    macInput.focus();
                                    macInput.select();
                                }, 30);
                            } else {
                                moveToNextRowOrAddNew(row);
                            }
                        }
                    });
                }

                // 2. MAC Address Enter handler (Continuous flow)
                if (macInput) {
                    macInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            e.stopPropagation();
                            clearTimeout(debounceTimer);
                            saveRow(row);

                            // Advance directly to next row's Serial Number for continuous scan!
                            setTimeout(() => {
                                moveToNextRowOrAddNew(row);
                            }, 30);
                        }
                    });
                }

                // 3. Delete button
                const delBtn = row.querySelector('.btn-remove-row');
                if (delBtn) {
                    delBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        clearTimeout(debounceTimer);

                        const itemIdInput = row.querySelector('.row-item-id');
                        const itemId = itemIdInput ? itemIdInput.value : '';

                        if (itemId) {
                            deletedIds.add(String(itemId));
                            setSyncStatus('saving');
                            fetch(`${deleteItemUrl}/${itemId}`, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': csrfToken,
                                    'Accept': 'application/json'
                                }
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (totalUnitsCount && data.transmittal) {
                                    totalUnitsCount.textContent = data.transmittal.total_quantity;
                                }
                                setSyncStatus('saved');
                            });
                        }

                        row.remove();
                        renumberRows();
                    };
                }
            }

            // Add single new row
            function addNewRow(autoFocus = true) {
                const currentCount = container.querySelectorAll('.subtable-row').length + 1;
                const defaultBrand = document.querySelector('[name="brand"]')?.value || '';
                const defaultModel = document.querySelector('[name="model"]')?.value || '';

                // Inherit Box No from previous row if present
                let defaultBox = '';
                const lastRow = container.querySelector('.subtable-row:last-child');
                if (lastRow) {
                    const lb = lastRow.querySelector('.row-box');
                    if (lb && lb.value.trim()) defaultBox = lb.value.trim();
                }

                const rowDiv = document.createElement('div');
                rowDiv.className = 'subtable-row live-row-new';
                rowDiv.style.cssText = 'grid-template-columns: 40px 1.5fr 1.5fr 1fr 1fr 1fr 30px 40px; gap: 8px;';
                rowDiv.innerHTML = `
                    <input type="hidden" name="item_id[]" class="row-item-id" value="">
                    <div class="subtable-row-num">${currentCount}</div>
                    <div>
                        <input type="text" name="serial_number[]" class="form-control mono row-sn" placeholder="Scan Serial Number">
                    </div>
                    <div>
                        <input type="text" name="mac_address[]" class="form-control mono row-mac" placeholder="Scan MAC Address">
                    </div>
                    <div>
                        <input type="text" name="row_model[]" class="form-control row-model" value="${defaultModel}" placeholder="Model">
                    </div>
                    <div>
                        <input type="text" name="row_brand[]" class="form-control row-brand" value="${defaultBrand}" placeholder="Brand">
                    </div>
                    <div>
                        <input type="text" name="box_no[]" class="form-control row-box" value="${defaultBox}" placeholder="Box No">
                    </div>
                    <div class="row-status-indicator" style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;"></div>
                    <div style="display: flex; align-items: center; justify-content: center;">
                        <button type="button" class="btn btn-outline btn-icon btn-remove-row" title="Delete Row" style="color: #DC2626; border-color: #FECACA;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;

                container.appendChild(rowDiv);
                attachRowListeners(rowDiv);

                if (autoFocus) {
                    const snInput = rowDiv.querySelector('.row-sn');
                    if (snInput) {
                        setTimeout(() => {
                            snInput.focus();
                            snInput.select();
                        }, 40);
                    }
                }
                return rowDiv;
            }

            // Custom Bulk Row Generator (e.g. 100 rows)
            if (btnGenerateCustomRows) {
                btnGenerateCustomRows.addEventListener('click', function() {
                    const count = parseInt(customRowQtyInput.value, 10) || 10;
                    if (count <= 0) return;

                    const initialRows = container.querySelectorAll('.subtable-row').length;
                    const fragment = document.createDocumentFragment();
                    const defaultBrand = document.querySelector('[name="brand"]')?.value || '';
                    const defaultModel = document.querySelector('[name="model"]')?.value || '';

                    let defaultBox = '';
                    const lastRow = container.querySelector('.subtable-row:last-child');
                    if (lastRow) {
                        const lb = lastRow.querySelector('.row-box');
                        if (lb && lb.value.trim()) defaultBox = lb.value.trim();
                    }

                    for (let i = 1; i <= count; i++) {
                        const rowNum = initialRows + i;
                        const rowDiv = document.createElement('div');
                        rowDiv.className = 'subtable-row';
                        rowDiv.style.cssText = 'grid-template-columns: 40px 1.5fr 1.5fr 1fr 1fr 1fr 30px 40px; gap: 8px;';
                        rowDiv.innerHTML = `
                            <input type="hidden" name="item_id[]" class="row-item-id" value="">
                            <div class="subtable-row-num">${rowNum}</div>
                            <div>
                                <input type="text" name="serial_number[]" class="form-control mono row-sn" placeholder="Scan Serial Number">
                            </div>
                            <div>
                                <input type="text" name="mac_address[]" class="form-control mono row-mac" placeholder="Scan MAC Address">
                            </div>
                            <div>
                                <input type="text" name="row_model[]" class="form-control row-model" value="${defaultModel}" placeholder="Model">
                            </div>
                            <div>
                                <input type="text" name="row_brand[]" class="form-control row-brand" value="${defaultBrand}" placeholder="Brand">
                            </div>
                            <div>
                                <input type="text" name="box_no[]" class="form-control row-box" value="${defaultBox}" placeholder="Box No">
                            </div>
                            <div class="row-status-indicator" style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;"></div>
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <button type="button" class="btn btn-outline btn-icon btn-remove-row" title="Delete Row" style="color: #DC2626; border-color: #FECACA;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                        fragment.appendChild(rowDiv);
                    }

                    container.appendChild(fragment);

                    // Attach listeners
                    container.querySelectorAll('.subtable-row').forEach(r => attachRowListeners(r));

                    // Focus on the first empty serial number field
                    const firstEmpty = container.querySelector('.row-sn:not([value]), .row-sn[value=""]');
                    if (firstEmpty) {
                        firstEmpty.focus();
                        firstEmpty.select();
                    }
                });
            }

            if (btnLiveAddSingleRow) {
                btnLiveAddSingleRow.addEventListener('click', function() {
                    addNewRow(true);
                });
            }

            function renumberRows() {
                const rows = container.querySelectorAll('.subtable-row');
                rows.forEach((r, idx) => {
                    const num = r.querySelector('.subtable-row-num');
                    if (num) num.textContent = idx + 1;
                });
            }

            // Attach initial listeners
            container.querySelectorAll('.subtable-row').forEach(row => attachRowListeners(row));

            // Inspector Modal functions
            window.openInspectorFor = function(serial) {
                if (!serial) return;
                modal.classList.add('active');
                inspectorLoading.style.display = 'block';
                inspectorContent.innerHTML = '';

                fetch(`${searchSerialUrl}?serial=${encodeURIComponent(serial)}`, {
                    headers: { 'Accept': 'application/json' }
                })
                .then(r => r.json())
                .then(data => {
                    inspectorLoading.style.display = 'none';
                    if (!data.found || !data.items || data.items.length === 0) {
                        inspectorContent.innerHTML = `
                            <div style="text-align: center; padding: 24px; color: var(--dftm-slate);">
                                <i class="bi bi-question-circle" style="font-size: 2.5rem; color: #94A3B8;"></i>
                                <div style="margin-top: 8px; font-weight: 700;">No existing records found for "${serial}".</div>
                            </div>
                        `;
                        return;
                    }

                    let html = `<div style="margin-bottom: 12px; font-weight: 800; font-size: 0.95rem; color: var(--dftm-navy);">Found ${data.items.length} Occurrence(s) for "${serial}":</div>`;
                    data.items.forEach((it, idx) => {
                        html += `
                            <div style="background: #F8FAFC; border: 1px solid var(--dftm-border); border-radius: 8px; padding: 14px; margin-bottom: 12px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span class="mono" style="font-weight: 800; color: #00205B; font-size: 1rem;">${it.serial_number}</span>
                                    <span class="badge ${it.repair_status === 'REPAIRED' ? 'badge-repaired' : (it.repair_status === 'BER' ? 'badge-ber' : 'badge-stock')}">
                                        ${it.repair_status || 'In process'}
                                    </span>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.82rem;">
                                    <div><strong>Transmittal:</strong> ${it.transmittal_no}</div>
                                    <div><strong>Batch:</strong> ${it.batch_no}</div>
                                    <div><strong>MAC:</strong> <span class="mono">${it.mac_address || '-'}</span></div>
                                    <div><strong>Model/Brand:</strong> ${it.brand || ''} ${it.model || ''}</div>
                                    <div><strong>Stock Status:</strong> <span class="badge badge-light">${it.stock_status}</span></div>
                                    <div><strong>Date Received:</strong> ${it.date || '-'}</div>
                                    <div><strong>Company:</strong> ${it.company || '-'}</div>
                                    <div><strong>Box No:</strong> ${it.box_no || '-'}</div>
                                </div>
                                ${it.technical_diagnostic ? `<div style="margin-top: 6px; font-size: 0.8rem;"><strong>Diagnostic:</strong> ${it.technical_diagnostic}</div>` : ''}
                                ${it.replace_parts ? `<div style="font-size: 0.8rem;"><strong>Replace Parts:</strong> ${it.replace_parts}</div>` : ''}
                            </div>
                        `;
                    });

                    inspectorContent.innerHTML = html;
                })
                .catch(err => {
                    inspectorLoading.style.display = 'none';
                    inspectorContent.innerHTML = `<div style="color: #EF4444; padding: 16px;">Failed to load records.</div>`;
                });
            };

            if (btnOpenDupModalFromBanner) {
                btnOpenDupModalFromBanner.addEventListener('click', function() {
                    openInspectorFor(lastDetectedDuplicateSN);
                });
            }

            if (btnSearchSerialModal) {
                btnSearchSerialModal.addEventListener('click', function() {
                    const q = quickSerialSearchInput.value.trim();
                    if (!q) return alert('Please enter a Serial Number or MAC to search.');
                    openInspectorFor(q);
                });
            }

            if (quickSerialSearchInput) {
                quickSerialSearchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const q = this.value.trim();
                        if (q) openInspectorFor(q);
                    }
                });
            }

            function closeModal() {
                modal.classList.remove('active');
            }
            if (btnCloseInspectorModal) btnCloseInspectorModal.addEventListener('click', closeModal);
            if (btnCloseInspectorFooter) btnCloseInspectorFooter.addEventListener('click', closeModal);

            // Multi-employee Live Polling (Sync every 1.5s)
            function pollSync() {
                if (isPolling) return;
                isPolling = true;

                fetch(getItemsUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.success && Array.isArray(data.items)) {
                        syncDomWithServer(data.items, data.transmittal);
                    }
                })
                .catch(e => console.warn('Sync notice:', e))
                .finally(() => {
                    isPolling = false;
                });
            }

            function syncDomWithServer(serverItems, transmittalData) {
                if (totalUnitsCount && transmittalData) {
                    totalUnitsCount.textContent = transmittalData.total_quantity;
                }

                const focusedEl = document.activeElement;
                const focusedRow = focusedEl ? focusedEl.closest('.subtable-row') : null;

                const serverMap = new Map();
                serverItems.forEach(item => {
                    const strId = String(item.id);
                    if (!deletedIds.has(strId)) {
                        serverMap.set(strId, item);
                    }
                });

                // Update existing rows
                const domRows = container.querySelectorAll('.subtable-row');
                domRows.forEach(row => {
                    const itemIdInput = row.querySelector('.row-item-id');
                    const rowItemId = itemIdInput ? String(itemIdInput.value) : '';

                    if (rowItemId) {
                        if (deletedIds.has(rowItemId) || !serverMap.has(rowItemId)) {
                            if (row !== focusedRow) row.remove();
                        } else {
                            const sItem = serverMap.get(rowItemId);
                            if (row !== focusedRow) {
                                const snInput = row.querySelector('.row-sn');
                                const macInput = row.querySelector('.row-mac');
                                const boxInput = row.querySelector('.row-box');
                                if (snInput && snInput.value !== sItem.serial_number) snInput.value = sItem.serial_number;
                                if (macInput && macInput.value !== sItem.mac_address) macInput.value = sItem.mac_address;
                                if (boxInput && boxInput.value !== sItem.box_no) boxInput.value = sItem.box_no;
                            }
                            serverMap.delete(rowItemId);
                        }
                    }
                });

                // Append newly saved items from other encoders
                if (serverMap.size > 0) {
                    serverMap.forEach(newItem => {
                        if (deletedIds.has(String(newItem.id))) return;

                        const rowDiv = document.createElement('div');
                        rowDiv.className = 'subtable-row live-row-remote';
                        rowDiv.setAttribute('data-item-id', newItem.id);
                        rowDiv.style.cssText = 'grid-template-columns: 40px 1.5fr 1.5fr 1fr 1fr 1fr 30px 40px; gap: 8px;';
                        rowDiv.innerHTML = `
                            <input type="hidden" name="item_id[]" class="row-item-id" value="${newItem.id}">
                            <div class="subtable-row-num">${newItem.item_no}</div>
                            <div>
                                <input type="text" name="serial_number[]" class="form-control mono row-sn" value="${newItem.serial_number}" placeholder="Scan Serial Number">
                            </div>
                            <div>
                                <input type="text" name="mac_address[]" class="form-control mono row-mac" value="${newItem.mac_address}" placeholder="Scan MAC Address">
                            </div>
                            <div>
                                <input type="text" name="row_model[]" class="form-control row-model" value="${newItem.model || ''}" placeholder="Model">
                            </div>
                            <div>
                                <input type="text" name="row_brand[]" class="form-control row-brand" value="${newItem.brand || ''}" placeholder="Brand">
                            </div>
                            <div>
                                <input type="text" name="box_no[]" class="form-control row-box" value="${newItem.box_no || ''}" placeholder="Box No">
                            </div>
                            <div class="row-status-indicator" style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                <i class="bi bi-check-lg" style="color: #10B981; font-weight: 800;"></i>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: center;">
                                <button type="button" class="btn btn-outline btn-icon btn-remove-row" title="Delete Row" style="color: #DC2626; border-color: #FECACA;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        `;
                        container.appendChild(rowDiv);
                        attachRowListeners(rowDiv);
                    });
                    renumberRows();
                }
            }

            setInterval(pollSync, 1500);
        });
    </script>
@endsection
