@extends('layouts.app')

@section('title', 'Emission Calculator — Adaro Emission Tracker')

@section('content')
<h1 class="page-title">Emission Calculator</h1>
<p class="page-subtitle">Calculate greenhouse gas emissions based on activity data.</p>

{{-- Step 1: Emission Type --}}
<div class="card">
    <div class="field">
        <label for="emission-type-select">Emission Type</label>
        <select id="emission-type-select">
            <option value="">— Select emission type —</option>
        </select>
    </div>

    {{-- Dynamic form (hidden until type selected) --}}
    <div id="dynamic-form" class="hidden">
        <div id="categories-section"></div>

        <div id="inputs-section"></div>

        <div class="field mt-2">
            <label>Calculation Formula</label>
            <div class="formula-box" id="formula-display">—</div>
        </div>

        <div class="mt-2" style="text-align:right">
            <button class="btn btn-primary" id="calculate-btn" onclick="calculate()">
                &#9632; Calculate Emission
            </button>
        </div>
    </div>
</div>

{{-- Result panel (hidden until calculated) --}}
<div id="result-panel" class="hidden">
    <div class="card result-card">
        <div class="co2-watermark">CO₂</div>
        <div class="result-label">Calculation Result</div>
        <div style="margin-bottom:1.25rem">
            <span class="result-number" id="result-number">—</span>
            <span class="result-unit" id="result-unit">tCO2e</span>
        </div>

        <table id="result-table">
            <thead>
                <tr>
                    <th>Coefficient</th>
                    <th class="td-num">Value</th>
                    <th>Based On</th>
                </tr>
            </thead>
            <tbody id="result-tbody"></tbody>
        </table>

        <div class="expr-box">
            <div class="expr-label">Evaluated Expression</div>
            <div class="expr-text" id="result-expr">—</div>
        </div>

        <div class="mt-2" style="text-align:right">
            <button class="btn btn-secondary" onclick="recalculate()">↺ Recalculate</button>
        </div>
    </div>
</div>

{{-- Error panel --}}
<div id="error-panel" class="hidden">
    <div class="alert alert-error" id="error-msg"></div>
</div>
@endsection

@push('scripts')
<script>
const API = '/api';
let currentSchema = null;

// Load emission types on page load
fetch(`${API}/emission-types`)
    .then(r => r.json())
    .then(types => {
        const sel = document.getElementById('emission-type-select');
        types.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.id;
            opt.textContent = t.name;
            sel.appendChild(opt);
        });
    });

document.getElementById('emission-type-select').addEventListener('change', function() {
    const id = this.value;
    if (!id) { hideForm(); return; }
    fetch(`${API}/emission-types/${id}/schema`)
        .then(r => r.json())
        .then(schema => renderForm(schema));
});

function renderForm(schema) {
    currentSchema = schema;
    hideResult();

    // Categories
    const catSection = document.getElementById('categories-section');
    catSection.innerHTML = '';
    schema.categories.forEach(cat => {
        const div = document.createElement('div');
        div.className = 'field';
        div.innerHTML = `
            <label for="cat-${cat.id}">${cat.display_name}</label>
            <select id="cat-${cat.id}" data-category-id="${cat.id}">
                <option value="">— Select ${cat.display_name} —</option>
                ${cat.values.map(v => `<option value="${v.id}">${v.label}</option>`).join('')}
            </select>`;
        catSection.appendChild(div);
    });

    // Input fields
    const inputSection = document.getElementById('inputs-section');
    inputSection.innerHTML = '';
    schema.input_fields.forEach(field => {
        const div = document.createElement('div');
        div.className = 'field';
        div.innerHTML = `
            <label for="input-${field.id}">${field.display_name}${field.unit ? ' (' + field.unit + ')' : ''}</label>
            <div class="input-group">
                <input type="number" id="input-${field.id}" data-input-id="${field.id}" min="0" step="any" placeholder="0.00">
                ${field.unit ? `<span class="input-unit">${field.unit}</span>` : ''}
            </div>`;
        inputSection.appendChild(div);
    });

    // Formula
    document.getElementById('formula-display').innerHTML = `<strong>${schema.formula_display}</strong>`;

    document.getElementById('dynamic-form').classList.remove('hidden');
}

function hideForm() {
    document.getElementById('dynamic-form').classList.add('hidden');
    hideResult();
}

function hideResult() {
    document.getElementById('result-panel').classList.add('hidden');
    document.getElementById('error-panel').classList.add('hidden');
}

function calculate() {
    if (!currentSchema) return;

    const inputs = [];
    let valid = true;

    document.querySelectorAll('[data-input-id]').forEach(el => {
        if (!el.value) { el.style.borderColor = '#DC2626'; valid = false; }
        else { el.style.borderColor = ''; inputs.push({ input_field_id: parseInt(el.dataset.inputId), value: parseFloat(el.value) }); }
    });

    const categorySelections = [];
    document.querySelectorAll('[data-category-id]').forEach(el => {
        if (!el.value) { el.style.borderColor = '#DC2626'; valid = false; }
        else { el.style.borderColor = ''; categorySelections.push({ category_id: parseInt(el.dataset.categoryId), category_value_id: parseInt(el.value) }); }
    });

    if (!valid) return;

    const btn = document.getElementById('calculate-btn');
    btn.disabled = true;
    btn.textContent = 'Calculating...';

    fetch(`${API}/calculate`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        body: JSON.stringify({ emission_type_id: currentSchema.id, inputs, category_selections: categorySelections }),
    })
    .then(async r => {
        const data = await r.json();
        if (!r.ok) throw new Error(data.message || 'Calculation failed.');
        showResult(data);
    })
    .catch(err => showError(err.message))
    .finally(() => { btn.disabled = false; btn.innerHTML = '&#9632; Calculate Emission'; });
}

function showResult(data) {
    document.getElementById('error-panel').classList.add('hidden');

    document.getElementById('result-number').textContent = Number(data.total_emission).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
    document.getElementById('result-unit').textContent = data.unit;

    const tbody = document.getElementById('result-tbody');
    tbody.innerHTML = '';

    // Input row first
    document.querySelectorAll('[data-input-id]').forEach(el => {
        const field = currentSchema.input_fields.find(f => f.id === parseInt(el.dataset.inputId));
        if (!field) return;
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${field.display_name}</td><td class="td-num">${Number(el.value).toLocaleString()} ${field.unit ?? ''}</td><td class="text-muted">User Input</td>`;
        tbody.appendChild(tr);
    });

    data.coefficients_used.forEach(c => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${c.name}</td><td class="td-num">${c.value}</td><td class="text-muted">${c.based_on}</td>`;
        tbody.appendChild(tr);
    });

    document.getElementById('result-expr').textContent = data.formula_evaluated;
    document.getElementById('result-panel').classList.remove('hidden');
    document.getElementById('result-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function showError(msg) {
    document.getElementById('result-panel').classList.add('hidden');
    document.getElementById('error-msg').textContent = msg;
    document.getElementById('error-panel').classList.remove('hidden');
}

function recalculate() {
    hideResult();
    document.getElementById('dynamic-form').scrollIntoView({ behavior: 'smooth' });
}
</script>
@endpush
