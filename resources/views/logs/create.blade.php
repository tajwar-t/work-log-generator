@extends('layouts.app')
@section('title', isset($log) ? 'Edit Log' : 'New Log')
@section('content')
<style>
.saved-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.3rem 0.75rem; border-radius: 20px;
    font-size: 0.75rem; font-weight: 500;
    margin-bottom: 0.875rem;
    border: 1px solid;
}
.saved-badge-green {
    background: rgba(67,198,120,0.1);
    border-color: rgba(67,198,120,0.4);
    color: #43c678;
}
.saved-badge-dim {
    background: rgba(108,143,255,0.08);
    border-color: rgba(108,143,255,0.25);
    color: #9ca3b4;
}
</style>

<div id="toastContainer" class="toaster-container"></div>

<div class="page-header">
    <div>
        <h1 class="page-title">{{ isset($log) ? 'Edit Log' : 'Log Generator' }}</h1>
        <p class="page-sub">{{ isset($log) ? 'Modify your work log entry' : 'Create your daily work log entry' }}</p>
    </div>
    <a href="{{ route('logs.index') }}" class="btn-secondary">View History →</a>
</div>

<div class="generator-layout">
    <!-- LEFT: Input Panel -->
    <div class="input-panel">
        <div class="panel-card">

            <!-- Type & Date Row -->
            <div class="control-row">
                <div class="control-group">
                    <label class="ctrl-label">Template Type</label>
                    <div class="select-wrap">
                        <select id="logType" class="styled-select">
                            <option value="day_start" {{ (old('log_type', $log->log_type ?? '') == 'day_start') ? 'selected' : '' }}>Day Start</option>
                            <option value="day_end"   {{ (old('log_type', $log->log_type ?? '') == 'day_end')   ? 'selected' : '' }}>Day End</option>
                        </select>
                    </div>
                </div>
                <div class="control-group">
                    <label class="ctrl-label">Date</label>
                    <input type="date" id="logDate" class="styled-input" value="{{ old('log_date', $log->log_date ?? date('Y-m-d')) }}">
                </div>
            </div>

            <div id="savedBadge" class="saved-badge" style="display:none"></div>
            <!-- Smart Suggestions -->
            <div class="smart-section">
                <span class="smart-label">⚡ Smart Fill</span>
                <div class="smart-chips">
                    <button type="button" class="chip" onclick="loadDayChip(1)">Yesterday's Work</button>
                    <button type="button" class="chip" onclick="loadDayChip(2)">2 Days Ago</button>
                    <button type="button" class="chip" onclick="loadDayChip(3)">3 Days Ago</button>
                </div>
            </div>

            <!-- Section A -->
            <div class="work-section">
                <div class="section-header">
                    <span class="section-label" id="sectionALabel">Last Day Work</span>
                    <button type="button" class="btn-add" onclick="addItem('sectionA')">+ Add Item</button>
                </div>
                <div id="sectionA" class="items-list">
                    <div class="item-row">
                        <input type="text" class="item-input" placeholder="Enter work item">
                        <button type="button" class="btn-remove" onclick="removeItem(this)">🗑</button>
                    </div>
                </div>
            </div>

            <!-- Section B -->
            <div class="work-section">
                <div class="section-header">
                    <span class="section-label" id="sectionBLabel">Today's Work</span>
                    <button type="button" class="btn-add btn-add-green" onclick="addItem('sectionB')">+ Add Item</button>
                </div>
                <div id="sectionB" class="items-list">
                    <div class="item-row">
                        <input type="text" class="item-input" placeholder="Enter work item">
                        <button type="button" class="btn-remove" onclick="removeItem(this)">🗑</button>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="action-row">
                <button type="button" class="btn-generate" onclick="generateTemplate()">
                    <span>⚙</span> Generate Template
                </button>
                <button type="button" class="btn-save" id="saveBtn" onclick="saveLog()">
                    <span>💾</span> Save Work Log
                </button>
            </div>

        </div>
    </div>

    <!-- RIGHT: Output Panel -->
    <div class="output-panel">
        <div class="panel-card output-card">
            <h3 class="output-title">Generated Template</h3>
            <div id="outputArea" class="output-area empty">
                <div class="output-empty">
                    <span class="empty-icon">◫</span>
                    <p>Your generated template will appear here</p>
                    <small>Fill in your work items and click "Generate Template"</small>
                </div>
            </div>
            <div class="output-actions">
                <button type="button" class="btn-copy" onclick="copyTemplate()" id="copyBtn" disabled>Copy Template</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
const IS_EDIT   = {{ isset($log) ? 'true' : 'false' }};
const LOG_ID    = {{ isset($log) ? $log->id : 'null' }};
const EXISTING  = @json($log ?? null);
const SAVE_URL  = IS_EDIT
    ? `{{ isset($log) ? route('logs.update', $log->id ?? 0) : '' }}`
    : '{{ route('logs.store') }}';

