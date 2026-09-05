/**
 * DFTM SOLUTIONS - CORE CLIENT JAVASCRIPT
 * Dynamic Subtable, Bulk Paste, Real-time Counts, and Modals
 */

document.addEventListener('DOMContentLoaded', function () {
    initSidebarToggle();
    initSubtableManager();
    initBulkPasteModal();
    initModalHandlers();
    initLiveClock();
    initOutgoingSelector();
    initTableSearch();
});

/**
 * Sidebar Collapse / Expand Toggle with localStorage persistence
 */
function initSidebarToggle() {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.app-sidebar');
    if (!toggleBtn) return;

    // Check saved preference for desktop
    if (window.innerWidth > 992) {
        const isCollapsed = localStorage.getItem('dftm_sidebar_collapsed') === 'true';
        if (isCollapsed) {
            document.body.classList.add('sidebar-collapsed');
        }
    }

    toggleBtn.addEventListener('click', function () {
        if (window.innerWidth <= 992) {
            if (sidebar) sidebar.classList.toggle('active');
        } else {
            document.body.classList.toggle('sidebar-collapsed');
            const state = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('dftm_sidebar_collapsed', state);
        }
    });

    // Close mobile sidebar on backdrop click
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 992 && sidebar && sidebar.classList.contains('active')) {
            if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        }
    });
}

/**
 * Real-time clock in Manila Timezone (Asia/Manila)
 */
