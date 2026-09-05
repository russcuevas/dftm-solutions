@extends('layouts.app')

@section('title', 'Edit Incoming Batch - ' . $batch->batch_no)
@section('page_title', 'Edit Incoming Repair Slip')

@section('content')
    <style>
        @keyframes spin {
            100% {
                transform: rotate(360deg);
            }
        }

        .spin-icon {
            display: inline-block;
            animation: spin 1s linear infinite;
        }

        @keyframes rowPulse {
            0% {
                background: #EEF2FF;
            }

            100% {
                background: #FFFFFF;
            }
        }

        .live-row-remote {
            animation: rowPulse 1.2s ease-out;
        }

        .live-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 700;
            transition: all 0.25s ease;
        }
    </style>

    <div class="card">
        <div class="card-header">
            <div>
                <div class="card-title">
                    <i class="bi bi-pencil-square"></i> Batch: {{ $batch->slip_no }} ({{ $batch->batch_no }})
                </div>
                <div class="card-subtitle">
                    Awtomatikong naka-save sa database bawat input. Sabay-sabay makikita ng lahat ng encoder sa real-time
                    nang hindi nagre-refresh.
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="badge badge-stock" id="liveUnitsBadge" style="font-size: 0.9rem; padding: 6px 14px;">
                    <i class="bi bi-cpu"></i> {{ $batch->items->count() }} Units Encoded
                </span>
                <a href="{{ route('encoder.incoming.show', $batch->id) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-eye"></i> View Slip
                </a>
                <a href="{{ route('encoder.incoming.print', $batch->id) }}" target="_blank" class="btn btn-outline btn-sm">
                    <i class="bi bi-printer"></i> Print
                </a>
                <a href="{{ route('encoder.incoming.index') }}" class="btn btn-outline btn-sm">
                    <i class="bi bi-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <div class="card-body">
            <!-- Header row 1 -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Slip Number</label>
                    <input type="text" name="slip_no" class="form-control mono batch-header-input"
                        value="{{ old('slip_no', $batch->slip_no) }}" placeholder="e.g. IRS-20260905-001" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Batch Number</label>
                    <input type="text" name="batch_no" class="form-control batch-header-input"
                        value="{{ old('batch_no', $batch->batch_no) }}" placeholder="e.g. BATCH 1">
                </div>
                <div class="form-group">
                    <label class="form-label">Company Name</label>
                    <input type="text" name="company_name" list="companySuggestionsEncoder"
                        class="form-control batch-header-input" value="{{ old('company_name', $batch->company_name) }}"
                        placeholder="Enter Company / Client Name">
                    <datalist id="companySuggestionsEncoder">
                        <option value="DFTM DIGITAL SOLUTIONS">
                        <option value="CONVERGE ICT">
                        <option value="PLDT / SMART">
                        <option value="GLOBE TELECOM">
                        <option value="DITO TELECOMMUNITY">
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Date Received</label>
                    <input type="date" name="date_delivered" class="form-control batch-header-input"
                        value="{{ old('date_delivered', $batch->date_delivered ? $batch->date_delivered->format('Y-m-d') : '') }}">
                </div>
            </div>

            <!-- Header row 2: Brand, Model, Status (Optional) -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Brand</label>
                    <input type="text" name="brand" list="brandSuggestionsEncoder"
                        class="form-control batch-header-input" value="{{ old('brand', $batch->brand) }}"
                        placeholder="Enter Brand">
                    <datalist id="brandSuggestionsEncoder">
                        <option value="HUAWEI">
                        <option value="ZTE">
                        <option value="SKYWORTH">
                        <option value="FIBERHOME">
                        <option value="NOKIA">
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Model</label>
                    <input type="text" name="model" class="form-control batch-header-input"
                        value="{{ old('model', $batch->model) }}" placeholder="Enter Model">
                </div>
                <div class="form-group">
                    <label class="form-label">Status (Optional)</label>
                    <input type="text" name="status" class="form-control batch-header-input"
                        value="{{ old('status', $batch->status) }}" placeholder="e.g. In process / For repair">
                </div>
            </div>

            <!-- Real-Time Subtable -->
            <div class="subtable-container" style="margin-top: 10px;">
                <div class="subtable-header">
                    <div>
                        <div class="subtable-title" style="display: flex; align-items: center; gap: 8px;">
                            <i class="bi bi-list-ol"></i> Serialized Products (Auto-Save Real-Time)
                        </div>
                        <div style="font-size: 0.78rem; color: var(--dftm-slate);">
                            Mag-type lang ng Serial/MAC/Box. Kusa itong magse-save at agad lilitaw sa ibang nakabukas na
                            encoder tabs (Press <strong>Enter</strong> para sa susunod na row).
                        </div>
                    </div>
                    <div class="subtable-actions">
                        <button type="button" class="btn btn-accent btn-sm" id="btnLiveAddRow">
                            <i class="bi bi-plus-lg"></i> Add Item Row
                        </button>
                    </div>
                </div>

                <!-- Column Headers -->
                <div
                    style="display: grid; grid-template-columns: 40px 2fr 2fr 1.2fr 30px 40px; gap: 10px; padding: 6px 12px; font-size: 0.72rem; font-weight: 700; color: var(--dftm-navy); text-transform: uppercase;">
                    <div style="text-align: center;">NO.</div>
                    <div>Serial Number</div>
                    <div>MAC Address</div>
                    <div>Box No</div>
                    <div style="text-align: center;" title="Auto-save status"></div>
                    <div style="text-align: center;"></div>
                </div>

                <div id="subtableRows">
                    @forelse($batch->items as $item)
                        <div class="subtable-row" data-item-id="{{ $item->id }}"
                            style="grid-template-columns: 40px 2fr 2fr 1.2fr 30px 40px;">
                            <input type="hidden" name="item_id[]" class="row-item-id" value="{{ $item->id }}">
                            <div class="subtable-row-num">{{ $loop->iteration }}</div>
                            <div>
                                <input type="text" name="serial_number[]" class="form-control mono row-sn"
                                    value="{{ $item->serial_number }}" placeholder="Serial Number">
                            </div>
                            <div>
                                <input type="text" name="mac_address[]" class="form-control mono row-mac"
                                    value="{{ $item->mac_address }}" placeholder="MAC Address">
                            </div>
                            <div>
                                <input type="text" name="box_no[]" class="form-control row-box"
                                    value="{{ $item->box_no }}" placeholder="Box No">
                            </div>
                            <div class="row-status-indicator"
                                style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                <i class="bi bi-check-lg" style="color: #10B981; font-weight: 800;"></i>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline btn-icon btn-remove-row" title="Remove Row"
                                    style="color: #DC2626; border-color: #FECACA;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="subtable-row" style="grid-template-columns: 40px 2fr 2fr 1.2fr 30px 40px;">
                            <input type="hidden" name="item_id[]" class="row-item-id" value="">
                            <div class="subtable-row-num">1</div>
                            <div><input type="text" name="serial_number[]" class="form-control mono row-sn"
                                    placeholder="Serial Number"></div>
                            <div><input type="text" name="mac_address[]" class="form-control mono row-mac"
                                    placeholder="MAC Address"></div>
                            <div><input type="text" name="box_no[]" class="form-control row-box"
                                    placeholder="Box No"></div>
                            <div class="row-status-indicator"
                                style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline btn-icon btn-remove-row"
                                    style="color: #DC2626;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group" style="margin-top: 18px;">
                <label class="form-label">Batch Notes / Remarks</label>
                <textarea name="notes" class="form-control batch-header-input" rows="2" placeholder="Optional notes">{{ old('notes', $batch->notes) }}</textarea>
            </div>
        </div>

        <div class="card-footer" style="display: flex; justify-content: space-between; align-items: center;">
            <div style="font-size: 0.85rem; color: var(--dftm-slate);">
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('encoder.incoming.show', $batch->id) }}" class="btn btn-primary">
                    <i class="bi bi-check2-circle"></i> Save changes
                </a>
                <a href="{{ route('encoder.incoming.index') }}" class="btn btn-outline">
                    Back to List
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const getItemsUrl = '{{ route('encoder.incoming.items', $batch->id) }}';
            const saveItemUrl = '{{ route('encoder.incoming.saveItem', $batch->id) }}';
            const deleteItemUrl = '{{ url('encoder/incoming/' . $batch->id . '/item') }}';
            const saveHeaderUrl = '{{ route('encoder.incoming.saveHeader', $batch->id) }}';
            const csrfToken = '{{ csrf_token() }}';

            const container = document.getElementById('subtableRows');
            const liveUnitsBadge = document.getElementById('liveUnitsBadge');
            const syncStatusBadge = document.getElementById('syncStatusBadge');
            const addRowBtn = document.getElementById('btnLiveAddRow');
            const headerInputs = document.querySelectorAll('.batch-header-input');

            let isPolling = false;

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
                    syncStatusBadge.innerHTML = '<i class="bi bi-check-circle-fill"></i> Saved in real-time';
                    setTimeout(() => {
                        if (syncStatusBadge.textContent.includes('Saved')) {
                            syncStatusBadge.innerHTML =
                                '<i class="bi bi-broadcast" style="color: #10B981;"></i> Live Sync Active';
                        }
                    }, 2000);
                } else if (status === 'live') {
                    syncStatusBadge.style.background = '#ECFDF5';
                    syncStatusBadge.style.color = '#059669';
                    syncStatusBadge.style.borderColor = '#A7F3D0';
                    syncStatusBadge.innerHTML =
                        '<i class="bi bi-broadcast" style="color: #10B981;"></i> Live Sync Active';
                }
            }

            // Auto-save header inputs on change
            headerInputs.forEach(input => {
                input.addEventListener('change', function() {
                    setSyncStatus('saving');
                    const data = {
                        _token: csrfToken,
                        slip_no: document.querySelector('[name="slip_no"]')?.value || '',
                        batch_no: document.querySelector('[name="batch_no"]')?.value || '',
                        company_name: document.querySelector('[name="company_name"]')?.value ||
                            '',
                        date_delivered: document.querySelector('[name="date_delivered"]')
                            ?.value || '',
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

            // Save individual row item
            function saveRow(row) {
                const itemIdInput = row.querySelector('.row-item-id');
                const snInput = row.querySelector('.row-sn');
                const macInput = row.querySelector('.row-mac');
                const boxInput = row.querySelector('.row-box');
                const statusIndicator = row.querySelector('.row-status-indicator');

                const itemId = itemIdInput ? itemIdInput.value : '';
                const sn = snInput ? snInput.value.trim() : '';
                const mac = macInput ? macInput.value.trim() : '';
                const box = boxInput ? boxInput.value.trim() : '';

                // If newly added row has no content at all and no ID, don't create empty row in DB yet
                if (!itemId && !sn && !mac && !box) return;

                if (statusIndicator) {
                    statusIndicator.innerHTML =
                        '<i class="bi bi-arrow-repeat spin-icon" style="color: var(--dftm-accent);"></i>';
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
                            box_no: box
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.item) {
                            if (itemIdInput) itemIdInput.value = data.item.id;
                            row.setAttribute('data-item-id', data.item.id);
                            if (statusIndicator) {
                                statusIndicator.innerHTML =
                                    '<i class="bi bi-check-lg" style="color: #10B981; font-weight: 800;"></i>';
                            }
                            if (liveUnitsBadge && data.batch) {
                                liveUnitsBadge.innerHTML =
                                    `<i class="bi bi-cpu"></i> ${data.batch.total_quantity} Units Encoded`;
                            }
                            setSyncStatus('saved');
                        }
                    })
                    .catch(err => {
                        console.error('Save error:', err);
                        if (statusIndicator) {
                            statusIndicator.innerHTML =
                                '<i class="bi bi-exclamation-triangle" style="color: #EF4444;" title="Save failed, retrying..."></i>';
                        }
                    });
            }

            const deletedIds = new Set();

            // Attach row events
            function attachRowListeners(row) {
                const snInput = row.querySelector('.row-sn');
                const macInput = row.querySelector('.row-mac');
                const boxInput = row.querySelector('.row-box');
                let rowDebounce = null;
                let scanBurstTimer = null;

                const inputs = [snInput, macInput, boxInput].filter(Boolean);

                inputs.forEach((input) => {
                    input.addEventListener('input', function() {
                        clearTimeout(rowDebounce);
                        rowDebounce = setTimeout(() => saveRow(row), 500);
                    });

                    input.addEventListener('blur', function() {
                        clearTimeout(rowDebounce);
                        saveRow(row);
                    });

                    // Prevent any form submission or link click on keypress
                    input.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13 || e.which === 13) {
                            e.preventDefault();
                            e.stopPropagation();
                        }
                    });
                });

                // Helper to jump to next row's SN or create a new row
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

                // 1. Serial Number Input handling (Barcode Gun / Keyboard)
                if (snInput) {
                    snInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13 || e.which === 13) {
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                            clearTimeout(rowDebounce);
                            saveRow(row);

                            // Auto-advance focus to MAC Address in the same row
                            if (macInput) {
                                setTimeout(() => {
                                    macInput.focus();
                                    macInput.select();
                                }, 30);
                            }
                            return false;
                        }
                    });
                }

                // 2. MAC Address Input handling (Barcode Gun / Keyboard)
                if (macInput) {
                    macInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13 || e.which === 13) {
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                            clearTimeout(rowDebounce);
                            saveRow(row);

                            // If Box No is empty, move to Box No, otherwise go to next row
                            if (boxInput && !boxInput.value.trim()) {
                                setTimeout(() => {
                                    boxInput.focus();
                                    boxInput.select();
                                }, 30);
                            } else {
                                moveToNextRowOrAddNew(row);
                            }
                            return false;
                        }
                    });
                }

                // 3. Box No Input handling
                if (boxInput) {
                    boxInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13 || e.which === 13) {
                            e.preventDefault();
                            e.stopPropagation();
                            e.stopImmediatePropagation();
                            clearTimeout(rowDebounce);
                            saveRow(row);

                            moveToNextRowOrAddNew(row);
                            return false;
                        }
                    });
                }

                // Delete button
                const delBtn = row.querySelector('.btn-remove-row');
                if (delBtn) {
                    delBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        row.dataset.deleted = 'true';
                        clearTimeout(rowDebounce);

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
                                    if (liveUnitsBadge && data.batch) {
                                        liveUnitsBadge.innerHTML =
                                            `<i class="bi bi-cpu"></i> ${data.batch.total_quantity} Units Encoded`;
                                    }
                                    setSyncStatus('saved');
                                })
                                .catch(err => console.error(err));
                        }

                        row.remove();
                        renumberRows();
                    };
                }
            }

            // Add new row helper
            function addNewRow(autoFocus = true) {
                const emptyPlaceholder = container.querySelector('.empty-row-placeholder');
                if (emptyPlaceholder) emptyPlaceholder.remove();

                const currentCount = container.querySelectorAll('.subtable-row').length + 1;
                const rowDiv = document.createElement('div');
                rowDiv.className = 'subtable-row live-row-new';
                rowDiv.style.cssText = 'grid-template-columns: 40px 2fr 2fr 1.2fr 30px 40px;';

                // Inherit Box No from previous row if available (for easy bulk boxing)
                let defaultBoxNo = '';
                const lastRow = container.querySelector('.subtable-row:last-child');
                if (lastRow) {
                    const lastBox = lastRow.querySelector('.row-box');
                    if (lastBox && lastBox.value.trim()) {
                        defaultBoxNo = lastBox.value.trim();
                    }
                }

                rowDiv.innerHTML = `
                    <input type="hidden" name="item_id[]" class="row-item-id" value="">
                    <div class="subtable-row-num">${currentCount}</div>
                    <div>
                        <input type="text" name="serial_number[]" class="form-control mono row-sn" placeholder="Serial Number (e.g. 48575443F8A...)">
                    </div>
                    <div>
                        <input type="text" name="mac_address[]" class="form-control mono row-mac" placeholder="MAC Address (e.g. 00:1A:2B:...)">
                    </div>
                    <div>
                        <input type="text" name="box_no[]" class="form-control row-box" value="${defaultBoxNo}" placeholder="Box No (e.g. BOX 1)">
                    </div>
                    <div class="row-status-indicator" style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;"></div>
                    <div>
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
                        }, 50);
                    }
                }
            }

            // Renumber row sequence
            function renumberRows() {
                const rows = container.querySelectorAll('.subtable-row');
                rows.forEach((r, idx) => {
                    const num = r.querySelector('.subtable-row-num');
                    if (num) num.textContent = idx + 1;
                });
                if (rows.length === 0) {
                    container.innerHTML =
                        `<div class="empty-row-placeholder" style="padding: 24px; text-align: center; color: var(--dftm-slate);">No serialized units yet. Click "+ Add Item Row" above to start encoding.</div>`;
                }
            }

            // Attach listeners to existing rows
            container.querySelectorAll('.subtable-row').forEach(row => {
                attachRowListeners(row);
            });

            if (addRowBtn) {
                addRowBtn.addEventListener('click', function() {
                    addNewRow(true);
                });
            }

            // Real-time background sync polling every 1.5 seconds
            function pollSync() {
                if (isPolling) return;
                isPolling = true;

                fetch(getItemsUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && Array.isArray(data.items)) {
                            syncDomWithServer(data.items, data.batch);
                        }
                    })
                    .catch(e => console.warn('Sync poll notice:', e))
                    .finally(() => {
                        isPolling = false;
                    });
            }

            function syncDomWithServer(serverItems, batchData) {
                if (liveUnitsBadge && batchData) {
                    liveUnitsBadge.innerHTML =
                        `<i class="bi bi-cpu"></i> ${batchData.total_quantity} Units Encoded`;
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

                // 1. Update or remove existing rows in DOM
                const domRows = container.querySelectorAll('.subtable-row');
                domRows.forEach(row => {
                    const itemIdInput = row.querySelector('.row-item-id');
                    const rowItemId = itemIdInput ? String(itemIdInput.value) : '';

                    if (rowItemId) {
                        if (deletedIds.has(rowItemId) || !serverMap.has(rowItemId)) {
                            // Deleted on another tab or marked deleted locally
                            if (row !== focusedRow) {
                                row.remove();
                            }
                        } else {
                            // Update values if changed on another tab and NOT currently being edited by this user
                            const serverItem = serverMap.get(rowItemId);
                            if (row !== focusedRow) {
                                const snInput = row.querySelector('.row-sn');
                                const macInput = row.querySelector('.row-mac');
                                const boxInput = row.querySelector('.row-box');
                                if (snInput && snInput.value !== serverItem.serial_number) snInput.value =
                                    serverItem.serial_number;
                                if (macInput && macInput.value !== serverItem.mac_address) macInput.value =
                                    serverItem.mac_address;
                                if (boxInput && boxInput.value !== serverItem.box_no) boxInput.value =
                                    serverItem.box_no;
                            }
                            serverMap.delete(rowItemId);
                        }
                    }
                });

                // 2. Add new items from server that are not yet in DOM
                if (serverMap.size > 0) {
                    const emptyPlaceholder = container.querySelector('.empty-row-placeholder');
                    if (emptyPlaceholder) emptyPlaceholder.remove();

                    serverMap.forEach(newItem => {
                        if (deletedIds.has(String(newItem.id))) return;

                        const rowDiv = document.createElement('div');
                        rowDiv.className = 'subtable-row live-row-remote';
                        rowDiv.setAttribute('data-item-id', newItem.id);
                        rowDiv.style.cssText = 'grid-template-columns: 40px 2fr 2fr 1.2fr 30px 40px;';
                        rowDiv.innerHTML = `
                    <input type="hidden" name="item_id[]" class="row-item-id" value="${newItem.id}">
                    <div class="subtable-row-num">${newItem.item_no}</div>
                    <div>
                        <input type="text" name="serial_number[]" class="form-control mono row-sn" value="${newItem.serial_number}" placeholder="Serial Number">
                    </div>
                    <div>
                        <input type="text" name="mac_address[]" class="form-control mono row-mac" value="${newItem.mac_address}" placeholder="MAC Address">
                    </div>
                    <div>
                        <input type="text" name="box_no[]" class="form-control row-box" value="${newItem.box_no}" placeholder="Box No">
                    </div>
                    <div class="row-status-indicator" style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                        <i class="bi bi-check-lg" style="color: #10B981; font-weight: 800;"></i>
                    </div>
                    <div>
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

            // Start Real-Time Live Sync Poller
            setInterval(pollSync, 1500);
        });
    </script>
@endsection