// ---- LABELS ----
const labels = {
    day_start: {
        a: 'Last Day Work',  b: "Today's Work",
        aHeader: '::: Last day I worked with :::', bHeader: ':::: Today I will work with ::::', title: 'Day Start'
    },
    day_end: {
        a: "Today's Work", b: "Tomorrow's Work",
        aHeader: '::: Today I worked with :::', bHeader: ':::: Tomorrow I will work with ::::', title: 'Day End'
    }
};

function getType() { return document.getElementById('logType').value; }
function getDate() { return document.getElementById('logDate').value; }

function updateLabels() {
    const t = getType();
    document.getElementById('sectionALabel').textContent = labels[t].a;
    document.getElementById('sectionBLabel').textContent = labels[t].b;
}
document.getElementById('logType').addEventListener('change', () => { updateLabels(); runSmartFill(); });
document.getElementById('logDate').addEventListener('change', runSmartFill);
updateLabels();

// ---- ROW HELPERS ----
function makeRow(value = '') {
    const div = document.createElement('div');
    div.className = 'item-row';
    div.draggable = true;
    div.innerHTML = `
        <span class="drag-handle" title="Drag to reorder">&#8942;&#8942;</span>
        <span class="item-num">1</span>
        <input type="text" class="item-input" placeholder="Enter work item" value="${value.replace(/"/g, '&quot;')}">
        <button type="button" class="btn-remove" onclick="removeItem(this)">🗑</button>`;
    attachDragEvents(div);
    return div;
}

function addItem(sectionId, value = '') {
    const section = document.getElementById(sectionId);
    const div = makeRow(value);
    section.appendChild(div);
    reNumber(section);
    div.querySelector('input').focus();
}

function removeItem(btn) {
    const row = btn.closest('.item-row');
    const section = row.closest('.items-list');
    if (section.querySelectorAll('.item-row').length > 1) {
        row.style.opacity = '0';
        row.style.transform = 'translateX(20px)';
        row.style.transition = 'opacity 0.2s, transform 0.2s';
        setTimeout(() => { row.remove(); reNumber(section); }, 200);
    } else {
        row.querySelector('input').value = '';
    }
}

function getItems(sectionId) {
    return [...document.querySelectorAll(`#${sectionId} .item-input`)]
        .map(i => i.value.trim()).filter(Boolean);
}

function setItems(sectionId, items) {
    const section = document.getElementById(sectionId);
    section.innerHTML = '';
    if (!items || items.length === 0) items = [''];
    items.forEach(item => section.appendChild(makeRow(item)));
    reNumber(section);
}

// ---- SMART FILL: auto on page load / type+date change ----
function runSmartFill() {
    const type = getType();
    const date = getDate();
    if (!date) return;
 
    fetch(`/api/smart-fill?type=${type}&date=${date}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        setItems('sectionA', data.section_a && data.section_a.length ? data.section_a : []);
        setItems('sectionB', data.section_b && data.section_b.length ? data.section_b : []);
 
        // Show badge: where did this data come from?
        const badge = document.getElementById('savedBadge');
        if (!badge) return;
        if (data.from_saved) {
            badge.textContent = '✓ Loaded your saved log';
            badge.className = 'saved-badge saved-badge-green';
            badge.style.display = 'inline-flex';
        } else if ((data.section_a && data.section_a.length) || (data.section_b && data.section_b.length)) {
            badge.textContent = '⟳ Auto-filled from previous log';
            badge.className = 'saved-badge saved-badge-dim';
            badge.style.display = 'inline-flex';
        } else {
            badge.style.display = 'none';
        }
    })
    .catch(() => {});
}

// ---- SMART FILL CHIPS: Yesterday / 2 days ago / 3 days ago ----
function loadDayChip(daysAgo) {
    const type = getType();
    const date = getDate();
    const chip = document.querySelectorAll('.chip')[daysAgo - 1];

    chip.classList.add('chip-loading');
    chip.disabled = true;

    fetch(`/api/fetch-day?days_ago=${daysAgo}&type=${type}&date=${date}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        chip.classList.remove('chip-loading');
        chip.disabled = false;
        if (!data.found) {
            showToast(`No ${type === 'day_start' ? 'Day Start' : 'Day End'} log found for ${data.date}`, 'error');
            return;
        }
        // Reset both before loading so no stale data bleeds through
        setItems('sectionA', data.section_a && data.section_a.length ? data.section_a : []);
        setItems('sectionB', data.section_b && data.section_b.length ? data.section_b : []);
        showToast('Loaded entries from ' + data.date, 'success');
    })
    .catch(() => {
        chip.classList.remove('chip-loading');
        chip.disabled = false;
        showToast('Could not fetch entries', 'error');
    });
}