function initLiveClock() {
    const clockEl = document.getElementById('liveClock');
    if (!clockEl) return;

    function updateTime() {
        const now = new Date();
        const options = {
            timeZone: 'Asia/Manila',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        clockEl.textContent = new Intl.DateTimeFormat('en-US', options).format(now);
    }

    updateTime();
    setInterval(updateTime, 1000);
}

/**
 * Dynamic Subtable Row Adder & Counter for Incoming Repair Slips
 */
function initSubtableManager() {
    const container = document.getElementById('subtableRows');
    const addBtn = document.getElementById('addRowBtn');
    const totalQtyInput = document.getElementById('totalQuantityInput');
    const totalQtyBadge = document.getElementById('totalQtyBadge');

    if (!container) return;
    // Skip on live real-time edit pages which manage their own deletion & sync via API
    if (document.getElementById('btnLiveAddRow')) return;

    function updateRowNumbersAndCount() {
        const rows = container.querySelectorAll('.subtable-row');
        let activeCount = 0;

        rows.forEach((row, index) => {
            const numEl = row.querySelector('.subtable-row-num');
            if (numEl) numEl.textContent = index + 1;

            const snInput = row.querySelector('input[name="serial_number[]"]');
            const macInput = row.querySelector('input[name="mac_address[]"]');
            
            if (snInput && (snInput.value.trim() !== '' || (macInput && macInput.value.trim() !== ''))) {
                activeCount++;
            }
        });

        // If user has rows created, set total quantity to rows count or active items
        const count = rows.length;
        if (totalQtyInput) totalQtyInput.value = count;
        if (totalQtyBadge) totalQtyBadge.textContent = count + ' Units';
    }

    if (addBtn) {
        addBtn.addEventListener('click', function () {
            addNewRow();
        });
    }

    // Delegate remove button clicks and inputs
    container.addEventListener('click', function (e) {
        if (e.target.closest('.btn-remove-row')) {
            const row = e.target.closest('.subtable-row');
            if (container.querySelectorAll('.subtable-row').length > 1) {
                row.remove();
                updateRowNumbersAndCount();
            } else {
                // Clear the only row instead of deleting
                row.querySelectorAll('input').forEach(input => input.value = '');
                updateRowNumbersAndCount();
            }
        }
    });

    container.addEventListener('input', function (e) {
        if (e.target.name === 'serial_number[]' || e.target.name === 'mac_address[]') {
            updateRowNumbersAndCount();
        }
    });

    function addNewRow(serial = '', mac = '', box = '') {
        const isOutgoingEdit = !!container.querySelector('input[name="box_no_item[]"], input[name="item_id[]"]');
        const boxInputName = isOutgoingEdit ? 'box_no_item[]' : 'box_no[]';
        const hiddenItemId = isOutgoingEdit ? '<input type="hidden" name="item_id[]" value="">' : '';

        const rowCount = container.querySelectorAll('.subtable-row').length + 1;
        const row = document.createElement('div');
        row.className = 'subtable-row';
        row.style.gridTemplateColumns = '45px 2fr 2fr 1.2fr 45px';
        row.innerHTML = `
            ${hiddenItemId}
            <div class="subtable-row-num">${rowCount}</div>
            <div>
                <input type="text" name="serial_number[]" class="form-control mono" placeholder="Serial Number" value="${escapeHtml(serial)}">
            </div>
            <div>
                <input type="text" name="mac_address[]" class="form-control mono" placeholder="MAC Address" value="${escapeHtml(mac)}">
            </div>
            <div>
                <input type="text" name="${boxInputName}" class="form-control" placeholder="Box No" value="${escapeHtml(box)}">
            </div>
            <div style="text-align: center;">
                <button type="button" class="btn btn-outline btn-icon btn-remove-row" title="Remove Row" style="color: #DC2626; border-color: #FECACA;">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        updateRowNumbersAndCount();
    }

    window.addNewSubtableRow = addNewRow;
}

/**
 * Bulk Paste Tool for rapid batch entry of serials & MAC addresses
 */
function initBulkPasteModal() {
    const modal = document.getElementById('bulkPasteModal');
    const openBtn = document.getElementById('openBulkPasteBtn');
    const applyBtn = document.getElementById('applyBulkPasteBtn');
    const textarea = document.getElementById('bulkPasteText');
    const container = document.getElementById('subtableRows');

    if (!modal || !openBtn || !applyBtn || !textarea || !container) return;

    openBtn.addEventListener('click', function () {
        textarea.value = '';
        modal.classList.add('active');
    });

    applyBtn.addEventListener('click', function () {
        const text = textarea.value.trim();
        if (!text) {
            modal.classList.remove('active');
            return;
        }

        const lines = text.split(/\r?\n/);
        
        // Remove empty default rows if they have no serials
        const existingRows = container.querySelectorAll('.subtable-row');
        if (existingRows.length === 1) {
            const sn = existingRows[0].querySelector('input[name="serial_number[]"]')?.value.trim();
            const mac = existingRows[0].querySelector('input[name="mac_address[]"]')?.value.trim();
            if (!sn && !mac) {
                existingRows[0].remove();
            }
        }

        lines.forEach(line => {
            const trimmed = line.trim();
            if (!trimmed) return;

            // Handle tab-separated or comma-separated or space-separated serial and MAC
            let parts = trimmed.split(/[\t,;]+/);
            if (parts.length === 1) {
                parts = trimmed.split(/\s{2,}/); // 2 or more spaces
            }

            const serial = parts[0] ? parts[0].trim() : '';
            const mac = parts[1] ? parts[1].trim() : '';
            const box = parts[2] ? parts[2].trim() : '';

            window.addNewSubtableRow(serial, mac, box);
        });

        modal.classList.remove('active');
    });
}

/**
 * Outgoing Item Selector Checkbox Manager with Batch Filter Dropdown
 */
function initOutgoingSelector() {
    const selectAllCheckbox = document.getElementById('selectAllUnits');
    const itemCheckboxes = document.querySelectorAll('.item-select-checkbox');
    const selectedCountBadge = document.getElementById('selectedUnitsCount');
    const totalQtyInput = document.getElementById('outgoingTotalQty');
    const batchFilter = document.getElementById('batchFilterSelector');
    const btnSelectAllBatch = document.getElementById('btnSelectAllBatch');
    const btnDeselectAll = document.getElementById('btnDeselectAll');
    const companyInput = document.getElementById('outgoingCompany');
    const dateDeliveredInput = document.querySelector('input[name="date_delivered"]');
    const table = document.getElementById('availableItemsTable');

    if (!itemCheckboxes.length) return;

    function updateSelectedSummary() {
        const checked = document.querySelectorAll('.item-select-checkbox:checked');
        const count = checked.length;

        if (selectedCountBadge) selectedCountBadge.textContent = count + ' Selected';
        if (totalQtyInput) totalQtyInput.value = count;

        // Highlight selected rows
        itemCheckboxes.forEach(cb => {
            const tr = cb.closest('tr');
            if (tr) {
                if (cb.checked) {
                    tr.style.backgroundColor = '#EFF6FF';
                } else {
                    tr.style.backgroundColor = '';
                }
            }
        });

        // Update selectAllCheckbox state based on visible rows
        if (selectAllCheckbox) {
            const visibleCheckboxes = Array.from(itemCheckboxes).filter(cb => {
                const tr = cb.closest('tr');
                return tr && tr.style.display !== 'none';
            });
            const allVisibleChecked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
            selectAllCheckbox.checked = allVisibleChecked;
        }
    }

    // Filter rows by selected Batch Dropdown
    function filterByBatch(batchId) {
        if (!table) return;
        const rows = table.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const rowBatchId = row.dataset.batchId;
            if (batchId === 'all' || !batchId) {
                row.style.display = '';
            } else {
                if (rowBatchId === String(batchId)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });

        // When switching batch, update the select-all checkbox for visible items
        updateSelectedSummary();
    }

    if (batchFilter) {
        batchFilter.addEventListener('change', function () {
            const batchId = this.value;
            filterByBatch(batchId);
        });

        // Trigger on load if pre-selected
        if (batchFilter.value && batchFilter.value !== 'all') {
            filterByBatch(batchFilter.value);
        }
    }

    // Select All Visible Rows (checks ONLY rows in current filtered batch)
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function () {
            const isChecked = selectAllCheckbox.checked;
            itemCheckboxes.forEach(cb => {
                const tr = cb.closest('tr');
                if (tr && tr.style.display !== 'none') {
                    cb.checked = isChecked;
                }
            });
            updateSelectedSummary();
        });
    }

    // Quick Button: Select All in Batch
    if (btnSelectAllBatch) {
        btnSelectAllBatch.addEventListener('click', function () {
            itemCheckboxes.forEach(cb => {
                const tr = cb.closest('tr');
                if (tr && tr.style.display !== 'none') {
                    cb.checked = true;
                }
            });
            updateSelectedSummary();
        });
    }

    // Quick Button: Deselect All
    if (btnDeselectAll) {
        btnDeselectAll.addEventListener('click', function () {
            itemCheckboxes.forEach(cb => {
                cb.checked = false;
            });
            if (selectAllCheckbox) selectAllCheckbox.checked = false;
            updateSelectedSummary();
        });
    }

    itemCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedSummary);
    });

    updateSelectedSummary();
}

/**
 * Table Search & Filter
 */
function initTableSearch() {
    const searchInputs = document.querySelectorAll('[data-table-search]');
    searchInputs.forEach(input => {
        const tableId = input.dataset.tableSearch;
        const table = document.getElementById(tableId);
        if (!table) return;

        input.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    });
}

/**
 * Generic Modal Backdrop & Triggers
 */
function initModalHandlers() {
    document.querySelectorAll('[data-modal-open]').forEach(btn => {
        btn.addEventListener('click', function () {
            const modalId = this.dataset.modalOpen;
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.add('active');
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach(btn => {
        btn.addEventListener('click', function () {
            const modal = this.closest('.modal-backdrop');
            if (modal) modal.classList.remove('active');
        });
    });

    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });
}

window.openItemEditModal = function (item) {
    const modal = document.getElementById('itemEditModal');
    const form = document.getElementById('itemEditForm');
    if (!modal || !form) return;

    // Set Form Action
    form.action = form.dataset.baseAction.replace('__ID__', item.id);

    // Populate Fields safely
    if (document.getElementById('editItemId')) document.getElementById('editItemId').value = item.id;
    if (document.getElementById('editSerialNumber')) document.getElementById('editSerialNumber').value = item.serial_number || '';
    if (document.getElementById('editMacAddress')) document.getElementById('editMacAddress').value = item.mac_address || '';
    if (document.getElementById('editBrand')) document.getElementById('editBrand').value = item.brand || '';
    if (document.getElementById('editModel')) document.getElementById('editModel').value = item.model || '';
    if (document.getElementById('editBoxNo')) document.getElementById('editBoxNo').value = item.box_no || '';
    if (document.getElementById('editDiagnostic')) document.getElementById('editDiagnostic').value = item.technical_diagnostic || '';
    if (document.getElementById('editReplaceParts')) document.getElementById('editReplaceParts').value = item.replace_parts || '';
    if (document.getElementById('editRepairStatus')) document.getElementById('editRepairStatus').value = item.repair_status || 'In process';
    if (document.getElementById('editStockStatus')) document.getElementById('editStockStatus').value = item.stock_status || 'IN_STOCK';
    if (document.getElementById('editNotes')) document.getElementById('editNotes').value = item.notes || '';

    modal.classList.add('active');
};

/**
 * Open User Edit Modal with Data
 */
window.openUserEditModal = function (user) {
    const modal = document.getElementById('userEditModal');
    const form = document.getElementById('userEditForm');
    if (!modal || !form) return;

    form.action = form.dataset.baseAction.replace('__ID__', user.id);

    document.getElementById('editUserId').value = user.id;
    document.getElementById('editUserName').value = user.name || '';
    document.getElementById('editUserUsername').value = user.username || '';
    document.getElementById('editUserEmail').value = user.email || '';
    document.getElementById('editUserPhone').value = user.phone || '';
    document.getElementById('editUserRole').value = user.role || 'encoder';
    document.getElementById('editUserStatus').value = user.status || 'active';

    modal.classList.add('active');
};

function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
