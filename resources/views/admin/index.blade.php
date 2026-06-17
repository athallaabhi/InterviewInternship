@extends('layouts.app')

@section('title', 'Admin Panel — Adaro Emission Tracker')

@section('content')
<h1 class="page-title">Admin Panel</h1>
<p class="page-subtitle">Manage emission types, categories, input fields, and coefficient values.</p>

<div class="tabs">
    <button class="tab-btn active" onclick="switchTab('emission-types')">Emission Types</button>
    <button class="tab-btn" onclick="switchTab('categories')">Categories & Values</button>
    <button class="tab-btn" onclick="switchTab('input-fields')">Input Fields</button>
    <button class="tab-btn" onclick="switchTab('coefficients')">Coefficients & Values</button>
</div>

{{-- ═══════════════════════════════════════════════════════
     TAB A: EMISSION TYPES
═══════════════════════════════════════════════════════ --}}
<div id="tab-emission-types" class="tab-pane active">
    <div class="section-header">
        <h2>Emission Types</h2>
    </div>
    <div class="card" style="padding:0">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Formula</th>
                    <th>Unit</th>
                    <th>Categories</th>
                    <th>Coefficients</th>
                </tr>
            </thead>
            <tbody id="et-tbody">
                <tr><td colspan="5" class="text-muted" style="text-align:center;padding:2rem">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     TAB B: CATEGORIES & VALUES
═══════════════════════════════════════════════════════ --}}
<div id="tab-categories" class="tab-pane">
    <div class="sub-select-row">
        <label>Emission Type:</label>
        <select id="cat-et-select" onchange="loadCategories()">
            <option value="">— Select —</option>
        </select>
    </div>

    <div id="categories-container" class="hidden">
        <div class="section-header">
            <h2 id="cat-section-title">Categories</h2>
        </div>
        <div class="card" style="padding:0">
            <table>
                <thead>
                    <tr><th>Display Name</th><th>Slug</th><th>Sort Order</th><th>Values</th><th>Actions</th></tr>
                </thead>
                <tbody id="cat-tbody"></tbody>
            </table>
        </div>

        <div id="values-container" class="hidden" style="margin-top:1.5rem">
            <div class="section-header">
                <h2 id="values-section-title">Category Values</h2>
                <button class="btn btn-primary btn-sm" onclick="openModal('cv-modal')">+ Add Value</button>
            </div>
            <div class="card" style="padding:0">
                <table>
                    <thead>
                        <tr><th>Label</th><th>Code</th><th>Actions</th></tr>
                    </thead>
                    <tbody id="cv-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     TAB C: INPUT FIELDS
═══════════════════════════════════════════════════════ --}}
<div id="tab-input-fields" class="tab-pane">
    <div class="sub-select-row">
        <label>Emission Type:</label>
        <select id="if-et-select" onchange="loadInputFields()">
            <option value="">— Select —</option>
        </select>
    </div>

    <div id="if-container" class="hidden">
        <div class="card" style="padding:0">
            <table>
                <thead>
                    <tr><th>Display Name</th><th>Slug</th><th>Unit</th></tr>
                </thead>
                <tbody id="if-tbody"></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     TAB D: COEFFICIENTS & VALUES
═══════════════════════════════════════════════════════ --}}
<div id="tab-coefficients" class="tab-pane">
    <div class="sub-select-row">
        <label>Emission Type:</label>
        <select id="coef-et-select" onchange="loadCoefficients()">
            <option value="">— Select —</option>
        </select>
    </div>

    <div id="coef-container" class="hidden">
        <div class="card" style="padding:0">
            <table>
                <thead>
                    <tr><th>Display Name</th><th>Slug</th><th>Depends On</th><th>Values</th><th>Actions</th></tr>
                </thead>
                <tbody id="coef-tbody"></tbody>
            </table>
        </div>

        <div id="coef-values-container" class="hidden" style="margin-top:1.5rem">
            <div class="section-header">
                <h2 id="coef-values-title">Coefficient Values</h2>
                <button class="btn btn-primary btn-sm" id="add-coefval-btn">+ Add Value</button>
            </div>
            <div class="card" style="padding:0;overflow-x:auto">
                <table>
                    <thead id="coef-values-thead"><tr></tr></thead>
                    <tbody id="coef-values-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     MODALS