// ---- GENERATE TEMPLATE ----
function generateTemplate() {
    const type  = getType();
    const dateVal = getDate();
    const date  = dateVal ? new Date(dateVal).toLocaleDateString('en-GB', {day:'2-digit',month:'2-digit',year:'numeric'}) : 'N/A';
    const itemsA = getItems('sectionA');
    const itemsB = getItems('sectionB');
    const lbl   = labels[type];
    const SEP   = '— -- — -- — -- — -- — -- —';

    const lines = [
        SEP,
        `**${lbl.title} ${date}**`,
        SEP,
        `**${lbl.aHeader}**`,
        ...itemsA.map((item, i) => `${i+1}. ${item}`),
        SEP,
        `**${lbl.bHeader}**`,
        ...itemsB.map((item, i) => `${i+1}. ${item}`),
        SEP,
    ];

    const text = lines.join('\n');
    renderOutput(text);
    document.getElementById('copyBtn').disabled = false;
    return text;
}

function renderOutput(text) {
    const area = document.getElementById('outputArea');
    area.classList.remove('empty');
    area.innerHTML = text.split('\n').map(line =>
        line.startsWith('**') && line.endsWith('**')
            ? `<div class="out-bold">${line.slice(2,-2)}</div>`
            : `<div class="out-line">${line}</div>`
    ).join('');
}

// ---- SAVE (AJAX — stays on same page) ----
function saveLog() {
    const text   = generateTemplate();
    const type   = getType();
    const date   = getDate();
    const itemsA = getItems('sectionA');
    const itemsB = getItems('sectionB');

    const saveBtn = document.getElementById('saveBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span>⏳</span> Saving…';

    const body = new URLSearchParams({
        _token:           CSRF,
        log_type:         type,
        log_date:         date,
        section_a_items:  JSON.stringify(itemsA),
        section_b_items:  JSON.stringify(itemsB),
        generated_text:   text,
    });

    if (IS_EDIT) body.append('_method', 'PUT');

    fetch(SAVE_URL, {
        method:  'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    body,
    })
    .then(r => r.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<span>💾</span> Save Work Log';
        if (data.success) {
            showToast(data.message ?? 'Saved successfully!', 'success');
        } else {
            showToast('Save failed. Please try again.', 'error');
        }
    })
    .catch(() => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<span>💾</span> Save Work Log';
        showToast('Network error. Please try again.', 'error');
    });
}

// ---- COPY ----
const BOLD_MAP = {'A':'𝗔','B':'𝗕','C':'𝗖','D':'𝗗','E':'𝗘','F':'𝗙','G':'𝗚','H':'𝗛','I':'𝗜','J':'𝗝','K':'𝗞','L':'𝗟','M':'𝗠','N':'𝗡','O':'𝗢','P':'𝗣','Q':'𝗤','R':'𝗥','S':'𝗦','T':'𝗧','U':'𝗨','V':'𝗩','W':'𝗪','X':'𝗫','Y':'𝗬','Z':'𝗭','a':'𝗮','b':'𝗯','c':'𝗰','d':'𝗱','e':'𝗲','f':'𝗳','g':'𝗴','h':'𝗵','i':'𝗶','j':'𝗷','k':'𝗸','l':'𝗹','m':'𝗺','n':'𝗻','o':'𝗼','p':'𝗽','q':'𝗾','r':'𝗿','s':'𝘀','t':'𝘁','u':'𝘂','v':'𝘃','w':'𝘄','x':'𝘅','y':'𝘆','z':'𝘇','0':'𝟎','1':'𝟏','2':'𝟐','3':'𝟑','4':'𝟒','5':'𝟓','6':'𝟔','7':'𝟕','8':'𝟖','9':'𝟗'};
function toBoldUnicode(str) { return [...str].map(ch => BOLD_MAP[ch] ?? ch).join(''); }
function copyTemplate() {
    const stored = document.getElementById('outputArea').innerText;
    const lines  = [...document.querySelectorAll('.out-bold, .out-line')].map(el => {
        return el.classList.contains('out-bold') ? toBoldUnicode(el.textContent) : el.textContent;
    }).join('\n');
    if (!lines.trim()) return;
    navigator.clipboard.writeText(lines).then(() => {
        const btn = document.getElementById('copyBtn');
        btn.textContent = '✓ Copied!';
        btn.classList.add('copied');
        setTimeout(() => { btn.textContent = 'Copy Template'; btn.classList.remove('copied'); }, 2000);
    });
}

