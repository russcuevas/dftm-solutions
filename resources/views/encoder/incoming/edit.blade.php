@extends('layouts.app')

@section('title', 'Edit Incoming Repair Slip - ' . $transmittal->transmittal_no)
@section('page_title', 'Incoming Barcode Scanner & Repair Slip')

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

        @keyframes shakeRow {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-4px); }
            40%, 80% { transform: translateX(4px); }
        }

        .row-duplicate {
            background-color: #FEF2F2 !important;
            border-left: 4px solid #EF4444 !important;
            animation: shakeRow 0.35s ease-in-out;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
        }

        .row-duplicate .row-sn {
            border-color: #EF4444 !important;
            background-color: #FFF5F5 !important;
            color: #DC2626 !important;
            font-weight: 700;
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
            from {
                transform: scale(0.95);
                opacity: 0;
            }
            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>

    <form action="{{ route('encoder.incoming.update', $transmittal->id) }}" method="POST" id="editIncomingForm">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <div>
                    <div class="card-title" style="display: flex; align-items: center; gap: 10px;">
                        <i class="bi bi-upc-scan"></i> Repair Slip: <span class="mono"
                            style="color: var(--dftm-accent);">{{ $transmittal->transmittal_no }}</span>
                    </div>
                    <div class="card-subtitle">
                        Continuous Barcode Scanning • Batch Encoding • Click 'Save Changes' to update
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <span class="badge badge-stock" id="liveUnitsBadge" style="font-size: 0.92rem; padding: 7px 16px;">
                        <i class="bi bi-cpu"></i> <span id="totalUnitsCount">{{ $transmittal->total_quantity }}</span> Units Scanned
                    </span>
                    <button type="submit" class="btn btn-primary btn-sm" id="btnHeaderSave" style="font-weight: 700; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <i class="bi bi-save-fill"></i> Save Changes
                    </button>
                    <a href="{{ route('encoder.incoming.print', $transmittal->id) }}" target="_blank"
                        class="btn btn-outline btn-sm">
                        <i class="bi bi-printer"></i> Print Report
                    </a>
                    <a href="{{ route('encoder.incoming.show', $transmittal->id) }}" class="btn btn-outline btn-sm">
                        <i class="bi bi-eye"></i> View Transmittal
                    </a>
                    <a href="{{ route('encoder.incoming.index') }}" class="btn btn-outline btn-sm">
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
                            value="{{ old('transmittal_no', $transmittal->transmittal_no) }}" placeholder="e.g. TR-20260915-001"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 700;">Company / Client Name</label>
                        <select name="company_name" class="form-select transmittal-header-input" style="font-weight: 700;">
                            @if ($transmittal->company_name && !$clients->contains('company_name', $transmittal->company_name))
                                <option value="{{ $transmittal->company_name }}" selected>{{ $transmittal->company_name }}
                                    (Current)</option>
                            @endif
                            @foreach ($clients as $c)
                                <option value="{{ $c->company_name }}"
                                    {{ $transmittal->company_name == $c->company_name ? 'selected' : '' }}>
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
                        <label class="form-label">Brand</label>
                        <input type="text" name="brand" id="headerBrandInput" list="brandSuggestions"
                            class="form-control transmittal-header-input" value="{{ old('brand', $transmittal->brand) }}"
                            placeholder="e.g. HUAWEI">
                        <datalist id="brandSuggestions">
                            <option value="HUAWEI">
                            <option value="ZTE">
                            <option value="SKYWORTH">
                            <option value="FIBERHOME">
                            <option value="NOKIA">
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Model</label>
                        <input type="text" name="model" id="headerModelInput" class="form-control transmittal-header-input"
                            value="{{ old('model', $transmittal->model) }}" placeholder="e.g. EG8145V5">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Status (Optional)</label>
                        <input type="text" name="status" list="statusSuggestions" class="form-control transmittal-header-input"
                            value="{{ old('status', $transmittal->status) }}" placeholder="e.g. In process / Received">
                        <datalist id="statusSuggestions">
                            <option value="In process">
                            <option value="Received">
                            <option value="Pending Diagnostic">
                            <option value="Repaired">
                            <option value="BER">
                        </datalist>
                    </div>
                </div>

                <!-- Fast Serial Search Bar & Custom Add Row Bar -->
                <div
                    style="background: #F8FAFC; border: 1px solid var(--dftm-border); border-radius: 8px; padding: 12px 16px; margin: 16px 0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;">
                    <!-- Custom Row Generator: input e.g. 100 rows -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-weight: 700; font-size: 0.85rem; color: var(--dftm-navy);">
                            <i class="bi bi-grid-plus"></i> Generate Blank Scan Rows:
                        </span>
                        <input type="number" id="customRowQtyInput" class="form-control"
                            style="width: 85px; text-align: center; font-weight: 700;" value="100" min="1"
                            max="1000">
                        <button type="button" class="btn btn-accent btn-sm" id="btnGenerateCustomRows"
                            style="font-weight: 700;">
                            <i class="bi bi-plus-circle-fill"></i> Add Rows
                        </button>
                        <button type="button" class="btn btn-outline btn-sm" id="btnLiveAddSingleRow" title="Add 1 row">
                            +1 Row
                        </button>
                    </div>

                    <!-- Quick Serial Lookup tool -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="position: relative;">
                            <input type="text" id="quickSerialSearchInput" class="form-control mono"
                                style="width: 260px; font-size: 0.85rem;" placeholder="Search serial / check duplicate...">
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
                                Itapat ang barcode gun sa <strong>Serial Number</strong>. Kusa itong lilipat sa
                                <strong>MAC</strong> o sa <strong>susunod na row</strong> nang tuloy-tuloy. I-click ang <strong>Save Changes</strong> kapag tapos na.
                            </div>
                        </div>
                    </div>

                    <!-- Column Headers -->
                    <div
                        style="display: grid; grid-template-columns: 40px 2fr 2fr 1.2fr 1.2fr 30px 40px; gap: 8px; padding: 8px 12px; font-size: 0.74rem; font-weight: 800; color: var(--dftm-navy); text-transform: uppercase; background: #F1F5F9; border-radius: 6px 6px 0 0; align-items: end;">
                        <div style="text-align: center; padding-bottom: 5px;">NO.</div>
                        <div style="padding-bottom: 5px;">Serial Number (Scan Barcode)</div>
                        <div style="padding-bottom: 5px;">MAC Address</div>
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px; flex-wrap: wrap; gap: 2px;">
                                <span>Model</span>
                                <label style="display: inline-flex; align-items: center; gap: 4px; font-size: 0.68rem; font-weight: 700; text-transform: none; cursor: pointer; color: var(--dftm-navy); margin-bottom: 0; user-select: none;" title="I-check para ma-update ang lahat ng rows">
                                    <input type="checkbox" name="apply_model_to_all" id="syncModelCheckbox" value="1"
                                        style="cursor: pointer; width: 13px; height: 13px; accent-color: var(--dftm-accent);">
                                    <span>Select All</span>
                                </label>
                            </div>
                            <div style="display: flex; gap: 4px; align-items: center;">
                                <input type="text" id="tableBatchModelInput" class="form-control"
                                    style="font-size: 0.75rem; padding: 3px 6px; height: 26px; text-transform: none; font-weight: 600; min-width: 0;"
                                    placeholder="Type model..." value="{{ old('model', $transmittal->model) }}">
                                <button type="button" id="btnUpdateAllModels" class="btn btn-outline btn-sm"
                                    style="font-size: 0.68rem; padding: 2px 7px; height: 26px; white-space: nowrap; font-weight: 700; border-color: var(--dftm-accent); color: var(--dftm-accent); display: inline-flex; align-items: center; gap: 3px;"
                                    title="I-apply ang model sa lahat ng rows">
                                    <i class="bi bi-arrow-repeat"></i> Update
                                </button>
                            </div>
                            <div id="modelUpdateNotice" style="display: none; font-size: 0.68rem; color: #16A34A; font-weight: 700; text-transform: none; margin-top: 2px;">
                                <i class="bi bi-check-circle-fill"></i> <span id="modelUpdateNoticeText">Na-update lahat!</span>
                            </div>
                        </div>
                        <div style="padding-bottom: 5px;">Brand</div>
                        <div style="text-align: center; padding-bottom: 5px;"></div>
                        <div style="text-align: center; padding-bottom: 5px;">Action</div>
                    </div>

                    <div id="subtableRows">
                        @forelse($transmittal->items as $item)
                            <div class="subtable-row" data-item-id="{{ $item->id }}"
                                style="grid-template-columns: 40px 2fr 2fr 1.2fr 1.2fr 30px 40px; gap: 8px;">
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
                                        value="{{ $item->model ?: $transmittal->model }}" placeholder="Model">
                                </div>
                                <div>
                                    <input type="text" name="row_brand[]" list="brandSuggestions" class="form-control row-brand"
                                        value="{{ $item->brand ?: $transmittal->brand }}" placeholder="Brand">
                                </div>
                                <div class="row-status-indicator"
                                    style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                    @if($item->serial_number)
                                        <i class="bi bi-check-lg" style="color: #10B981; font-weight: 800;"></i>
                                    @endif
                                </div>
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <button type="button" class="btn btn-outline btn-icon btn-remove-row" title="Delete Row"
                                        style="color: #DC2626; border-color: #FECACA;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="subtable-row"
                                style="grid-template-columns: 40px 2fr 2fr 1.2fr 1.2fr 30px 40px; gap: 8px;">
                                <input type="hidden" name="item_id[]" class="row-item-id" value="">
                                <div class="subtable-row-num">1</div>
                                <div><input type="text" name="serial_number[]" class="form-control mono row-sn"
                                        placeholder="Scan Serial Number"></div>
                                <div><input type="text" name="mac_address[]" class="form-control mono row-mac"
                                        placeholder="Scan MAC Address"></div>
                                <div><input type="text" name="row_model[]" class="form-control row-model"
                                        value="{{ $transmittal->model }}" placeholder="Model"></div>
                                <div><input type="text" name="row_brand[]" list="brandSuggestions" class="form-control row-brand"
                                        value="{{ $transmittal->brand }}" placeholder="Brand"></div>
                                <div class="row-status-indicator"
                                    style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                </div>
                                <div style="display: flex; align-items: center; justify-content: center;">
                                    <button type="button" class="btn btn-outline btn-icon btn-remove-row"
                                        style="color: #DC2626;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Bottom Add Row Toolbar (No need to scroll back to top) -->
                    <div style="background: #F8FAFC; border-top: 1px solid var(--dftm-border); padding: 12px 16px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; border-radius: 0 0 6px 6px;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span style="font-weight: 700; font-size: 0.85rem; color: var(--dftm-navy);">
                                <i class="bi bi-plus-square-fill" style="color: var(--dftm-accent);"></i> Add Rows (Ilalim):
                            </span>
                            <button type="button" class="btn btn-primary btn-sm btn-add-row-bottom" data-qty="1" style="font-weight: 700;">
                                <i class="bi bi-plus-circle"></i> +1 Row
                            </button>
                            <button type="button" class="btn btn-outline btn-sm btn-add-row-bottom" data-qty="5">
                                +5 Rows
                            </button>
                            <button type="button" class="btn btn-outline btn-sm btn-add-row-bottom" data-qty="10">
                                +10 Rows
                            </button>
                            <button type="button" class="btn btn-outline btn-sm btn-add-row-bottom" data-qty="50">
                                +50 Rows
                            </button>
                            <button type="button" class="btn btn-outline btn-sm btn-add-row-bottom" data-qty="100">
                                +100 Rows
                            </button>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 0.82rem; color: var(--dftm-slate);">
                                Kabuuang Rows: <strong id="bottomTotalRowsBadge" style="color: var(--dftm-navy); font-size: 0.9rem;">0</strong>
                            </span>
                        </div>
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
                    <i class="bi bi-info-circle"></i> I-click ang <strong>Save Changes</strong> upang ma-save ang lahat ng na-encode na rows sa database.
                </div>
                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn btn-primary" id="btnFooterSave">
                        <i class="bi bi-save-fill"></i> Save Changes
                    </button>
                    <a href="{{ route('encoder.incoming.index') }}" class="btn btn-outline">
                        Cancel / Back to List
                    </a>
                </div>
            </div>
        </div>
    </form>

    <!-- Duplicate & Serial Status Inspector Modal -->
    <div class="lookup-modal" id="serialInspectorModal">
        <div class="lookup-modal-content">
            <div
                style="background: #00205B; color: #FFFFFF; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between;">
                <div style="font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-info-circle-fill" style="color: #38BDF8;"></i> Serial Number Status & History
                </div>
                <button type="button" id="btnCloseInspectorModal"
                    style="background: transparent; border: none; color: #FFFFFF; font-size: 1.2rem; cursor: pointer;">
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

            <div
                style="background: #F8FAFC; border-top: 1px solid var(--dftm-border); padding: 12px 20px; text-align: right;">
                <button type="button" class="btn btn-outline btn-sm" id="btnCloseInspectorFooter">Close</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchSerialUrl = '{{ route('encoder.incoming.searchSerial') }}';
            const updateModelAllUrl = '{{ route('encoder.incoming.updateModelAll', $transmittal->id) }}';
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                              document.querySelector('input[name="_token"]')?.value;
            const container = document.getElementById('subtableRows');
            const totalUnitsCount = document.getElementById('totalUnitsCount');
            const btnGenerateCustomRows = document.getElementById('btnGenerateCustomRows');
            const customRowQtyInput = document.getElementById('customRowQtyInput');
            const btnLiveAddSingleRow = document.getElementById('btnLiveAddSingleRow');
            const modal = document.getElementById('serialInspectorModal');
            const btnCloseInspectorModal = document.getElementById('btnCloseInspectorModal');
            const btnCloseInspectorFooter = document.getElementById('btnCloseInspectorFooter');
            const inspectorLoading = document.getElementById('inspectorLoading');
            const inspectorContent = document.getElementById('inspectorContent');
            const quickSerialSearchInput = document.getElementById('quickSerialSearchInput');
            const btnSearchSerialModal = document.getElementById('btnSearchSerialModal');
            const headerBrandInput = document.getElementById('headerBrandInput');
            const headerModelInput = document.getElementById('headerModelInput');
            const tableBatchModelInput = document.getElementById('tableBatchModelInput');
            const syncModelCheckbox = document.getElementById('syncModelCheckbox');
            const btnUpdateAllModels = document.getElementById('btnUpdateAllModels');
            const modelUpdateNotice = document.getElementById('modelUpdateNotice');
            const modelUpdateNoticeText = document.getElementById('modelUpdateNoticeText');

            // Update row numbers and calculate total filled units count & duplicate check
            function updateRowNumbersAndCount() {
                const rows = container.querySelectorAll('.subtable-row');
                let count = 0;
                const serialMap = {};

                rows.forEach((r, idx) => {
                    const num = r.querySelector('.subtable-row-num');
                    if (num) num.textContent = idx + 1;

                    const sn = r.querySelector('.row-sn')?.value.trim();
                    const mac = r.querySelector('.row-mac')?.value.trim();
                    if (sn || mac) {
                        count++;
                    }
                    if (sn) {
                        serialMap[sn] = (serialMap[sn] || 0) + 1;
                    }
                });

                rows.forEach(r => {
                    const snInput = r.querySelector('.row-sn');
                    const statusIndicator = r.querySelector('.row-status-indicator');
                    const sn = snInput?.value.trim();

                    if (sn && serialMap[sn] > 1) {
                        r.classList.add('row-duplicate');
                        if (statusIndicator) {
                            statusIndicator.innerHTML = '<span class="badge badge-ber" style="font-size: 0.65rem; padding: 2px 5px;" title="Duplicate serial in this form">DUP</span>';
                        }
                    } else {
                        r.classList.remove('row-duplicate');
                        if (statusIndicator) {
                            const hasSn = sn && sn.length > 0;
                            statusIndicator.innerHTML = hasSn ? '<i class="bi bi-check-lg" style="color: #10B981; font-weight: 800;"></i>' : '';
                        }
                    }
                });

                if (totalUnitsCount) {
                    totalUnitsCount.textContent = count;
                }

                const bottomTotalRowsBadge = document.getElementById('bottomTotalRowsBadge');
                if (bottomTotalRowsBadge) {
                    bottomTotalRowsBadge.textContent = rows.length;
                }
            }

            // Save model directly to database via AJAX
            function saveModelToDatabaseViaAjax(modelVal) {
                if (!btnUpdateAllModels) return;

                const originalBtnHtml = btnUpdateAllModels.innerHTML;
                btnUpdateAllModels.disabled = true;
                btnUpdateAllModels.innerHTML = '<i class="bi bi-arrow-repeat spin-icon"></i> Saving...';

                fetch(updateModelAllUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ model: modelVal })
                })
                .then(res => res.json())
                .then(data => {
                    btnUpdateAllModels.disabled = false;
                    if (data.success) {
                        btnUpdateAllModels.innerHTML = '<i class="bi bi-check2"></i> Saved';
                        btnUpdateAllModels.style.borderColor = '#16A34A';
                        btnUpdateAllModels.style.color = '#16A34A';

                        if (modelUpdateNotice && modelUpdateNoticeText) {
                            modelUpdateNoticeText.innerHTML = `<strong>Saved to database!</strong> Na-save sa DB ang model "${modelVal}" (${data.affected_count || 'lahat ng'} items).`;
                            modelUpdateNotice.style.display = 'block';
                            setTimeout(() => {
                                modelUpdateNotice.style.display = 'none';
                            }, 5000);
                        }

                        setTimeout(() => {
                            btnUpdateAllModels.innerHTML = originalBtnHtml;
                            btnUpdateAllModels.style.borderColor = '';
                            btnUpdateAllModels.style.color = '';
                        }, 2500);
                    } else {
                        btnUpdateAllModels.innerHTML = originalBtnHtml;
                        alert(data.message || 'May error sa pag-save ng model sa database.');
                    }
                })
                .catch(err => {
                    btnUpdateAllModels.disabled = false;
                    btnUpdateAllModels.innerHTML = originalBtnHtml;
                    console.error('AJAX model update failed:', err);
                    alert('Hindi ma-save ang model sa database. Pakisuri ang network o koneksyon.');
                });
            }

            // Apply model to all unit rows (UI and optionally DB via AJAX)
            function applyModelToAllRows(saveToDb = false) {
                const modelVal = tableBatchModelInput ? tableBatchModelInput.value.trim() : (headerModelInput ? headerModelInput.value.trim() : '');

                // Keep header model in sync as well
                if (headerModelInput && tableBatchModelInput && headerModelInput.value !== modelVal) {
                    headerModelInput.value = modelVal;
                }

                const rowModelInputs = container.querySelectorAll('.row-model');

                rowModelInputs.forEach(input => {
                    input.value = modelVal;
                    input.style.transition = 'background-color 0.3s';
                    input.style.backgroundColor = '#E0F2FE';
                    setTimeout(() => {
                        input.style.backgroundColor = '';
                    }, 600);
                });

                if (saveToDb && modelVal !== '') {
                    saveModelToDatabaseViaAjax(modelVal);
                }
            }

            // Sync header Brand change to rows
            if (headerBrandInput) {
                headerBrandInput.addEventListener('input', function() {
                    const val = this.value;
                    container.querySelectorAll('.row-brand').forEach(input => {
                        input.value = val;
                    });
                });
            }

            // Sync top header model to batch model input
            if (headerModelInput) {
                headerModelInput.addEventListener('input', function() {
                    if (tableBatchModelInput && tableBatchModelInput.value !== this.value) {
                        tableBatchModelInput.value = this.value;
                    }
                    if (syncModelCheckbox && syncModelCheckbox.checked) {
                        applyModelToAllRows(false);
                    }
                });
            }

            // Batch model input typing & Enter key trigger
            if (tableBatchModelInput) {
                tableBatchModelInput.addEventListener('input', function() {
                    if (headerModelInput && headerModelInput.value !== this.value) {
                        headerModelInput.value = this.value;
                    }
                    if (syncModelCheckbox && syncModelCheckbox.checked) {
                        applyModelToAllRows(false);
                    }
                });

                tableBatchModelInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (syncModelCheckbox) {
                            syncModelCheckbox.checked = true;
                        }
                        applyModelToAllRows(true);
                    }
                });
            }

            // Click Update button: check checkbox, apply to rows, and save directly to DB via AJAX!
            if (btnUpdateAllModels) {
                btnUpdateAllModels.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (syncModelCheckbox) {
                        syncModelCheckbox.checked = true;
                    }
                    applyModelToAllRows(true);
                });
            }

            // When checkbox is toggled on, if model has value, apply to rows and save to DB
            if (syncModelCheckbox) {
                syncModelCheckbox.addEventListener('change', function() {
                    const modelVal = tableBatchModelInput ? tableBatchModelInput.value.trim() : (headerModelInput ? headerModelInput.value.trim() : '');
                    if (this.checked && modelVal !== '') {
                        applyModelToAllRows(true);
                    }
                });
            }

            // Ensure individual row models remain freely editable:
            // If user manually edits a specific row's model, uncheck syncModelCheckbox
            // so subsequent typing won't overwrite their custom edit.
            container.addEventListener('input', function(e) {
                if (e.target && e.target.classList.contains('row-model')) {
                    if (syncModelCheckbox && syncModelCheckbox.checked) {
                        syncModelCheckbox.checked = false;
                    }
                }
            });

            // Create a row DOM element purely client-side
            function createBlankRowElement(itemNo) {
                const rowDiv = document.createElement('div');
                rowDiv.className = 'subtable-row';
                rowDiv.style.cssText = 'grid-template-columns: 40px 2fr 2fr 1.2fr 1.2fr 30px 40px; gap: 8px;';

                const brandVal = headerBrandInput?.value || '';
                const modelVal = tableBatchModelInput?.value || headerModelInput?.value || '';

                rowDiv.innerHTML = `
                    <input type="hidden" name="item_id[]" class="row-item-id" value="">
                    <div class="subtable-row-num">${itemNo}</div>
                    <div>
                        <input type="text" name="serial_number[]" class="form-control mono row-sn" placeholder="Scan Serial Number">
                    </div>
                    <div>
                        <input type="text" name="mac_address[]" class="form-control mono row-mac" placeholder="Scan MAC Address">
                    </div>
                    <div>
                        <input type="text" name="row_model[]" class="form-control row-model" value="${modelVal}" placeholder="Model">
                    </div>
                    <div>
                        <input type="text" name="row_brand[]" list="brandSuggestions" class="form-control row-brand" value="${brandVal}" placeholder="Brand">
                    </div>
                    <div class="row-status-indicator" style="display: flex; align-items: center; justify-content: center; font-size: 0.9rem;"></div>
                    <div style="display: flex; align-items: center; justify-content: center;">
                        <button type="button" class="btn btn-outline btn-icon btn-remove-row" title="Delete Row" style="color: #DC2626; border-color: #FECACA;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                `;

                return rowDiv;
            }

            // Client-side row addition
            function addBlankRows(quantity = 1, autoFocus = false) {
                const currentRowsCount = container.querySelectorAll('.subtable-row').length;
                const fragment = document.createDocumentFragment();

                for (let i = 0; i < quantity; i++) {
                    const rowDiv = createBlankRowElement(currentRowsCount + i + 1);
                    attachRowListeners(rowDiv);
                    fragment.appendChild(rowDiv);
                }

                container.appendChild(fragment);
                updateRowNumbersAndCount();

                if (autoFocus) {
                    const allRows = container.querySelectorAll('.subtable-row');
                    const targetRow = allRows[currentRowsCount];
                    if (targetRow) {
                        const sn = targetRow.querySelector('.row-sn');
                        if (sn) {
                            sn.focus();
                            sn.select();
                        }
                    }
                }
            }

            // Barcode continuous navigation helper
            function moveToNextRowOrAddNew(currentRow) {
                const nextRow = currentRow.nextElementSibling;
                if (nextRow && nextRow.classList.contains('subtable-row')) {
                    const nextSn = nextRow.querySelector('.row-sn');
                    if (nextSn) {
                        nextSn.focus();
                        nextSn.select();
                    }
                } else {
                    addBlankRows(1, true);
                }
            }

            // Attach listeners to row inputs
            function attachRowListeners(row) {
                const snInput = row.querySelector('.row-sn');
                const macInput = row.querySelector('.row-mac');
                const statusIndicator = row.querySelector('.row-status-indicator');
                const delBtn = row.querySelector('.btn-remove-row');

                function updateStatus() {
                    const hasSn = snInput && snInput.value.trim().length > 0;
                    if (statusIndicator) {
                        statusIndicator.innerHTML = hasSn ? '<i class="bi bi-check-lg" style="color: #10B981; font-weight: 800;"></i>' : '';
                    }
                    updateRowNumbersAndCount();
                }

                if (snInput) {
                    snInput.addEventListener('input', updateStatus);

                    snInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            e.stopPropagation();

                            updateStatus();

                            if (macInput) {
                                macInput.focus();
                                macInput.select();
                            } else {
                                moveToNextRowOrAddNew(row);
                            }
                        }
                    });
                }

                if (macInput) {
                    macInput.addEventListener('input', updateStatus);

                    macInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.keyCode === 13) {
                            e.preventDefault();
                            e.stopPropagation();

                            updateStatus();
                            moveToNextRowOrAddNew(row);
                        }
                    });
                }

                if (delBtn) {
                    delBtn.onclick = function(e) {
                        e.preventDefault();
                        e.stopPropagation();

                        const itemIdVal = row.querySelector('.row-item-id')?.value;
                        if (itemIdVal) {
                            const delInput = document.createElement('input');
                            delInput.type = 'hidden';
                            delInput.name = 'deleted_item_ids[]';
                            delInput.value = itemIdVal;
                            document.getElementById('editIncomingForm').appendChild(delInput);
                        }

                        const allRows = container.querySelectorAll('.subtable-row');
                        if (allRows.length === 1) {
                            if (snInput) snInput.value = '';
                            if (macInput) macInput.value = '';
                            const itemId = row.querySelector('.row-item-id');
                            if (itemId) itemId.value = '';
                            updateStatus();
                            return;
                        }

                        row.remove();
                        updateRowNumbersAndCount();
                    };
                }
            }

            // Attach listeners to initially rendered rows
            container.querySelectorAll('.subtable-row').forEach(row => attachRowListeners(row));
            updateRowNumbersAndCount();

            // Button to generate bulk rows
            if (btnGenerateCustomRows) {
                btnGenerateCustomRows.addEventListener('click', function() {
                    const count = parseInt(customRowQtyInput.value, 10) || 10;
                    if (count <= 0) return;
                    addBlankRows(count, true);
                });
            }

            if (btnLiveAddSingleRow) {
                btnLiveAddSingleRow.addEventListener('click', function() {
                    addBlankRows(1, true);
                });
            }

            // Bottom Add Row toolbar buttons
            document.querySelectorAll('.btn-add-row-bottom').forEach(btn => {
                btn.addEventListener('click', function() {
                    const qty = parseInt(this.getAttribute('data-qty'), 10) || 1;
                    addBlankRows(qty, true);
                });
            });

            // Quick Serial Lookup Tool
            window.openInspectorFor = function(serial) {
                if (!serial) return;
                modal.classList.add('active');
                inspectorLoading.style.display = 'block';
                inspectorContent.innerHTML = '';

                fetch(`${searchSerialUrl}?serial=${encodeURIComponent(serial)}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
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

                        let html =
                            `<div style="margin-bottom: 12px; font-weight: 800; font-size: 0.95rem; color: var(--dftm-navy);">Found ${data.items.length} Occurrence(s) for "${serial}":</div>`;
                        data.items.forEach((it) => {
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
                    .catch(() => {
                        inspectorLoading.style.display = 'none';
                        inspectorContent.innerHTML =
                            `<div style="color: #EF4444; padding: 16px;">Failed to load records.</div>`;
                    });
            };

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
        });
    </script>
@endsection