═══════════════════════════════════════════════════════ --}}

{{-- Category Value Modal --}}
<div class="modal-overlay" id="cv-modal">
    <div class="modal">
        <h3 id="cv-modal-title">Add Category Value</h3>
        <input type="hidden" id="cv-id">
        <div class="field"><label>Label</label><input type="text" id="cv-label" placeholder="e.g. HSD (High-Speed Diesel)"></div>
        <div class="field"><label>Code / Slug</label><input type="text" id="cv-code" placeholder="e.g. hsd"></div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('cv-modal')">Cancel</button>
            <button class="btn btn-primary" onclick="saveCategoryValue()">Save</button>
        </div>
    </div>
</div>

{{-- Coefficient Value Modal --}}
<div class="modal-overlay" id="coefval-modal">
    <div class="modal">
        <h3 id="coefval-modal-title">Add Coefficient Value</h3>
        <input type="hidden" id="coefval-id">
        <div class="field"><label>Value</label><input type="number" id="coefval-value" placeholder="0.000000" step="any"></div>
        <div class="field"><label>Based On <span class="text-muted" style="text-transform:none;font-weight:400">(source / reference)</span></label><input type="text" id="coefval-based-on" placeholder="e.g. IPCC 2006, AR5, Industry Standard"></div>
        <div id="coefval-cat-dropdowns"></div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('coefval-modal')">Cancel</button>
            <button class="btn btn-primary" onclick="saveCoefficientValue()">Save</button>
        </div>
    </div>
</div>

{{-- Delete confirm modal --}}
<div class="modal-overlay" id="del-modal">
    <div class="modal">
        <h3>Confirm Delete</h3>
        <p id="del-msg" style="margin-bottom:1.25rem;color:var(--text-muted)"></p>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('del-modal')">Cancel</button>
            <button class="btn btn-danger" onclick="confirmDelete()">Delete</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const API = '/api/admin';
let allEmissionTypes = [];
let currentCategoryId = null;
let currentCoefficientId = null;
let currentCoefDepCategories = [];
let deleteFn = null;

// ── Bootstrap ──────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    loadEmissionTypesGlobal();
});

function loadEmissionTypesGlobal() {
    fetch(`${API}/emission-types`)
        .then(r => r.json())
        .then(types => {
            allEmissionTypes = types;
            renderEmissionTypesTable(types);
            ['cat-et-select','if-et-select','coef-et-select'].forEach(id => {
                const sel = document.getElementById(id);
                sel.innerHTML = '<option value="">— Select —</option>';
                types.forEach(t => {
                    const o = document.createElement('option');
                    o.value = t.id; o.textContent = t.name;
                    sel.appendChild(o);
                });
            });
        });
}

// ── Tab switching ───────────────────────────────────────────────────────────
function switchTab(name) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    event.target.classList.add('active');
}

// ── Modal helpers ───────────────────────────────────────────────────────────
function openModal(id) { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
    const inner = m.querySelector('.modal');
    if (inner) inner.addEventListener('click', e => e.stopPropagation());
});

// ── Delete helpers ──────────────────────────────────────────────────────────
function askDelete(msg, fn) {
    document.getElementById('del-msg').textContent = msg;
    deleteFn = fn;
    openModal('del-modal');
}
function confirmDelete() { if (deleteFn) { deleteFn(); deleteFn = null; } closeModal('del-modal'); }

// ── CSRF helper ─────────────────────────────────────────────────────────────
function csrf() { return document.querySelector('meta[name="csrf-token"]').content; }

function apiReq(url, method, body) {
    return fetch(url, {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
        body: body ? JSON.stringify(body) : undefined,
    }).then(async r => {
        if (!r.ok) { const d = await r.json(); throw new Error(JSON.stringify(d)); }
        return r.status === 204 ? null : r.json();
    });
}