// ---- TOASTER (stacked slide-in notifications) ----
function showToast(msg, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `toaster-item toaster-${type}`;

    const icon = type === 'success' ? '✓' : type === 'error' ? '✕' : 'ℹ';
    toast.innerHTML = `
        <span class="toaster-icon">${icon}</span>
        <span class="toaster-msg">${msg}</span>
        <button class="toaster-close" onclick="this.closest('.toaster-item').remove()">×</button>
    `;

    container.appendChild(toast);

    // Trigger animation
    requestAnimationFrame(() => toast.classList.add('toaster-show'));

    // Auto-dismiss after 3.5s
    setTimeout(() => {
        toast.classList.remove('toaster-show');
        toast.classList.add('toaster-hide');
        setTimeout(() => toast.remove(), 400);
    }, 3500);
}

// ---- DRAG & DROP ----
let dragSrc = null;

function reNumber(section) {
    section.querySelectorAll('.item-num').forEach((el, i) => el.textContent = i + 1);
}

function attachDragEvents(row) {
    row.addEventListener('dragstart', e => {
        dragSrc = row; row.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', '');
    });
    row.addEventListener('dragend', () => {
        row.classList.remove('dragging');
        document.querySelectorAll('.item-row').forEach(r => r.classList.remove('drag-over'));
        dragSrc = null;
    });
    row.addEventListener('dragover', e => {
        e.preventDefault(); e.dataTransfer.dropEffect = 'move';
        if (dragSrc && dragSrc !== row) {
            document.querySelectorAll('.item-row').forEach(r => r.classList.remove('drag-over'));
            row.classList.add('drag-over');
        }
    });
    row.addEventListener('dragleave', () => row.classList.remove('drag-over'));
    row.addEventListener('drop', e => {
        e.preventDefault(); row.classList.remove('drag-over');
        if (!dragSrc || dragSrc === row) return;
        const section = row.closest('.items-list');
        const rows    = [...section.querySelectorAll('.item-row')];
        const srcIdx  = rows.indexOf(dragSrc);
        const tgtIdx  = rows.indexOf(row);
        if (srcIdx < tgtIdx) section.insertBefore(dragSrc, row.nextSibling);
        else                  section.insertBefore(dragSrc, row);
        reNumber(section);
        dragSrc.classList.add('drop-flash');
        setTimeout(() => dragSrc && dragSrc.classList.remove('drop-flash'), 350);
    });
    // Touch
    row.querySelector('.drag-handle').addEventListener('touchstart', e => {
        dragSrc = row; row.classList.add('dragging'); e.preventDefault();
    }, { passive: false });
    row.querySelector('.drag-handle').addEventListener('touchmove', e => {
        if (!dragSrc) return; e.preventDefault();
        const t = e.touches[0];
        const el = document.elementFromPoint(t.clientX, t.clientY);
        const target = el ? el.closest('.item-row') : null;
        document.querySelectorAll('.item-row').forEach(r => r.classList.remove('drag-over'));
        if (target && target !== dragSrc) target.classList.add('drag-over');
    }, { passive: false });
    row.querySelector('.drag-handle').addEventListener('touchend', e => {
        if (!dragSrc) return;
        dragSrc.classList.remove('dragging');
        const t = e.changedTouches[0];
        const el = document.elementFromPoint(t.clientX, t.clientY);
        const target = el ? el.closest('.item-row') : null;
        document.querySelectorAll('.item-row').forEach(r => r.classList.remove('drag-over'));
        if (target && target !== dragSrc) {
            const section = target.closest('.items-list');
            const rows    = [...section.querySelectorAll('.item-row')];
            if (rows.indexOf(dragSrc) < rows.indexOf(target)) section.insertBefore(dragSrc, target.nextSibling);
            else section.insertBefore(dragSrc, target);
            reNumber(section);
            dragSrc.classList.add('drop-flash');
            setTimeout(() => dragSrc && dragSrc.classList.remove('drop-flash'), 350);
        }
        dragSrc = null;
    });
}

// ---- INIT ----
document.querySelectorAll('.items-list').forEach(section => {
    section.querySelectorAll('.item-row').forEach(row => {
        row.draggable = true;
        if (!row.querySelector('.drag-handle')) {
            const h = document.createElement('span');
            h.className = 'drag-handle'; h.title = 'Drag to reorder'; h.innerHTML = '&#8942;&#8942;';
            const n = document.createElement('span'); n.className = 'item-num';
            row.insertBefore(n, row.firstChild);
            row.insertBefore(h, row.firstChild);
        }
        attachDragEvents(row);
    });
    reNumber(section);
});

// Pre-fill if editing an existing log
if (EXISTING) {
    if (EXISTING.section_a_items) setItems('sectionA', JSON.parse(EXISTING.section_a_items));
    if (EXISTING.section_b_items) setItems('sectionB', JSON.parse(EXISTING.section_b_items));
    if (EXISTING.generated_text)  { renderOutput(EXISTING.generated_text); document.getElementById('copyBtn').disabled = false; }
} else {
    // New log: auto smart-fill immediately
    runSmartFill();
}
</script>
@endsection