// ════════════════════════════════════════════════════════════════════════════
// TAB A — EMISSION TYPES
// ════════════════════════════════════════════════════════════════════════════
function renderEmissionTypesTable(types) {
    const tbody = document.getElementById('et-tbody');
    if (!types.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-muted" style="text-align:center;padding:2rem">No emission types yet.</td></tr>'; return; }
    tbody.innerHTML = types.map(t => `
        <tr>
            <td><strong>${t.name}</strong><br><span class="text-muted" style="font-size:.78rem">${t.slug}</span></td>
            <td><span style="font-family:monospace;font-size:.8rem;color:var(--text-muted)">${t.formula ?? '—'}</span></td>
            <td><span class="badge">${t.unit}</span></td>
            <td>${t.categories_count ?? '—'}</td>
            <td>${t.coefficients_count ?? '—'}</td>
        </tr>`).join('');
}

// ════════════════════════════════════════════════════════════════════════════
// TAB B — CATEGORIES & VALUES
// ════════════════════════════════════════════════════════════════════════════
function loadCategories() {
    const etId = document.getElementById('cat-et-select').value;
    document.getElementById('values-container').classList.add('hidden');

    if (!etId) { document.getElementById('categories-container').classList.add('hidden'); return; }

    fetch(`${API}/emission-types/${etId}/categories`)
        .then(r => r.json())
        .then(cats => {
            const t = allEmissionTypes.find(x => x.id == etId);
            document.getElementById('cat-section-title').textContent = `Categories — ${t?.name ?? ''}`;
            const tbody = document.getElementById('cat-tbody');
            tbody.innerHTML = cats.map(c => `
                <tr>
                    <td><strong>${c.display_name}</strong></td>
                    <td><code>${c.name}</code></td>
                    <td>${c.sort_order}</td>
                    <td>${c.values_count ?? '—'}</td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick="manageValues(${c.id}, '${c.display_name}')">Manage Values</button>
                    </td>
                </tr>`).join('') || '<tr><td colspan="5" class="text-muted" style="text-align:center;padding:1.5rem">No categories.</td></tr>';
            document.getElementById('categories-container').classList.remove('hidden');
        });
}

// ── Category Values ─────────────────────────────────────────────────────────
function manageValues(catId, catName) {
    currentCategoryId = catId;
    document.getElementById('values-section-title').textContent = `Values — ${catName}`;
    loadCategoryValues();
    document.getElementById('values-container').classList.remove('hidden');
    document.getElementById('values-container').scrollIntoView({ behavior: 'smooth' });
}

function loadCategoryValues() {
    fetch(`${API}/categories/${currentCategoryId}/values`)
        .then(r => r.json())
        .then(vals => {
            const tbody = document.getElementById('cv-tbody');
            tbody.innerHTML = vals.map(v => `
                <tr>
                    <td>${v.label}</td>
                    <td><code>${v.code}</code></td>
                    <td>
                        <div class="flex-row">
                            <button class="btn btn-secondary btn-sm" onclick="editCategoryValue(${v.id}, '${v.label.replace(/'/g,"\\'")}', '${v.code}')">Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="askDelete('Delete value &quot;${v.label}&quot;?', () => deleteCategoryValue(${v.id}))">Delete</button>
                        </div>
                    </td>
                </tr>`).join('') || '<tr><td colspan="3" class="text-muted" style="text-align:center;padding:1.5rem">No values.</td></tr>';
        });
}

function editCategoryValue(id, label, code) {
    document.getElementById('cv-id').value = id;
    document.getElementById('cv-label').value = label;
    document.getElementById('cv-code').value = code;
    document.getElementById('cv-modal-title').textContent = 'Edit Category Value';
    openModal('cv-modal');
}

function saveCategoryValue() {
    const id = document.getElementById('cv-id').value;
    const body = { label: document.getElementById('cv-label').value, code: document.getElementById('cv-code').value };
    const req = id
        ? apiReq(`${API}/category-values/${id}`, 'PUT', body)
        : apiReq(`${API}/categories/${currentCategoryId}/values`, 'POST', body);
    req.then(() => { closeModal('cv-modal'); resetCvModal(); loadCategoryValues(); })
       .catch(e => alert('Error: ' + e.message));
}

function deleteCategoryValue(id) {
    apiReq(`${API}/category-values/${id}`, 'DELETE').then(() => loadCategoryValues()).catch(e => alert(e.message));
}

function resetCvModal() {
    ['cv-id','cv-label','cv-code'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('cv-modal-title').textContent = 'Add Category Value';
}

// ════════════════════════════════════════════════════════════════════════════
// TAB C — INPUT FIELDS
// ════════════════════════════════════════════════════════════════════════════
function loadInputFields() {
    const etId = document.getElementById('if-et-select').value;
    if (!etId) { document.getElementById('if-container').classList.add('hidden'); return; }

    fetch(`${API}/emission-types/${etId}/input-fields`)
        .then(r => r.json())
        .then(fields => {
            const tbody = document.getElementById('if-tbody');
            tbody.innerHTML = fields.map(f => `
                <tr>
                    <td><strong>${f.display_name}</strong></td>
                    <td><code>${f.name}</code></td>
                    <td><span class="badge">${f.unit ?? '—'}</span></td>
                </tr>`).join('') || '<tr><td colspan="3" class="text-muted" style="text-align:center;padding:1.5rem">No input fields.</td></tr>';
            document.getElementById('if-container').classList.remove('hidden');
        });
}

// ════════════════════════════════════════════════════════════════════════════
// TAB D — COEFFICIENTS & VALUES
// ════════════════════════════════════════════════════════════════════════════
function loadCoefficients() {
    const etId = document.getElementById('coef-et-select').value;
    document.getElementById('coef-values-container').classList.add('hidden');
    if (!etId) { document.getElementById('coef-container').classList.add('hidden'); return; }

    fetch(`${API}/emission-types/${etId}/coefficients`)
        .then(r => r.json())
        .then(coefs => {
            const tbody = document.getElementById('coef-tbody');
            tbody.innerHTML = coefs.map(c => {
                const deps = c.dependent_categories?.map(d => `<span class="badge">${d.display_name}</span>`).join(' ') || '<span class="text-muted">constant</span>';
                return `
                <tr>
                    <td><strong>${c.display_name}</strong></td>
                    <td><code>${c.name}</code></td>
                    <td>${deps}</td>
                    <td>${c.values_count ?? '—'}</td>
                    <td>
                        <button class="btn btn-secondary btn-sm" onclick="manageCoefValues(${c.id}, '${c.display_name}', ${JSON.stringify(c.dependent_categories ?? []).replace(/"/g,'&quot;')})">Manage Values</button>
                    </td>
                </tr>`;
            }).join('') || '<tr><td colspan="5" class="text-muted" style="text-align:center;padding:1.5rem">No coefficients.</td></tr>';
            document.getElementById('coef-container').classList.remove('hidden');
        });
}

// ── Coefficient Values ──────────────────────────────────────────────────────
function manageCoefValues(coefId, coefName, depCategories) {
    currentCoefficientId = coefId;
    currentCoefDepCategories = depCategories;
    document.getElementById('coef-values-title').textContent = `Values — ${coefName}`;
    loadCoefficientValues(depCategories);
    document.getElementById('coef-values-container').classList.remove('hidden');
    document.getElementById('coef-values-container').scrollIntoView({ behavior: 'smooth' });
}

function loadCoefficientValues(depCategories) {
    fetch(`${API}/coefficients/${currentCoefficientId}/values`)
        .then(r => r.json())
        .then(vals => {
            const thead = document.getElementById('coef-values-thead');
            thead.innerHTML = '<tr><th>Value</th>' +
                depCategories.map(d => `<th>${d.display_name}</th>`).join('') +
                '<th>Based On</th><th>Actions</th></tr>';

            const tbody = document.getElementById('coef-values-tbody');
            if (!vals.length) {
                tbody.innerHTML = `<tr><td colspan="${3 + depCategories.length}" class="text-muted" style="text-align:center;padding:1.5rem">No values yet.</td></tr>`;
                return;
            }
            tbody.innerHTML = vals.map(cv => {
                const catCols = depCategories.map(dep => {
                    const match = cv.category_values.find(v => v.category_id === dep.id);
                    return `<td>${match ? match.label : '<span class="text-muted">—</span>'}</td>`;
                }).join('');
                const basedOnEsc = (cv.based_on ?? '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                return `<tr>
                    <td class="td-num"><strong>${cv.value}</strong></td>
                    ${catCols}
                    <td><span class="text-muted">${cv.based_on ?? '—'}</span></td>
                    <td>
                        <div class="flex-row">
                            <button class="btn btn-secondary btn-sm" onclick="editCoefValue(${cv.id}, ${cv.value}, '${basedOnEsc}', ${JSON.stringify(cv.category_values.map(v=>v.id)).replace(/"/g,'&quot;')})">Edit</button>
                            <button class="btn btn-danger btn-sm" onclick="askDelete('Delete this coefficient value?', () => deleteCoefValue(${cv.id}))">Delete</button>
                        </div>
                    </td>
                </tr>`;
            }).join('');
        });
}

function openCoefValueModal(id = '', value = '', basedOn = '', selectedCvIds = []) {
    document.getElementById('coefval-id').value = id;
    document.getElementById('coefval-value').value = value;
    document.getElementById('coefval-based-on').value = basedOn;
    document.getElementById('coefval-modal-title').textContent = id ? 'Edit Coefficient Value' : 'Add Coefficient Value';

    const container = document.getElementById('coefval-cat-dropdowns');
    container.innerHTML = '';

    if (!currentCoefDepCategories.length) {
        container.innerHTML = '<p class="text-muted" style="font-size:.85rem">This coefficient is constant — no category selection needed.</p>';
        openModal('coefval-modal');
        return;
    }

    const fetches = currentCoefDepCategories.map(dep =>
        fetch(`${API}/categories/${dep.id}/values`).then(r => r.json()).then(vals => ({ dep, vals }))
    );
    Promise.all(fetches).then(results => {
        results.forEach(({ dep, vals }) => {
            const div = document.createElement('div');
            div.className = 'field';
            div.innerHTML = `<label>${dep.display_name}</label>
                <select data-cat-id="${dep.id}">
                    <option value="">— Select —</option>
                    ${vals.map(v => `<option value="${v.id}" ${selectedCvIds.includes(v.id) ? 'selected' : ''}>${v.label}</option>`).join('')}
                </select>`;
            container.appendChild(div);
        });
        openModal('coefval-modal');
    });
}

function editCoefValue(id, value, basedOn, catValueIds) {
    openCoefValueModal(id, value, basedOn, catValueIds);
}

function saveCoefficientValue() {
    const id = document.getElementById('coefval-id').value;
    const value = document.getElementById('coefval-value').value;
    const basedOn = document.getElementById('coefval-based-on').value;
    const categoryValueIds = [...document.querySelectorAll('#coefval-cat-dropdowns select')]
        .map(s => parseInt(s.value)).filter(v => !isNaN(v));

    const body = { value: parseFloat(value), based_on: basedOn || null, category_value_ids: categoryValueIds };
    const req = id
        ? apiReq(`${API}/coefficient-values/${id}`, 'PUT', body)
        : apiReq(`${API}/coefficients/${currentCoefficientId}/values`, 'POST', body);
    req.then(() => { closeModal('coefval-modal'); loadCoefficientValues(currentCoefDepCategories); })
       .catch(e => alert('Error: ' + e.message));
}

function deleteCoefValue(id) {
    apiReq(`${API}/coefficient-values/${id}`, 'DELETE')
        .then(() => loadCoefficientValues(currentCoefDepCategories))
        .catch(e => alert(e.message));
}

document.getElementById('add-coefval-btn').addEventListener('click', () => openCoefValueModal());
</script>
@endpush
